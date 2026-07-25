<?php

use Illuminate\Support\Facades\Route;

Route::get('/up', [App\Http\Controllers\HealthController::class, 'liveness']);
Route::get('/health', [App\Http\Controllers\HealthController::class, '__invoke']);

require __DIR__.'/identity-access.php';
require __DIR__.'/agents.php';
require __DIR__.'/operations.php';
require __DIR__.'/reporting.php';
require __DIR__.'/daily-closing.php';
