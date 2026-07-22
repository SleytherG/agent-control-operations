<?php

namespace Tests\Feature\Operations;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\Operations\Models\OperationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationTypeTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::factory()->create();
        $this->admin = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
    }

    public function test_admin_can_create_operation_type(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->post(route('admin.operation-types.store'), [
            'name' => 'Nuevo Tipo',
            'description' => 'Descripción de prueba',
            'cash_direction' => 'ENTRADA',
            'bank_id' => null,
        ]);

        $response->assertRedirect(route('admin.operation-types.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('operation_types', [
            'organization_id' => $this->org->id,
            'name' => 'Nuevo Tipo',
            'cash_direction' => 'ENTRADA',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_edit_operation_type(): void
    {
        $this->actingAsJwt($this->admin);

        $type = OperationType::create([
            'organization_id' => $this->org->id, 'bank_id' => null,
            'name' => 'Original', 'cash_direction' => 'ENTRADA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->patch(route('admin.operation-types.update', $type), [
            'name' => 'Renombrado',
            'cash_direction' => 'SALIDA',
            'bank_id' => null,
        ]);

        $response->assertRedirect(route('admin.operation-types.index'));

        $this->assertDatabaseHas('operation_types', [
            'id' => $type->id,
            'name' => 'Renombrado',
            'cash_direction' => 'SALIDA',
        ]);
    }

    public function test_admin_can_deactivate_operation_type(): void
    {
        $this->actingAsJwt($this->admin);

        $type = OperationType::create([
            'organization_id' => $this->org->id, 'bank_id' => null,
            'name' => 'Para Desactivar', 'cash_direction' => 'NEUTRA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->delete(route('admin.operation-types.destroy', $type));

        $response->assertRedirect(route('admin.operation-types.index'));

        $this->assertDatabaseHas('operation_types', [
            'id' => $type->id,
            'is_active' => false,
        ]);
        $this->assertNotNull(OperationType::find($type->id)->deactivated_at);
    }

    public function test_duplicate_name_same_bank_is_rejected(): void
    {
        $this->actingAsJwt($this->admin);

        OperationType::create([
            'organization_id' => $this->org->id, 'bank_id' => null,
            'name' => 'Tipo Único', 'cash_direction' => 'ENTRADA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->post(route('admin.operation-types.store'), [
            'name' => 'Tipo Único',
            'cash_direction' => 'ENTRADA',
            'bank_id' => null,
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertEquals(1, OperationType::where('name', 'Tipo Único')->whereNull('bank_id')->count());
    }

    public function test_admin_can_create_bank_specific_type(): void
    {
        $this->actingAsJwt($this->admin);

        $bank = Bank::create([
            'organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->post(route('admin.operation-types.store'), [
            'name' => 'Depósito BCP',
            'cash_direction' => 'ENTRADA',
            'bank_id' => $bank->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('operation_types', [
            'organization_id' => $this->org->id,
            'bank_id' => $bank->id,
            'name' => 'Depósito BCP',
        ]);
    }
}
