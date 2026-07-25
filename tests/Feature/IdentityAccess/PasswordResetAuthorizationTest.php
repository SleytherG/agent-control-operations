<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_admin_can_reset_active_operator_in_same_organization(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), [
            'admin_password' => 'password',
        ])->assertOk()->assertJsonStructure(['temporaryPassword', 'issuedAt', 'expiresAt', 'deliveryWarning']);
    }

    public function test_unauthorized_actor_or_target_is_rejected_without_mutation(): void
    {
        $organization = Organization::factory()->create();
        $operatorActor = User::factory()->create([
            'organization_id' => $organization->id,
            'password_changed_at' => now(),
        ]);
        $targetAdmin = User::factory()->administradorPropietario()->create(['organization_id' => $organization->id]);
        $target = User::factory()->create();
        $originalHash = $target->password;

        $this->actingAsJwt($operatorActor);
        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $targetAdmin), [
            'admin_password' => 'password',
        ])->assertForbidden();

        $owner = User::factory()->administradorPropietario()->create(['organization_id' => $organization->id]);
        $this->actingAsJwt($owner);
        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $target), [
            'admin_password' => 'password',
        ])->assertForbidden();

        $this->assertSame($originalHash, $target->fresh()->password);
    }

    public function test_non_active_operator_returns_conflict_without_reset(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->inactive()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        $this->withHeader('Accept', 'application/json')->post(route('admin.users.password-reset', $operator), [
            'admin_password' => 'password',
        ])->assertStatus(409);

        $this->assertDatabaseMissing('password_resets', ['user_id' => $operator->id]);
        $this->assertSame(UserStatus::INACTIVE, $operator->fresh()->status);
    }
}
