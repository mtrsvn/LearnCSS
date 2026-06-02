<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\Voucher;
use App\Models\Certificate;
use App\Models\AuditLog;
use Carbon\Carbon;

class ExamController extends Controller
{
    public function getQuestions()
    {
        // Fetch all approved questions (from all topics) for the comprehensive final exam.
        $questions = QuizQuestion::where('status', 'approved')->orderBy('id')->get();

        $formatted = $questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'options' => $q->options,
                // Do not return the answer index to the client for integrity!
            ];
        });

        return response()->json([
            'success' => true,
            'questions' => $formatted
        ]);
    }

    public function submit(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'voucher_code' => 'required|string',
            'answers' => 'required|array'
        ]);

        $voucherCode = strtoupper(trim($request->input('voucher_code')));
        $userAnswers = $request->input('answers'); // Associative array of [question_id => selected_option_index]

        // Validate voucher is redeemed by this user
        $voucher = Voucher::where('code', $voucherCode)
            ->where('used_by', $user->id)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid exam entry. A verified voucher code is required to submit the exam.'
            ], 400);
        }

        $questions = QuizQuestion::where('status', 'approved')->orderBy('id')->get();
        $score = 0;
        $total = count($questions);

        // Grade the exam
        // The user answers list is expected to match the array index sequence or database ids.
        // We'll support both, matching script.js structure where answers is an array of indices.
        // In script.js: userAnswers is an array [idx0, idx1, idx2...] corresponding to the question list order.
        foreach ($questions as $index => $q) {
            $userSelected = isset($userAnswers[$index]) ? intval($userAnswers[$index]) : null;
            if ($userSelected !== null && $userSelected === $q->answer) {
                $score++;
            }
        }

        $passed = ($score === $total);

        // Record the attempt
        QuizAttempt::create([
            'user_id' => $user->id,
            'topic_id' => null, // NULL is final exam
            'score' => $score,
            'total' => $total,
            'passed' => $passed
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Final Exam Completed',
            'description' => 'Completed the CSS Certification Final Exam. Scored: ' . $score . '/' . $total . '. Passed: ' . ($passed ? 'Yes' : 'No'),
            'ip_address' => $request->ip()
        ]);

        $certificate = null;
        if ($passed) {
            $year = date('Y');
            $serial = str_pad(Certificate::count() + 1, 4, '0', STR_PAD_LEFT);
            $certCode = 'LC-CERT-' . $year . '-' . $serial;

            $certificate = Certificate::firstOrCreate([
                'user_id' => $user->id
            ], [
                'code' => $certCode,
                'issued_at' => Carbon::now()
            ]);

            // Only log if it was recently created
            if ($certificate->wasRecentlyCreated) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'Certificate Issued',
                    'description' => 'Issued certificate ' . $certCode . ' for completing the CSS Certification Program.',
                    'ip_address' => $request->ip()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'passed' => $passed,
            'score' => $score,
            'total' => $total,
            'certificate' => $certificate ? [
                'code' => $certificate->code,
                'issuedAt' => Carbon::parse($certificate->issued_at)->format('F d, Y'),
                'userName' => $user->name
            ] : null
        ]);
    }

    public function getCertificate()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $certificate = Certificate::where('user_id', $user->id)->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'certificate' => [
                'code' => $certificate->code,
                'issuedAt' => Carbon::parse($certificate->issued_at)->format('F d, Y'),
                'userName' => $user->name
            ]
        ]);
    }
}
