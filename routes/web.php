<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/login', 'admin.auth.login')->name('login');
    Route::redirect('/', '/admin/dashboard');

    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    Route::view('/users', 'admin.users.index')->name('users.index');
    Route::get('/users/{user}', function (string $user) {
        return view('admin.users.show', ['userId' => $user]);
    })->name('users.show');

    Route::view('/content', 'admin.content.index')->name('content.index');
    Route::view('/content/topics', 'admin.content.topics')->name('content.topics');
    Route::view('/content/quizzes', 'admin.content.quizzes')->name('content.quizzes');

    Route::view('/progress', 'admin.progress.index')->name('progress.index');
    Route::view('/vouchers', 'admin.vouchers.index')->name('vouchers.index');
    Route::view('/certificates', 'admin.certificates.index')->name('certificates.index');
    Route::view('/reports', 'admin.reports.index')->name('reports.index');
    Route::view('/notifications', 'admin.notifications.index')->name('notifications.index');
    Route::view('/audit-logs', 'admin.audit-logs.index')->name('audit-logs.index');
    Route::view('/settings', 'admin.settings.index')->name('settings.index');
});
