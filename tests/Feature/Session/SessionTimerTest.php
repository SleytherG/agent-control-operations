<?php

namespace Tests\Feature\Session;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTimerTest extends TestCase
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

    public function test_session_indicator_present_in_authenticated_views(): void
    {
        $this->actingAsJwt($this->operator);
        $response = $this->get(route('home'));

        $response->assertOk();
        $this->assertStringContainsString(
            'session-expires-at',
            $response->getContent(),
            'Authenticated views must include session expiry data attribute'
        );
    }

    public function test_session_expiry_is_shared_to_views(): void
    {
        $this->actingAsJwt($this->operator);
        $response = $this->get(route('home'));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/data-expires-at/',
            $content,
            'Session indicator must have data-expires-at attribute with server expiry'
        );
    }

    public function test_unauthenticated_users_cannot_access_protected_routes(): void
    {
        $response = $this->get(route('home'));
        $response->assertRedirect(route('login'));
    }
}
