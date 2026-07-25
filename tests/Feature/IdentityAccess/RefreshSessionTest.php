<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Application\Actions\RotateRefreshToken;
use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\RefreshTokenState;
use App\Modules\IdentityAccess\Models\AuthRefreshToken;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\RefreshTokenService;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RefreshSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_rotate_refresh_token_returns_expires_at_as_datetime(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $session = AuthSession::create([
            'public_id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'status' => AuthSessionStatus::ACTIVE,
            'started_at' => now(),
            'access_expires_at' => now()->addSeconds(300),
            'absolute_expires_at' => now()->addHours(8),
        ]);

        $refreshService = app(RefreshTokenService::class);
        $rawToken = $refreshService->generate();
        $tokenHash = $refreshService->hash($rawToken);

        AuthRefreshToken::create([
            'auth_session_id' => $session->id,
            'token_hash' => $tokenHash,
            'state' => RefreshTokenState::ACTIVE,
            'generation' => 1,
            'issued_at' => now(),
            'expires_at' => now()->addSeconds(300),
        ]);

        $action = app(RotateRefreshToken::class);
        $result = $action->execute($rawToken);

        $this->assertNotNull($result, 'RotateRefreshToken should return non-null for valid token');
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertInstanceOf(\DateTimeInterface::class, $result['expires_at']);

        $iso8601 = $result['expires_at']->format(\DateTimeInterface::ATOM);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $iso8601,
            'expires_at format must be ISO 8601'
        );
    }

    public function test_refresh_endpoint_requires_cookie(): void
    {
        $response = $this->postJson('/auth/refresh');
        $response->assertUnauthorized();
    }

    public function test_refresh_endpoint_rotates_an_encrypted_cookie_and_returns_json(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $session = AuthSession::create([
            'public_id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'status' => AuthSessionStatus::ACTIVE,
            'started_at' => now(),
            'access_expires_at' => now()->addSeconds(300),
            'absolute_expires_at' => now()->addHours(8),
        ]);

        $refreshService = app(RefreshTokenService::class);
        $rawToken = $refreshService->generate();
        $tokenHash = $refreshService->hash($rawToken);

        $refreshRow = AuthRefreshToken::create([
            'auth_session_id' => $session->id,
            'token_hash' => $tokenHash,
            'state' => RefreshTokenState::ACTIVE,
            'generation' => 1,
            'issued_at' => now(),
            'expires_at' => now()->addSeconds(300),
        ]);

        $response = $this->withCredentials()->withCookie(
            config('session-security.cookies.refresh_name'),
            $rawToken,
        )->postJson('/auth/refresh');

        $response
            ->assertOk()
            ->assertJsonStructure(['expiresAt'])
            ->assertCookie(config('session-security.cookies.access_name'))
            ->assertCookie(config('session-security.cookies.refresh_name'));

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $response->json('expiresAt'),
        );

        $refreshRow->refresh();
        $this->assertSame(RefreshTokenState::CONSUMED, $refreshRow->state);
        $this->assertDatabaseHas('auth_refresh_tokens', [
            'auth_session_id' => $session->id,
            'generation' => 2,
            'state' => RefreshTokenState::ACTIVE->value,
        ]);
        $this->assertSame(AuthSessionStatus::ACTIVE, $session->refresh()->status);

        $reuseResponse = $this->withCredentials()->withCookie(
            config('session-security.cookies.refresh_name'),
            $rawToken,
        )->postJson('/auth/refresh');

        $reuseResponse->assertUnauthorized();
        $this->assertSame(AuthSessionStatus::REVOKED, $session->refresh()->status);
    }
}
