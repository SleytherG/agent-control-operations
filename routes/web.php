<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', [App\Http\Controllers\HealthController::class, '__invoke']);

require __DIR__.'/identity-access.php';
require __DIR__.'/organization.php';
require __DIR__.'/banking-network.php';
require __DIR__.'/operations.php';
require __DIR__.'/reporting.php';
require __DIR__.'/daily-closing.php';
require __DIR__.'/demo.php';
