<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetAuditAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_admin_can_view_same_organization_audit(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $this->actingAsJwt($admin);

        $this->get(route('admin.users.password-resets.index', $operator))->assertOk();
    }

    public function test_operator_and_cross_organization_admin_receive_forbidden(): void
    {
        $operator = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAsJwt($operator);
        $this->get(route('admin.users.password-resets.index', $other))->assertForbidden();

        $admin = User::factory()->administradorPropietario()->create();
        $this->actingAsJwt($admin);
        $this->get(route('admin.users.password-resets.index', $other))->assertForbidden();
    }
}
