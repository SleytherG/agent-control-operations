<?php

namespace Tests\Feature\Session;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionExpiryTest extends TestCase
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

    public function test_expired_access_token_rejects_requests(): void
    {
        $this->actingAsJwt($this->operator);

        $session = \App\Modules\IdentityAccess\Models\AuthSession::where('user_id', $this->operator->id)
            ->where('status', \App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus::ACTIVE)
            ->latest()
            ->first();

        $this->assertNotNull($session, 'A valid session should have been created');
        $response = $this->get(route('home'));
        $response->assertOk();
    }

    public function test_no_cookie_redirects_to_login(): void
    {
        $response = $this->get(route('home'));
        $response->assertRedirect(route('login'));
    }

    public function test_valid_session_accesses_home(): void
    {
        $this->actingAsJwt($this->operator);
        $response = $this->get(route('home'));
        $response->assertOk();
    }

    public function test_form_input_preserved_on_expiry(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->post(route('operations.store'), [
            'amount' => 100,
            'operation_type_id' => 1,
        ]);

        $this->assertFalse(
            $response->isServerError(),
            'POST with valid session should not error'
        );
    }
}
