<?php

namespace Tests\Feature;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UiVisualStabilizationTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        $session = AuthSession::create([
            'public_id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'status' => AuthSessionStatus::ACTIVE,
            'started_at' => now(),
            'access_expires_at' => now()->addMinutes(5),
            'absolute_expires_at' => now()->addHours(8),
        ]);

        return app(JwtTokenService::class)->issue((string) $user->id, $session->public_id)['token'];
    }

    public function test_admin_home_uses_real_identity_and_single_shared_session_timer(): void
    {
        $user = User::factory()->administradorPropietario()->create([
            'username_normalized' => 'admin-real',
            'password_changed_at' => now(),
        ]);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $this->tokenFor($user))
            ->get(route('home'));

        $response->assertOk()
            ->assertSee('Control de operaciones')
            ->assertSee('Bienvenido, admin-real')
            ->assertSee('class="session-indicator"', false)
            ->assertDontSee('Financial Operations')
            ->assertDontSee('Carlos López')
            ->assertDontSee('Tienda Centro')
            ->assertDontSee('Tiempo restante de sesión:')
            ->assertDontSee('--:--');

        $this->assertSame(1, substr_count($response->getContent(), 'class="session-indicator"'));
    }

    public function test_operation_registration_navigation_matches_server_authorization(): void
    {
        $admin = User::factory()->administradorPropietario()->create(['password_changed_at' => now()]);
        $adminResponse = $this->withCookie(config('session-security.cookies.access_name'), $this->tokenFor($admin))
            ->get(route('home'));

        $adminResponse->assertOk()->assertDontSee('Registrar Operación');

        $this->withCookie(config('session-security.cookies.access_name'), $this->tokenFor($admin))
            ->get(route('operations.create'))
            ->assertForbidden();

        $operator = User::factory()->create(['password_changed_at' => now()]);
        $this->withCookie(config('session-security.cookies.access_name'), $this->tokenFor($operator))
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Registrar Operación');
    }

    public function test_admin_lists_do_not_render_literal_php_or_blade_source(): void
    {
        $admin = User::factory()->administradorPropietario()->create(['password_changed_at' => now()]);
        $token = $this->tokenFor($admin);

        foreach ([
            'admin.stores.index',
            'admin.users.index',
            'admin.banks.index',
            'admin.bank-agents.index',
            'admin.operation-types.index',
            'sessions.index',
        ] as $route) {
            $this->withCookie(config('session-security.cookies.access_name'), $token)
                ->get(route($route))
                ->assertOk()
                ->assertDontSee('$actions', false)
                ->assertDontSee('->toArray()', false)
                ->assertDontSee('<x-ui.badge', false)
                ->assertDontSee('emptyMessage=', false);
        }
    }

    public function test_history_icons_are_not_html_escaped(): void
    {
        $admin = User::factory()->administradorPropietario()->create(['password_changed_at' => now()]);

        $this->withCookie(config('session-security.cookies.access_name'), $this->tokenFor($admin))
            ->get(route('operations.index'))
            ->assertOk()
            ->assertDontSee('&amp;#x1F4CB;', false)
            ->assertSee('metric-card--dark', false)
            ->assertSee('Movimiento Neto');
    }

    public function test_daily_closings_index_uses_stitch_components_and_empty_state(): void
    {
        $admin = User::factory()->administradorPropietario()->create(['password_changed_at' => now()]);

        $this->withCookie(config('session-security.cookies.access_name'), $this->tokenFor($admin))
            ->get(route('daily-closures.index'))
            ->assertOk()
            ->assertSee('class="filter-bar"', false)
            ->assertSee('class="data-table"', false)
            ->assertSee('No se encontraron cierres diarios.')
            ->assertSee('class="pagination"', false)
            ->assertDontSee('IDAgenteFechaEstadoOperacionesMonto BrutoNetoAcciones');
    }
}
