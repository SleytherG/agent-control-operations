<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Demo\DemoAuthController;
use App\Http\Controllers\Demo\DemoOperatorController;
use App\Http\Controllers\Demo\DemoAdminController;
use App\Http\Controllers\Demo\DemoClosingController;

Route::prefix('demo')->group(function () {
    Route::get('/login', [DemoAuthController::class, 'login']);
    Route::get('/expiry', [DemoAuthController::class, 'expiry']);
    Route::get('/operator/dashboard', [DemoOperatorController::class, 'dashboard']);
    Route::get('/operator/register', [DemoOperatorController::class, 'register']);
    Route::get('/operator/history', [DemoOperatorController::class, 'history']);
    Route::get('/admin/dashboard', [DemoAdminController::class, 'dashboard']);
    Route::get('/daily-closing/{id}', [DemoClosingController::class, 'show']);
});
