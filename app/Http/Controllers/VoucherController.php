<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Voucher;
use App\Models\AuditLog;
use Carbon\Carbon;

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

        $code = $this->generateVoucherCode();

        $voucher = Voucher::create([
            'code' => $code,
            'price' => 299.00,
            'used' => false,
            'used_by' => $user ? $user->id : null,
            'used_at' => null
        ]);

        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Voucher Purchase',
                'description' => 'Purchased voucher code ' . $code . ' for ₱299.',
                'ip_address' => $request->ip()
            ]);
        }

        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => 'Simulated purchase successful!'
        ]);
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
