<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DemoAuthController extends Controller
{
    public function login(Request $request)
    {
        $state = $request->query('state', 'normal');
        return view('screens.auth.login', [
            'loginState' => $state,
            'title' => 'AgenteFlow - Iniciar Sesion',
        ]);
    }

    public function expiry(Request $request)
    {
        $expiry = $request->query('expiry', 'warning');
        return view('screens.auth.expiry-modal', [
            'expiryState' => $expiry,
            'title' => 'AgenteFlow - Sesion',
        ]);
    }
}
