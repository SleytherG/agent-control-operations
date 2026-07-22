<?php

use App\Http\Middleware\AuthenticateJwtSession;
use App\Modules\IdentityAccess\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');

    Route::middleware([AuthenticateJwtSession::class])->group(function () {
        Route::get('/home', [LoginController::class, 'home'])->name('home');
        Route::post('/auth/refresh', [App\Modules\IdentityAccess\Http\Controllers\RefreshSessionController::class, 'refresh'])->name('auth.refresh');
        Route::post('/logout', [App\Modules\IdentityAccess\Http\Controllers\LogoutController::class, 'logout'])->name('logout');
        Route::get('/sessions', [App\Modules\IdentityAccess\Http\Controllers\SessionHistoryController::class, 'index'])->name('sessions.index');
        Route::patch('/admin/users/{user}/deactivate', [App\Modules\IdentityAccess\Http\Controllers\DeactivateUserController::class, 'deactivate'])->name('admin.users.deactivate');
    });
});
