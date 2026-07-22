<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            return response()->json(['status' => 'ok'], 200);
        } catch (\Throwable) {
            return response()->json(['status' => 'unavailable'], 503);
        }
    }
}
