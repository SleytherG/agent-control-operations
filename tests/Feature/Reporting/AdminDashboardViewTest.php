<?php

namespace Tests\Feature\Reporting;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardViewTest extends TestCase
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

    public function test_admin_dashboard_loads_for_admin(): void
    {
        $user = User::factory()->administradorPropietario()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_operator_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_dashboard_shows_empty_state(): void
    {
        $user = User::factory()->administradorPropietario()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Sin datos', false);
    }
}
