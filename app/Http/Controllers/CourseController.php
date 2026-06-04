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
        $topics = Topic::where('status', 'approved')->orderBy('sort_order')->get();

        // Map database format to matches what frontend expects:
        $formattedTopics = $topics->map(function ($topic) {
            return [
                'id' => $topic->id,
                'title' => $topic->title,
                'videoUrl' => $topic->video_url,
                'videos' => $topic->videos,
                'documentationPath' => $topic->documentation_path,
                'documentationFilename' => $topic->documentation_filename,
                // We'll lazy-load quizzes on demand or keep them embedded.
                // Keeping them embedded matches index.html/script.js expectation!
                'quiz' => $topic->quizQuestions()->where('status', 'approved')->get()->map(function ($q) {
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

    public function getPublicTopics()
    {
        $topics = Topic::where('status', 'approved')->orderBy('sort_order')->get(['id', 'title', 'description', 'sort_order']);
        
        return response()->json([
            'success' => true,
            'topics' => $topics
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
            'hasCertificate' => \App\Models\Certificate::where('user_id', $user->id)->exists(),
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
            'hasCertificate' => \App\Models\Certificate::where('user_id', $user->id)->exists(),
        ]);
    }

    private function updateUserProgressSnapshot(int $userId, int $completedCount): array
    {
        $totalTopics = Topic::where('status', 'approved')->count();
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
