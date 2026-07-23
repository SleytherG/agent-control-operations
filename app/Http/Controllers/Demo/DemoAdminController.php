<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;

class DemoAdminController extends Controller
{
    public function dashboard()
    {
        $data = require resource_path('demo/admin-dashboard.php');
        $user = require resource_path('demo/user.php');

        return view('screens.admin.dashboard', [
            'metrics' => $data['metrics'],
            'evolution' => $data['evolution'],
            'bankDistribution' => $data['bank_distribution'],
            'flowByRegion' => $data['flow_by_region'],
            'topStores' => $data['top_stores'],
            'topWorkers' => $data['top_workers'],
            'user' => $user['admin'],
            'role' => 'admin',
            'title' => 'Dashboard Admin - AgenteFlow',
        ]);
    }
}
