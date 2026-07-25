<?php

use App\Http\Middleware\AuthenticateJwtSession;
use App\Modules\IdentityAccess\Http\Controllers\LoginController;
use App\Modules\IdentityAccess\Http\Controllers\PasswordResetController;
use App\Modules\IdentityAccess\Http\Controllers\PasswordResetAuditController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
    Route::post('/auth/refresh', [App\Modules\IdentityAccess\Http\Controllers\RefreshSessionController::class, 'refresh'])->name('auth.refresh');

    Route::middleware([AuthenticateJwtSession::class])->group(function () {
        Route::get('/home', [LoginController::class, 'home'])->name('home');
        Route::post('/logout', [App\Modules\IdentityAccess\Http\Controllers\LogoutController::class, 'logout'])->name('logout');
        Route::get('/sessions', [App\Modules\IdentityAccess\Http\Controllers\SessionHistoryController::class, 'index'])->name('sessions.index');
        Route::patch('/admin/users/{user}/deactivate', [App\Modules\IdentityAccess\Http\Controllers\DeactivateUserController::class, 'deactivate'])->name('admin.users.deactivate');
        Route::get('/admin/users', [App\Modules\IdentityAccess\Http\Controllers\OperatorController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [App\Modules\IdentityAccess\Http\Controllers\OperatorController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [App\Modules\IdentityAccess\Http\Controllers\OperatorController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [App\Modules\IdentityAccess\Http\Controllers\OperatorController::class, 'edit'])->name('admin.users.edit');
        Route::patch('/admin/users/{user}', [App\Modules\IdentityAccess\Http\Controllers\OperatorController::class, 'update'])->name('admin.users.update');
        Route::post('/admin/users/{user}/password-reset', PasswordResetController::class)->name('admin.users.password-reset');
        Route::get('/admin/users/{user}/password-resets', PasswordResetAuditController::class)->name('admin.users.password-resets.index');
        Route::delete('/admin/users/{user}', [App\Modules\IdentityAccess\Http\Controllers\OperatorController::class, 'deactivate'])->name('admin.users.deactivate-operator');

        Route::get('/password/change', [App\Modules\IdentityAccess\Http\Controllers\PasswordChangeController::class, 'show'])->name('password.change');
        Route::patch('/password/change', [App\Modules\IdentityAccess\Http\Controllers\PasswordChangeController::class, 'update'])->name('password.change.update');
    });
});
