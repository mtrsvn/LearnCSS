<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;

// ─── CUSTOMER FRONTEND LANDING ───────────────────────────────
Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});

// ─── PUBLIC API ROUTES ─────────────────────────────────────────
Route::prefix('api')->group(function () {
    Route::get('/public/courses', [CourseController::class, 'getPublicCourses']);
    Route::get('/public/courses/{course}/topics', [CourseController::class, 'getPublicTopics']);
});

// ─── CUSTOMER BACKEND API ROUTES ─────────────────────────────
Route::prefix('api')->group(function () {
    // Auth Actions
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/session', [AuthController::class, 'session']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

    // Authenticated API endpoints
    Route::middleware('auth')->group(function () {
        // Course & Progress
        Route::get('/courses', [CourseController::class, 'getCourses']);
        Route::get('/courses/{course}/topics', [CourseController::class, 'getTopics']);
        Route::get('/progress', [CourseController::class, 'getProgress']);
        Route::post('/progress/start', [CourseController::class, 'startTopic']);
        Route::post('/progress/unlock', [CourseController::class, 'unlockProgress']);
        Route::post('/quiz/attempt', [CourseController::class, 'submitQuiz']);

        // Vouchers
        Route::post('/voucher/buy', [VoucherController::class, 'buy']);
        Route::post('/voucher/verify', [VoucherController::class, 'verify']);
        Route::post('/voucher/redeem', [VoucherController::class, 'redeem']);

        // Exam & Certificate
        Route::get('/courses/{course}/exam/questions', [ExamController::class, 'getQuestions']);
        Route::post('/courses/{course}/exam/submit', [ExamController::class, 'submit']);
        Route::get('/courses/{course}/certificate', [ExamController::class, 'getCertificate']);

        // Notifications / Announcements API
        Route::get('/notifications', function () {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            $announcements = \App\Models\Announcement::orderBy('created_at', 'desc')->get();
            $readIds = session()->get('read_announcements_' . $user->id, []);
            
            $notifications = $announcements->map(function ($a) use ($readIds) {
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'message' => $a->content,
                    'created_at' => $a->created_at->toIso8601String(),
                    'is_read' => in_array($a->id, $readIds)
                ];
            });

            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);
        });

        Route::post('/notifications/{id}/read', function ($id) {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            $readIds = session()->get('read_announcements_' . $user->id, []);
            if (!in_array((int)$id, $readIds)) {
                $readIds[] = (int)$id;
                session()->put('read_announcements_' . $user->id, $readIds);
            }
            return response()->json(['success' => true]);
        });

        Route::post('/notifications/read-all', function () {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            $ids = \App\Models\Announcement::pluck('id')->toArray();
            session()->put('read_announcements_' . $user->id, $ids);
            return response()->json(['success' => true]);
        });
    });

    // Public / callback endpoints (no auth middleware required)
    Route::get('/voucher/xendit/success', [VoucherController::class, 'xenditSuccess'])->name('voucher.xendit.success');
});

// ─── ADMIN DASHBOARD SYSTEM ──────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Guest Auth
    Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // Admin Protected Area
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::redirect('/', '/admin/dashboard');
        
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Users
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::post('/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
        Route::post('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');

        // Content
        Route::get('/content', [AdminController::class, 'contentCourses'])->name('content.index');
        
        // Course specific content
        Route::prefix('/content/courses/{course}')->group(function () {
            Route::get('/topics', [AdminController::class, 'contentTopics'])->name('content.topics');
            Route::get('/quizzes', [AdminController::class, 'contentQuizzes'])->name('content.quizzes');

            // Topics CRUD
            Route::post('/topics/reorder', [AdminController::class, 'reorderTopics'])->name('content.topics.reorder');
            Route::post('/topics/{topic}/approve', [AdminController::class, 'approveTopic'])->name('content.topics.approve');
            Route::post('/topics', [AdminController::class, 'storeTopic'])->name('content.topics.store');
            Route::post('/topics/{topic}', [AdminController::class, 'updateTopic'])->name('content.topics.update');
            Route::delete('/topics/{topic}', [AdminController::class, 'destroyTopic'])->name('content.topics.destroy');

            // Subtopics CRUD
            Route::post('/subtopics', [AdminController::class, 'storeSubtopic'])->name('content.subtopics.store');
            Route::post('/subtopics/{subtopic}/approve', [AdminController::class, 'approveSubtopic'])->name('content.subtopics.approve');
            Route::post('/subtopics/{subtopic}', [AdminController::class, 'updateSubtopic'])->name('content.subtopics.update');
            Route::delete('/subtopics/{subtopic}', [AdminController::class, 'destroySubtopic'])->name('content.subtopics.destroy');

            // Quizzes CRUD
            Route::post('/quizzes/{quiz}/approve', [AdminController::class, 'approveQuiz'])->name('content.quizzes.approve');
            Route::post('/quizzes', [AdminController::class, 'storeQuiz'])->name('content.quizzes.store');
            Route::post('/quizzes/{quiz}', [AdminController::class, 'updateQuiz'])->name('content.quizzes.update');
            Route::delete('/quizzes/{quiz}', [AdminController::class, 'destroyQuiz'])->name('content.quizzes.destroy');
        });

        // Courses CRUD
        Route::post('/content/courses', [AdminController::class, 'storeCourse'])->name('content.courses.store');
        Route::post('/content/courses/{course}', [AdminController::class, 'updateCourse'])->name('content.courses.update');
        Route::delete('/content/courses/{course}', [AdminController::class, 'destroyCourse'])->name('content.courses.destroy');

        // Activity Logs
        Route::get('/progress', [AdminController::class, 'progress'])->name('progress.index');
        Route::get('/vouchers', [AdminController::class, 'vouchers'])->name('vouchers.index');
        Route::post('/vouchers/generate', [AdminController::class, 'generateVouchers'])->name('vouchers.generate');
        
        Route::get('/certificates', [AdminController::class, 'certificates'])->name('certificates.index');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');
        
        // Notifications
        Route::get('/notifications', [AdminController::class, 'announcements'])->name('notifications.index');
        Route::post('/notifications/create', [AdminController::class, 'createAnnouncement'])->name('notifications.create');
        
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs.index');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings.index');
    });
});
