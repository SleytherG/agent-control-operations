<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Domain\Enums\RefreshTokenState;
use App\Modules\IdentityAccess\Models\AuthRefreshToken;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_issues_one_hour_cycle_and_revokes_target_sessions_only(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $targetSession = AuthSession::factory()->create(['user_id' => $operator->id]);
        $refresh = AuthRefreshToken::factory()->create(['auth_session_id' => $targetSession->id]);
        $this->actingAsJwt($admin);

        $response = $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), [
            'admin_password' => 'password',
            'reason' => 'Olvido reportado',
        ])->assertOk();

        $reset = PasswordReset::where('user_id', $operator->id)->sole();
        $this->assertEquals(3600, $reset->issued_at->diffInSeconds($reset->expires_at));
        $this->assertSame(PasswordResetStatus::PENDING, $reset->status);
        $this->assertSame(AuthSessionStatus::REVOKED, $targetSession->fresh()->status);
        $this->assertSame(RefreshTokenState::REVOKED, $refresh->fresh()->state);
        $this->assertAuthenticatedAs($admin);
        $this->assertNotEmpty($response->json('temporaryPassword'));
    }

    public function test_second_reset_supersedes_previous_cycle(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), ['admin_password' => 'password'])->assertOk();
        $first = PasswordReset::where('user_id', $operator->id)->sole();
        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), ['admin_password' => 'password'])->assertOk();

        $this->assertSame(PasswordResetStatus::SUPERSEDED, $first->fresh()->status);
        $this->assertSame(1, PasswordReset::where('user_id', $operator->id)
            ->where('status', PasswordResetStatus::PENDING)->count());
    }
}
