<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Voucher;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\VoucherPurchased;

class VoucherController extends Controller
{
    private function generateVoucherCode()
    {
        $seg = function() {
            return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        };
        return "CSSM-" . $seg() . "-" . $seg();
    }

    public function buy(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Must be logged in'], 401);
        }

        $code = $this->generateVoucherCode();

        // Create the voucher as pending
        $voucher = Voucher::create([
            'code' => $code,
            'price' => 299.00,
            'status' => 'pending_payment',
            'used' => false,
            'used_by' => $user->id,
            'used_at' => null
        ]);

        // Create Xendit Invoice
        $secretKey = env('XENDIT_SECRET_KEY');
        
        $response = Http::withBasicAuth($secretKey, '')
            ->post('https://api.xendit.co/v2/invoices', [
                'external_id' => $code,
                'amount' => 299,
                'payer_email' => $user->email,
                'description' => 'CertApp CSS Certification Voucher',
                'success_redirect_url' => url('/api/voucher/xendit/success?code=' . $code),
                'failure_redirect_url' => url('/')
            ]);

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'checkout_url' => $response->json()['invoice_url']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate checkout link.'
        ], 500);
    }

    public function xenditSuccess(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            return redirect('/');
        }

        $voucher = Voucher::where('code', $code)->first();
        if (!$voucher || $voucher->status !== 'pending_payment') {
            return redirect('/?voucher_success=' . $code); // Already processed
        }

        // Verify with Xendit
        $secretKey = env('XENDIT_SECRET_KEY');
        $response = Http::withBasicAuth($secretKey, '')
            ->get('https://api.xendit.co/v2/invoices?external_id=' . $code);

        if ($response->successful()) {
            $invoices = $response->json();
            \Illuminate\Support\Facades\Log::info('Xendit Invoices:', $invoices);
            if (count($invoices) > 0 && in_array($invoices[0]['status'], ['PAID', 'SETTLED'])) {
                $voucher->status = 'active';
                $voucher->save();

                $user = \App\Models\User::find($voucher->used_by);
                if ($user) {
                    \Illuminate\Support\Facades\Log::info('Sending email to: ' . $user->email);
                    try {
                        Mail::to($user->email)->send(new VoucherPurchased($voucher, $user));
                        \Illuminate\Support\Facades\Log::info('Email sent.');
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error('User not found for voucher.');
                }

                AuditLog::create([
                    'user_id' => $voucher->used_by,
                    'action' => 'Voucher Purchase',
                    'description' => 'Purchased voucher code ' . $code . ' for ₱299 via Xendit.',
                    'ip_address' => $request->ip()
                ]);

                return redirect('/?voucher_success=' . $code);
            } else {
                \Illuminate\Support\Facades\Log::info('Status not PAID. Redirecting to error.');
            }
        } else {
             \Illuminate\Support\Facades\Log::error('Xendit verification failed.');
        }

        return redirect('/?error=payment_not_completed');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = strtoupper(trim($request->input('code')));
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid voucher code.'
            ], 404);
        }

        if ($voucher->used) {
            return response()->json([
                'success' => false,
                'message' => 'This voucher has already been redeemed.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher code is valid and ready to redeem.'
        ]);
    }

    public function redeem(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'code' => 'required|string'
        ]);

        $code = strtoupper(trim($request->input('code')));
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid voucher code.'
            ], 404);
        }

        if ($voucher->used) {
            return response()->json([
                'success' => false,
                'message' => 'This voucher has already been redeemed.'
            ], 400);
        }

        // Mark as used
        $voucher->update([
            'used' => true,
            'used_by' => $user->id,
            'used_at' => Carbon::now()
        ]);

        $user->is_course_unlocked = true;
        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Voucher Redemption',
            'description' => 'Redeemed voucher code ' . $code . ' to access the Final Certification Exam.',
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Voucher successfully redeemed! Starting final exam...',
            'voucher' => $code
        ]);
    }
}
