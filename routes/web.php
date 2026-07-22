<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', [App\Http\Controllers\HealthController::class, '__invoke']);

require __DIR__.'/identity-access.php';
