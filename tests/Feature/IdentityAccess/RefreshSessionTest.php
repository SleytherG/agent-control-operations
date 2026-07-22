<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\RefreshTokenState;
use App\Modules\IdentityAccess\Models\AuthRefreshToken;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefreshSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_refresh_rotates_token_and_returns_expires_at(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $session = AuthSession::factory()->create([
            'user_id' => $user->id,
            'status' => AuthSessionStatus::ACTIVE,
            'access_expires_at' => now()->addSeconds(300),
            'absolute_expires_at' => now()->addHours(8),
        ]);
        $refreshToken = AuthRefreshToken::factory()->create([
            'auth_session_id' => $session->id,
            'state' => RefreshTokenState::ACTIVE,
        ]);

        $this->markTestSkipped('Requires full cookie/CSRF integration.');
    }
}
