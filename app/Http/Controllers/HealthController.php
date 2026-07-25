<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function liveness(): JsonResponse
    {
        return response()->json(['status' => 'ok'], 200);
    }

    public function __invoke(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            return response()->json(['status' => 'ok', 'database' => 'connected'], 200);
        } catch (\Throwable) {
            return response()->json(['status' => 'unavailable', 'database' => 'disconnected'], 503);
        }
    }
}
