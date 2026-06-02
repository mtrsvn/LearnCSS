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
        Route::get('/topics', [CourseController::class, 'getTopics']);
        Route::get('/progress', [CourseController::class, 'getProgress']);
        Route::post('/progress/start', [CourseController::class, 'startTopic']);
        Route::post('/quiz/attempt', [CourseController::class, 'submitQuiz']);

        // Vouchers
        Route::post('/voucher/buy', [VoucherController::class, 'buy']);
        Route::post('/voucher/verify', [VoucherController::class, 'verify']);
        Route::post('/voucher/redeem', [VoucherController::class, 'redeem']);

        // Exam & Certificate
        Route::get('/exam/questions', [ExamController::class, 'getQuestions']);
        Route::post('/exam/submit', [ExamController::class, 'submit']);
        Route::get('/certificate', [ExamController::class, 'getCertificate']);

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
        Route::get('/content', [AdminController::class, 'content'])->name('content.index');
        Route::get('/content/topics', [AdminController::class, 'contentTopics'])->name('content.topics');
        Route::get('/content/quizzes', [AdminController::class, 'contentQuizzes'])->name('content.quizzes');
        
        // Topics CRUD
        Route::post('/content/topics', [AdminController::class, 'storeTopic'])->name('content.topics.store');
        Route::post('/content/topics/{topic}', [AdminController::class, 'updateTopic'])->name('content.topics.update');
        Route::delete('/content/topics/{topic}', [AdminController::class, 'destroyTopic'])->name('content.topics.destroy');

        // Lessons CRUD
        Route::post('/content/lessons', [AdminController::class, 'storeLesson'])->name('content.lessons.store');
        Route::post('/content/lessons/{lesson}', [AdminController::class, 'updateLesson'])->name('content.lessons.update');
        Route::delete('/content/lessons/{lesson}', [AdminController::class, 'destroyLesson'])->name('content.lessons.destroy');

        // Quizzes CRUD
        Route::post('/content/quizzes', [AdminController::class, 'storeQuiz'])->name('content.quizzes.store');
        Route::post('/content/quizzes/{quiz}', [AdminController::class, 'updateQuiz'])->name('content.quizzes.update');
        Route::delete('/content/quizzes/{quiz}', [AdminController::class, 'destroyQuiz'])->name('content.quizzes.destroy');

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
