<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Application\Actions\DeactivateUser;
use App\Modules\IdentityAccess\Http\Requests\DeactivateUserRequest;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeactivateUserController extends Controller
{
    public function __construct(
        private DeactivateUser $deactivateUser,
    ) {}

    public function deactivate(DeactivateUserRequest $request, User $user): RedirectResponse
    {
        $actor = auth()->user();

        if (! $actor) {
            abort(401);
        }

        $target = User::findOrFail($user->id);

        Gate::authorize('deactivate', $target);

        $this->deactivateUser->execute($target, $actor, $request->deactivationReason());

        return redirect()->route('sessions.index')
            ->with('status', 'Usuario desactivado correctamente.');
    }
}
