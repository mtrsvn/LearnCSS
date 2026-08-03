<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Topic;
use App\Models\Course;
use App\Models\UserProgress;
use App\Models\QuizAttempt;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Certificate;

class CourseController extends Controller
{
    public function getCourses()
    {
        $courses = Course::where('is_published', true)->get();
        return response()->json([
            'success' => true,
            'courses' => $courses
        ]);
    }

    public function getPublicCourses()
    {
        $courses = Course::where('is_published', true)->get(['id', 'title', 'description', 'thumbnail_url']);
        return response()->json([
            'success' => true,
            'courses' => $courses
        ]);
    }

    public function getTopics($courseId)
    {
        $user = Auth::user();
        if (!$user || !$user->isSubscribed()) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription required to access course content.'
            ], 403);
        }

        $topics = Topic::where('course_id', $courseId)->where('status', 'approved')->orderBy('sort_order')->get();

        $formattedTopics = $topics->map(function ($topic) {
            $subtopics = $topic->subtopics()->where('status', 'approved')->get()->map(function ($sub) {
                return [
                    'id'                    => $sub->id,
                    'title'                 => $sub->title,
                    'sort_order'            => $sub->sort_order,
                    'videoUrl'              => $sub->video_url,
                    'documentationPath'     => $sub->documentation_path,
                    'documentationFilename' => $sub->documentation_filename,
                ];
            });

            return [
                'id'         => $topic->id,
                'title'      => $topic->title,
                'sort_order' => $topic->sort_order,
                'subtopics'  => $subtopics,
                'videoUrl'              => $topic->video_url,
                'videos'                => $topic->videos,
                'documentationPath'     => $topic->documentation_path,
                'documentationFilename' => $topic->documentation_filename,
                'quiz' => $topic->quizQuestions()->where('status', 'approved')->get()->map(function ($q) {
                    return [
                        'question' => $q->question,
                        'options'  => $q->options,
                        'answer'   => $q->answer
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'topics'  => $formattedTopics
        ]);
    }

    public function getPublicTopics($courseId)
    {
        $topics = Topic::where('course_id', $courseId)->where('status', 'approved')->orderBy('sort_order')->get(['id', 'title', 'description', 'sort_order']);
        
        return response()->json([
            'success' => true,
            'topics' => $topics
        ]);
    }

    public function getProgress(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $courseId = $request->query('course_id');

        $query = UserProgress::where('user_id', $user->id);
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        $progressData = $query->get(['topic_id', 'course_id', 'max_unlocked_index']);

        $completedTopics = $progressData->pluck('topic_id')->toArray();
        $topicProgressMap = $progressData->pluck('max_unlocked_index', 'topic_id')->toArray();

        $totalTopics = Topic::where('status', 'approved')->count();
        if ($courseId) {
            $totalTopics = Topic::where('course_id', $courseId)->where('status', 'approved')->count();
        }

        $completedCount = count($completedTopics);
        $progressPercentage = $totalTopics > 0 ? (int) round(($completedCount / $totalTopics) * 100) : 0;

        $certExists = false;
        if ($courseId) {
            $certExists = Certificate::where('user_id', $user->id)->where('course_id', $courseId)->exists();
        } else {
            $certExists = Certificate::where('user_id', $user->id)->exists();
        }

        return response()->json([
            'success' => true,
            'completedTopics' => $completedTopics,
            'topicProgressMap' => $topicProgressMap,
            'progressPercentage' => $progressPercentage,
            'modulesCompletedCount' => $completedCount,
            'lastTopicStarted' => $user->last_topic_id,
            'hasCertificate' => $certExists,
        ]);
    }

    public function startTopic(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$user->isSubscribed()) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription required to access course content.'
            ], 403);
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

    public function unlockProgress(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$user->isSubscribed()) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription required to access course content.'
            ], 403);
        }

        $request->validate([
            'topic_id' => 'required|integer|exists:topics,id',
            'max_unlocked_index' => 'required|integer|min:0',
        ]);

        $topic = Topic::findOrFail($request->topic_id);

        $progress = UserProgress::firstOrCreate(
            ['user_id' => $user->id, 'topic_id' => $request->topic_id],
            ['course_id' => $topic->course_id]
        );

        if ($request->max_unlocked_index > $progress->max_unlocked_index) {
            $progress->max_unlocked_index = $request->max_unlocked_index;
            $progress->save();
        }

        return response()->json([
            'success' => true,
            'max_unlocked_index' => $progress->max_unlocked_index
        ]);
    }

    public function submitQuiz(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$user->isSubscribed()) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription required to access course content.'
            ], 403);
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
        $topic = Topic::findOrFail($topicId);

        QuizAttempt::create([
            'user_id' => $user->id,
            'course_id' => $topic->course_id,
            'topic_id' => $topicId,
            'score' => $score,
            'total' => $total,
            'passed' => $passed
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Topic Quiz Completed',
            'description' => 'Scored ' . $score . '/' . $total . ' in ' . $topic->title . ' quiz. Status: ' . ($passed ? 'Passed' : 'Failed'),
            'ip_address' => $request->ip()
        ]);

        if ($passed) {
            UserProgress::firstOrCreate([
                'user_id' => $user->id,
                'topic_id' => $topicId
            ], ['course_id' => $topic->course_id]);
        }

        $progress = UserProgress::where('user_id', $user->id)->where('course_id', $topic->course_id)->pluck('topic_id')->toArray();
        $totalTopics = Topic::where('course_id', $topic->course_id)->where('status', 'approved')->count();
        $completedCount = count($progress);

        return response()->json([
            'success' => true,
            'message' => $passed ? 'Topic completed!' : 'Quiz completed.',
            'completedTopics' => $progress,
            'progressPercentage' => $totalTopics > 0 ? (int) round(($completedCount / $totalTopics) * 100) : 0,
            'modulesCompletedCount' => $completedCount,
            'hasCertificate' => Certificate::where('user_id', $user->id)->where('course_id', $topic->course_id)->exists(),
        ]);
    }
}
