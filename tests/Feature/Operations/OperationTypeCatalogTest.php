<?php

namespace Tests\Feature\Operations;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationTypeCatalogTest extends TestCase
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

    public function test_admin_can_create_type_with_multipliers(): void
    {
        $this->actingAsJwt($this->admin);
        $this->post(route('admin.operation-types.store'), [
            'name' => 'Depósito',
            'cash_multiplier' => '1',
            'digital_multiplier' => '0',
        ])->assertRedirect(route('admin.operation-types.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('operation_types', [
            'name' => 'Depósito',
            'cash_multiplier' => 1,
            'digital_multiplier' => 0,
        ]);
    }

    public function test_admin_can_create_transfer_type(): void
    {
        $this->actingAsJwt($this->admin);
        $this->post(route('admin.operation-types.store'), [
            'name' => 'Transferencia',
            'cash_multiplier' => '1',
            'digital_multiplier' => '-1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('operation_types', [
            'name' => 'Transferencia',
            'cash_multiplier' => 1,
            'digital_multiplier' => -1,
        ]);
    }

    public function test_cash_multiplier_accepts_valid_values(): void
    {
        $this->actingAsJwt($this->admin);

        foreach (['1', '0', '-1'] as $value) {
            $response = $this->post(route('admin.operation-types.store'), [
                'name' => 'Type ' . $value,
                'cash_multiplier' => $value,
                'digital_multiplier' => '0',
            ]);

            if ($value === '0') {
                $this->assertDatabaseHas('operation_types', [
                    'name' => 'Type ' . $value,
                    'cash_multiplier' => (int) $value,
                ]);
            }
        }
    }

    public function test_type_without_cash_multiplier_fails(): void
    {
        $this->actingAsJwt($this->admin);
        $this->post(route('admin.operation-types.store'), [
            'name' => 'Invalid',
            'digital_multiplier' => '0',
        ])->assertSessionHasErrors(['cash_multiplier']);
    }

    public function test_type_with_invalid_multiplier_fails(): void
    {
        $this->actingAsJwt($this->admin);
        $this->post(route('admin.operation-types.store'), [
            'name' => 'Invalid',
            'cash_multiplier' => '999',
            'digital_multiplier' => '0',
        ])->assertSessionHasErrors(['cash_multiplier']);
    }

    public function test_type_supports_sort_order(): void
    {
        $this->actingAsJwt($this->admin);
        $this->post(route('admin.operation-types.store'), [
            'name' => 'Sorted',
            'cash_multiplier' => '0',
            'digital_multiplier' => '0',
            'sort_order' => '5',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('operation_types', [
            'name' => 'Sorted',
            'sort_order' => 5,
        ]);
    }
}
