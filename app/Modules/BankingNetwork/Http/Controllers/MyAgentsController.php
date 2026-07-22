<?php

namespace App\Modules\BankingNetwork\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyAgentsController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $assignments = UserBankAgentAssignment::with(['bankAgent.store', 'bankAgent.bank'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('assigned_at', 'desc')
            ->paginate(20);

        return view('banking-network.my-agents', compact('assignments'));
    }
}
