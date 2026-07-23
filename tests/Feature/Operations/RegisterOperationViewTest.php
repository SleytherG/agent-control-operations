<?php

namespace Tests\Feature\Operations;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterOperationViewTest extends TestCase
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

        return app(JwtTokenService::class)->issue((string) $user->id, $session->public_id)['token'];
    }

    public function test_registration_form_loads_successfully(): void
    {
        $user = User::factory()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('operations.create'));

        $response->assertOk();
        $response->assertSee('Registro Rapido', false);
    }

    public function test_registration_form_requires_authentication(): void
    {
        $response = $this->get(route('operations.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_registration_shows_empty_state_when_no_assignments(): void
    {
        $user = User::factory()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('operations.create'));

        $response->assertOk();
        $response->assertSee('Sin agentes asignados', false);
    }
}
