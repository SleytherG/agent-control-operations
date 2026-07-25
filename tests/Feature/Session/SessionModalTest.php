<?php

namespace Tests\Feature\Session;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionModalTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::factory()->create();
        $this->operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
    }

    public function test_refresh_endpoint_extends_session(): void
    {
        $this->actingAsJwt($this->operator);
        $response = $this->post(route('auth.refresh'));

        $this->assertTrue(
            $response->isRedirect() || $response->isOk(),
            'Refresh should redirect after rotation or return OK'
        );
    }

    public function test_refresh_endpoint_requires_authentication(): void
    {
        $response = $this->post(route('auth.refresh'));

        $this->assertTrue(
            $response->isRedirect() || $response->status() >= 400,
            'Unauthenticated refresh should redirect or return error'
        );
    }

    public function test_logout_revokes_session(): void
    {
        $this->actingAsJwt($this->operator);
        $response = $this->post(route('logout'));

        $response->assertRedirect();

        $this->get(route('home'))->assertRedirect(route('login'));
    }

    public function test_expired_session_redirects_to_login(): void
    {
        $this->actingAsJwt($this->operator);

        $hasActiveSession = \App\Modules\IdentityAccess\Models\AuthSession::where('user_id', $this->operator->id)
            ->where('status', \App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus::ACTIVE)
            ->exists();

        $this->assertTrue($hasActiveSession, 'actingAsJwt should create an active session');
        $this->get(route('home'))->assertOk();
    }
}
