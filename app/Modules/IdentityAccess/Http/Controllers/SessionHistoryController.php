<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Application\Actions\ListAuthorizedSessions;
use App\Modules\IdentityAccess\Http\Requests\ListSessionsRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionHistoryController extends Controller
{
    public function __construct(
        private ListAuthorizedSessions $listAuthorizedSessions,
    ) {}

    public function index(ListSessionsRequest $request): View
    {
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        $sessions = $this->listAuthorizedSessions->execute(
            $user,
            $request->validated(),
        );

        return view('identity-access.sessions.index', [
            'sessions' => $sessions,
            'filters' => $request->validated(),
        ]);
    }
}
