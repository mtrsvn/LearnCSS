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
    public function getQuestions(Request $request)
    {
        $type = $request->query('type', 'final');
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if ($type === 'mid') {
            $totalTopics = \App\Models\Topic::where('status', 'approved')->count();
            $midpoint = floor($totalTopics / 2);
            $topics = \App\Models\Topic::where('status', 'approved')->orderBy('sort_order')->take($midpoint)->pluck('id');
            $questions = QuizQuestion::whereIn('topic_id', $topics)->where('status', 'approved')->get();
        } else {
            $questions = QuizQuestion::where('status', 'approved')->get();
        }

        $count = floor($questions->count() * 0.8);
        if ($count == 0) $count = $questions->count();
        $questions = $questions->random($count)->shuffle();

        session()->put('exam_questions_' . $user->id, $questions->pluck('id')->toArray());
        session()->put('exam_type_' . $user->id, $type);

        $formatted = $questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'options' => $q->options,
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
            'answers' => 'required|array'
        ]);

        $type = session()->get('exam_type_' . $user->id, 'final');
        
        if ($type === 'final') {
            $request->validate(['voucher_code' => 'required|string']);
            $voucherCode = strtoupper(trim($request->input('voucher_code')));
            $voucher = Voucher::where('code', $voucherCode)->where('used_by', $user->id)->first();
            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid exam entry. A verified voucher code is required to submit the final exam.'
                ], 400);
            }
        }

        $userAnswers = $request->input('answers');
        $questionIds = session()->get('exam_questions_' . $user->id, []);
        
        if (empty($questionIds)) {
             return response()->json(['success' => false, 'message' => 'No active exam session found.'], 400);
        }

        $questionsDb = QuizQuestion::whereIn('id', $questionIds)->get()->keyBy('id');
        $score = 0;
        $total = count($questionIds);

        foreach ($questionIds as $index => $qId) {
            $q = $questionsDb->get($qId);
            if ($q) {
                $userSelected = isset($userAnswers[$index]) ? intval($userAnswers[$index]) : null;
                if ($userSelected !== null && $userSelected === $q->answer) {
                    $score++;
                }
            }
        }

        $passed = ($score === $total);

        QuizAttempt::create([
            'user_id' => $user->id,
            'topic_id' => null, 
            'score' => $score,
            'total' => $total,
            'passed' => $passed
        ]);

        $examName = $type === 'mid' ? 'Mid Exam' : 'Final Exam';
        
        AuditLog::create([
            'user_id' => $user->id,
            'action' => "$examName Completed",
            'description' => "Completed the CSS Certification $examName. Scored: $score/$total. Passed: " . ($passed ? 'Yes' : 'No'),
            'ip_address' => $request->ip()
        ]);

        $certificate = null;
        if ($type === 'final' && $passed) {
            $year = date('Y');
            $serial = str_pad(Certificate::count() + 1, 4, '0', STR_PAD_LEFT);
            $certCode = 'LC-CERT-' . $year . '-' . $serial;

            $certificate = Certificate::firstOrCreate([
                'user_id' => $user->id
            ], [
                'code' => $certCode,
                'issued_at' => Carbon::now()
            ]);

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
