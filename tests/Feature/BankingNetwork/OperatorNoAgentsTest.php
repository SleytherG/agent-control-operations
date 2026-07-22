<?php

namespace Tests\Feature\BankingNetwork;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorNoAgentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_without_assignments_sees_empty_list(): void
    {
        $org = Organization::factory()->create();
        $operator = User::factory()->create([
            'organization_id' => $org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($operator);

        $response = $this->get(route('my-agents.index'));
        $response->assertStatus(200);
        $response->assertSee('No tienes agentes asignados activos.');
    }
}
