<?php

namespace Tests\Feature\DailyClosing;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\JwtTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClosingViewTest extends TestCase
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

    public function test_daily_closings_index_loads(): void
    {
        $user = User::factory()->create(['password_changed_at' => now()]);
        $jwt = $this->authenticateUser($user);

        $response = $this->withCookie(config('session-security.cookies.access_name'), $jwt)
            ->get(route('daily-closures.index'));

        $response->assertOk();
    }

    public function test_daily_closings_requires_authentication(): void
    {
        $response = $this->get(route('daily-closures.index'));

        $response->assertRedirect(route('login'));
    }
}
