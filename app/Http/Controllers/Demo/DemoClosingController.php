<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DemoClosingController extends Controller
{
    public function show(Request $request, $id)
    {
        $closings = require resource_path('demo/closing.php');
        $status = $request->query('status', 'active');

        $closing = $closings[$status] ?? $closings['active'];
        $closing['status_display'] = match ($status) {
            'confirmed' => 'CONFIRMADO',
            'reopened' => 'REABIERTO',
            default => 'ACTIVO',
        };
        $closing['status_query'] = $status;

        return view('screens.daily-closing.show', [
            'closing' => $closing,
            'role' => 'operator',
            'title' => 'Cierre Diario - AgenteFlow',
        ]);
    }
}
