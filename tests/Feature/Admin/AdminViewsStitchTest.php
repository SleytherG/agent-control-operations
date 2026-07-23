<?php

namespace Tests\Feature\Admin;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminViewsStitchTest extends TestCase
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

    public function test_stores_index_loads_for_admin(): void
    {
        $user = User::factory()->administradorPropietario()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('admin.stores.index'));

        $response->assertOk();
    }

    public function test_banks_index_loads_for_admin(): void
    {
        $user = User::factory()->administradorPropietario()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('admin.banks.index'));

        $response->assertOk();
    }

    public function test_operator_blocked_from_admin_routes(): void
    {
        $user = User::factory()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('admin.stores.index'));

        $response->assertStatus(403);
    }
}
