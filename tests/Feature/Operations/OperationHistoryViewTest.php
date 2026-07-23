<?php

namespace Tests\Feature\Operations;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationHistoryViewTest extends TestCase
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

    public function test_operations_index_loads_successfully(): void
    {
        $user = User::factory()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('operations.index'));

        $response->assertOk();
    }

    public function test_operations_index_requires_authentication(): void
    {
        $response = $this->get(route('operations.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operations_index_shows_empty_state(): void
    {
        $user = User::factory()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('operations.index'));

        $response->assertOk();
        $response->assertSee('Sin operaciones', false);
    }

    public function test_operations_index_shows_summary_metrics(): void
    {
        $user = User::factory()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('operations.index'));

        $response->assertOk();
        $response->assertSee('Total Operaciones', false);
        $response->assertSee('Monto Bruto', false);
        $response->assertSee('Movimiento Neto', false);
    }
}
