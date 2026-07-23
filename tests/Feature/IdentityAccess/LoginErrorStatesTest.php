<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginErrorStatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_invalid_credentials_flashes_error_state(): void
    {
        $response = $this->post(route('login.store'), [
            'identifier' => 'noone',
            'password' => 'wrong',
        ]);

        $response->assertSessionHas('login_state', 'error');
    }

    public function test_login_with_disabled_user_flashes_disabled_state(): void
    {
        User::factory()->create([
            'username_normalized' => 'disableduser',
            'password' => bcrypt('password'),
            'status' => UserStatus::INACTIVE,
            'deactivated_at' => now(),
        ]);

        $response = $this->post(route('login.store'), [
            'identifier' => 'disableduser',
            'password' => 'password',
        ]);

        $response->assertSessionHas('login_state', 'disabled');
    }

    public function test_disabled_user_cannot_login(): void
    {
        User::factory()->create([
            'username_normalized' => 'disabled2',
            'password' => bcrypt('password'),
            'status' => UserStatus::INACTIVE,
            'deactivated_at' => now(),
        ]);

        $response = $this->post(route('login.store'), [
            'identifier' => 'disabled2',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionMissing('session_expires_at');
    }

    public function test_error_state_preserves_identifier_input(): void
    {
        $response = $this->post(route('login.store'), [
            'identifier' => 'myuser',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasInput('identifier', 'myuser');
    }

    public function test_normal_login_state_has_no_error_message(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertDontSee('login-error', false);
    }

    public function test_error_state_shows_error_component(): void
    {
        $response = $this->followingRedirects()
            ->post(route('login.store'), [
                'identifier' => 'baduser',
                'password' => 'wrongpass',
            ]);

        $response->assertOk();
        $response->assertSee('login-error', false);
    }
}
