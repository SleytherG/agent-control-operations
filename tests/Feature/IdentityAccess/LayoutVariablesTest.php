<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LayoutVariablesTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateUser(User $user): string
    {
        $session = AuthSession::create([
            'public_id' => \Illuminate\Support\Str::uuid()->toString(),
            'user_id' => $user->id,
            'status' => AuthSessionStatus::ACTIVE,
            'started_at' => now(),
            'access_expires_at' => now()->addMinutes(5),
            'absolute_expires_at' => now()->addHours(8),
        ]);

        $result = app(JwtTokenService::class)->issue((string) $user->id, $session->public_id);

        return $result['token'];
    }

    public function test_middleware_shares_user_variable_to_authenticated_views(): void
    {
        $user = User::factory()->create();
        $jwt = $this->authenticateUser($user);

        $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('home'));

        $sharedUser = View::shared('user');
        $this->assertNotNull($sharedUser);
        $this->assertEquals($user->id, $sharedUser->id);
    }

    public function test_middleware_shares_role_variable_for_operator(): void
    {
        $user = User::factory()->create();
        $jwt = $this->authenticateUser($user);

        $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('home'));

        $sharedRole = View::shared('role');
        $this->assertNotNull($sharedRole);
        $this->assertEquals('operator', $sharedRole);
    }

    public function test_middleware_shares_admin_role_correctly(): void
    {
        $user = User::factory()->administradorPropietario()->create();
        $jwt = $this->authenticateUser($user);

        $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('home'));

        $sharedRole = View::shared('role');
        $this->assertEquals('admin', $sharedRole);
    }

    public function test_middleware_shares_session_expires_at(): void
    {
        $user = User::factory()->create();
        $jwt = $this->authenticateUser($user);

        $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('home'));

        $sharedExpiry = View::shared('sessionExpiresAt');
        $this->assertNotNull($sharedExpiry);
    }

    public function test_unauthenticated_user_redirects_to_login(): void
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }

    public function test_expired_jwt_redirects_to_login(): void
    {
        $this->withCookie(config('session-security.cookies.access_name'), 'expired.jwt.token')
            ->get(route('home'))
            ->assertRedirect(route('login'));
    }
}
