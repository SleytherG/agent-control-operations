<?php

namespace App\Modules\IdentityAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Application\Actions\ResetOperatorPassword;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Http\Requests\ResetOperatorPasswordRequest;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function __invoke(
        ResetOperatorPasswordRequest $request,
        User $user,
        ResetOperatorPassword $resetOperatorPassword,
    ): JsonResponse {
        Gate::authorize('updateOperator', $user);

        if ($user->status !== UserStatus::ACTIVE) {
            return response()->json([
                'message' => 'Active o desbloquee la cuenta antes de restablecer la contraseña.',
            ], 409);
        }

        Gate::authorize('resetPassword', $user);

        $key = $this->stepUpKey($request);
        $maxAttempts = config('session-security.password_reset.step_up_max_attempts', 5);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Demasiados intentos. Intente nuevamente más tarde.',
                'retryAfter' => RateLimiter::availableIn($key),
            ], 429);
        }

        try {
            $result = $resetOperatorPassword->execute(
                $user,
                $request->user(),
                $request->string('admin_password')->toString(),
                $request->input('reason'),
                $request->header('X-Correlation-ID'),
            );
        } catch (ValidationException $exception) {
            RateLimiter::hit(
                $key,
                config('session-security.password_reset.step_up_decay_seconds', 60),
            );
            throw $exception;
        }

        RateLimiter::clear($key);
        $reset = $result['reset'];

        return response()->json([
            'temporaryPassword' => $result['temporary_password'],
            'issuedAt' => $reset->issued_at->timezone('America/Lima')->toIso8601String(),
            'expiresAt' => $reset->expires_at->timezone('America/Lima')->toIso8601String(),
            'deliveryWarning' => 'Compártela únicamente por un canal privado aprobado.',
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    }

    private function stepUpKey(ResetOperatorPasswordRequest $request): string
    {
        $originHash = substr(hash('sha256', (string) $request->ip()), 0, 12);

        return "password-reset-step-up:{$request->user()->id}:{$originHash}";
    }
}
