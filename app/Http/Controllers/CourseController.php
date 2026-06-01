<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Topic;
use App\Models\UserProgress;
use App\Models\QuizAttempt;
use App\Models\AuditLog;
use App\Models\User;

class CourseController extends Controller
{
    public function getTopics()
    {
        $topics = Topic::with(['lessons'])->orderBy('sort_order')->get();

        // Map database format to matches what frontend expects:
        $formattedTopics = $topics->map(function ($topic) {
            return [
                'id' => $topic->id,
                'title' => $topic->title,
                'lessons' => $topic->lessons->map(function ($lesson) {
                    return [
                        'title' => $lesson->title,
                        'videoUrl' => $lesson->video_url,
                        'notes' => $lesson->notes
                    ];
                }),
                // We'll lazy-load quizzes on demand or keep them embedded.
                // Keeping them embedded matches index.html/script.js expectation!
                'quiz' => $topic->quizQuestions()->get()->map(function ($q) {
                    return [
                        'question' => $q->question,
                        'options' => $q->options,
                        'answer' => $q->answer
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'topics' => $formattedTopics
        ]);
    }

    public function getProgress()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $progress = UserProgress::where('user_id', $user->id)
            ->pluck('topic_id')
            ->toArray();

        $snapshot = $this->updateUserProgressSnapshot($user->id, count($progress));

        return response()->json([
            'success' => true,
            'completedTopics' => $progress,
            'progressPercentage' => $snapshot['progressPercentage'],
            'modulesCompletedCount' => $snapshot['modulesCompletedCount'],
            'examStatus' => $snapshot['examStatus'],
            'lastTopicStarted' => $user->last_topic_id,
        ]);
    }

    public function startTopic(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'topic_id' => 'required|integer|exists:topics,id',
        ]);

        $topicId = $request->input('topic_id');

        $user->last_topic_id = $topicId;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Topic started.',
            'lastTopicStarted' => $topicId,
        ]);
    }

    public function submitQuiz(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'topic_id' => 'required|integer|exists:topics,id',
            'score' => 'required|integer',
            'total' => 'required|integer',
            'passed' => 'required|boolean'
        ]);

        $topicId = $request->input('topic_id');
        $score = $request->input('score');
        $total = $request->input('total');
        $passed = $request->input('passed');

        // Log the quiz attempt
        QuizAttempt::create([
            'user_id' => $user->id,
            'topic_id' => $topicId,
            'score' => $score,
            'total' => $total,
            'passed' => $passed
        ]);

        $topic = Topic::find($topicId);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Topic Quiz Completed',
            'description' => 'Scored ' . $score . '/' . $total . ' in ' . ($topic ? $topic->title : 'Topic ' . $topicId) . ' quiz. Status: ' . ($passed ? 'Passed' : 'Failed'),
            'ip_address' => $request->ip()
        ]);

        // If passed, mark topic as completed in progress!
        if ($passed) {
            UserProgress::firstOrCreate([
                'user_id' => $user->id,
                'topic_id' => $topicId
            ]);
        }

        // Get updated progress
        $progress = UserProgress::where('user_id', $user->id)
            ->pluck('topic_id')
            ->toArray();

        $snapshot = $this->updateUserProgressSnapshot($user->id, count($progress));

        return response()->json([
            'success' => true,
            'message' => $passed ? 'Topic completed!' : 'Quiz completed.',
            'completedTopics' => $progress,
            'progressPercentage' => $snapshot['progressPercentage'],
            'modulesCompletedCount' => $snapshot['modulesCompletedCount'],
            'examStatus' => $snapshot['examStatus'],
        ]);
    }

    private function updateUserProgressSnapshot(int $userId, int $completedCount): array
    {
        $totalTopics = Topic::count();
        $progressPercentage = $totalTopics > 0
            ? (int) round(($completedCount / $totalTopics) * 100)
            : 0;
        $examStatus = $completedCount >= $totalTopics && $totalTopics > 0 ? 'eligible' : 'locked';

        User::where('id', $userId)->update([
            'modules_completed_count' => $completedCount,
            'progress_percentage' => $progressPercentage,
            'exam_status' => $examStatus,
        ]);

        return [
            'modulesCompletedCount' => $completedCount,
            'progressPercentage' => $progressPercentage,
            'examStatus' => $examStatus,
        ];
    }
}
