<?php

namespace App\Modules\Agents\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Agents\Models\UserAgentAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyAgentsController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $assignments = UserAgentAssignment::with('agent')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('starts_at', 'desc')
            ->paginate(20);

        return view('agents.my-agents', compact('assignments'));
    }
}
