<?php

use App\Http\Middleware\AuthenticateJwtSession;
use App\Modules\DailyClosing\Http\Controllers\DailyClosingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', AuthenticateJwtSession::class])->group(function () {
    Route::get('/daily-closures', [DailyClosingController::class, 'index'])->name('daily-closures.index');
    Route::get('/daily-closures/create', [DailyClosingController::class, 'create'])->name('daily-closures.create');
    Route::post('/daily-closures', [DailyClosingController::class, 'store'])->name('daily-closures.store');
    Route::get('/daily-closures/{closure}', [DailyClosingController::class, 'show'])->name('daily-closures.show');
    Route::post('/daily-closures/{closure}/confirm', [DailyClosingController::class, 'confirm'])->name('daily-closures.confirm');
    Route::post('/daily-closures/{closure}/reopen', [DailyClosingController::class, 'reopen'])->name('daily-closures.reopen');
});
