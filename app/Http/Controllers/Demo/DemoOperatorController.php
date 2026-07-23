<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;

class DemoOperatorController extends Controller
{
    public function dashboard()
    {
        $data = require resource_path('demo/operator-dashboard.php');
        $user = require resource_path('demo/user.php');

        return view('screens.operator.dashboard', [
            'metrics' => $data['metrics'],
            'distribution' => $data['distribution'],
            'evolution' => $data['evolution'],
            'recentOperations' => $data['recent_operations'],
            'user' => $user['operator'],
            'role' => 'operator',
            'title' => 'Dashboard - AgenteFlow',
        ]);
    }

    public function register()
    {
        $user = require resource_path('demo/user.php');

        return view('screens.operator.register', [
            'user' => $user['operator'],
            'role' => 'operator',
            'title' => 'Registrar Operacion - AgenteFlow',
            'banks' => [
                'bcp' => 'Banco de Credito (BCP)',
                'bbva' => 'BBVA Continental',
                'interbank' => 'Interbank',
                'scotiabank' => 'Scotiabank',
            ],
            'types' => [
                'deposito' => 'Deposito en Efectivo',
                'retiro' => 'Retiro de Efectivo',
                'pago_servicios' => 'Pago de Servicios',
                'transferencia' => 'Transferencia a Terceros',
            ],
        ]);
    }

    public function history()
    {
        $operations = require resource_path('demo/operations.php');
        $user = require resource_path('demo/user.php');

        return view('screens.operator.history', [
            'operations' => $operations,
            'user' => $user['operator'],
            'role' => 'operator',
            'title' => 'Historial - AgenteFlow',
            'summary' => [
                'total_ops' => count($operations),
                'total_amount' => 'S/ ' . number_format(22550.50, 2),
                'total_cash_in' => 'S/ ' . number_format(18890.00, 2),
                'total_cash_out' => 'S/ ' . number_format(3660.50, 2),
                'net_movement' => '+S/ ' . number_format(15229.50, 2),
            ],
        ]);
    }
}
