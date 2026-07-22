<?php

use App\Http\Middleware\AuthenticateJwtSession;
use App\Modules\Reporting\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', AuthenticateJwtSession::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'operatorDashboard'])->name('dashboard.operator');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/dashboard/operators', [DashboardController::class, 'operatorComparison'])->name('dashboard.operators');
    });
});
