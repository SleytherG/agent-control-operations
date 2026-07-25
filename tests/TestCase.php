<?php

namespace Tests;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function actingAsJwt(User $user): void
    {
        $session = AuthSession::create([
            'public_id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'status' => AuthSessionStatus::ACTIVE,
            'started_at' => now(),
            'access_expires_at' => now()->addMinutes(5),
            'absolute_expires_at' => now()->addHours(8),
            'last_refreshed_at' => now(),
            'ip_hash' => 'test',
            'user_agent_summary' => 'test',
        ]);

        $jwtService = app(JwtTokenService::class);
        $result = $jwtService->issue((string) $user->id, $session->public_id);

        $this->withCookie(config('session-security.cookies.access_name'), $result['token']);
    }

    protected function actingAsJwtSession(User $user, AuthSession $session): void
    {
        $result = app(JwtTokenService::class)->issue((string) $user->id, $session->public_id);

        $this->withCookie(config('session-security.cookies.access_name'), $result['token']);
    }
}
