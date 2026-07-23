<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_guest_layout(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('guest-layout', false);
        $response->assertSee('guest-content', false);
    }

    public function test_login_page_renders_stitch_components(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('login-card', false);
        $response->assertSee('login-header', false);
        $response->assertSee('AgenteFlow', false);
    }

    public function test_login_page_renders_username_and_password_inputs(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('name="identifier"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_login_with_valid_credentials_redirects_to_home(): void
    {
        $this->markTestSkipped('Requires MySQL for session_events.created_at auto-population.');

        User::factory()->create([
            'username_normalized' => 'testuser',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.store'), [
            'identifier' => 'testuser',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
    }

    public function test_login_with_invalid_credentials_shows_error(): void
    {
        $response = $this->post(route('login.store'), [
            'identifier' => 'nonexistent',
            'password' => 'wrong',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('login_state', 'error');
    }

    public function test_login_with_throttle_shows_throttled_state(): void
    {
        User::factory()->create([
            'username_normalized' => 'throttleduser',
            'password' => bcrypt('password'),
        ]);

        $maxAttempts = config('session-security.throttle.max_attempts', 5);

        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->post(route('login.store'), [
                'identifier' => 'throttleduser',
                'password' => 'wrong',
            ]);
        }

        $response = $this->post(route('login.store'), [
            'identifier' => 'throttleduser',
            'password' => 'wrong',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('login_state', 'throttled');
    }
}
