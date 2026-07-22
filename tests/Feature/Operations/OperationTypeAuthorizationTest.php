<?php

namespace Tests\Feature\Operations;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Operations\Models\OperationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationTypeAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $operator;
    private OperationType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::factory()->create();
        $this->operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->type = OperationType::create([
            'organization_id' => $this->org->id, 'bank_id' => null,
            'name' => 'Test Type', 'cash_direction' => 'ENTRADA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_operator_cannot_access_types_index(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->get(route('admin.operation-types.index'));

        $response->assertForbidden();
    }

    public function test_operator_cannot_create_type(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->post(route('admin.operation-types.store'), [
            'name' => 'Hack Type',
            'cash_direction' => 'ENTRADA',
        ]);

        $response->assertForbidden();
    }

    public function test_operator_cannot_edit_type(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->patch(route('admin.operation-types.update', $this->type), [
            'name' => 'Hacked',
            'cash_direction' => 'SALIDA',
        ]);

        $response->assertForbidden();
    }

    public function test_operator_cannot_deactivate_type(): void
    {
        $this->actingAsJwt($this->operator);

        $response = $this->delete(route('admin.operation-types.destroy', $this->type));

        $response->assertForbidden();
    }
}
