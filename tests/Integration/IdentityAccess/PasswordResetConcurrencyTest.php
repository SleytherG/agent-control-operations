<?php

namespace Tests\Integration\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_resets_leave_only_latest_cycle_pending(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), ['admin_password' => 'password'])->assertOk();
        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), ['admin_password' => 'password'])->assertOk();

        $this->assertSame(1, PasswordReset::where('user_id', $operator->id)
            ->where('status', PasswordResetStatus::PENDING)->count());
        $this->assertSame(1, PasswordReset::where('user_id', $operator->id)
            ->where('status', PasswordResetStatus::SUPERSEDED)->count());
    }
}
