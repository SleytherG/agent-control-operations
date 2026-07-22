<?php

namespace Tests\Feature\BankingNetwork;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\BankingNetwork\Models\Bank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankTest extends TestCase
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

    public function test_admin_can_create_bank(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->post(route('admin.banks.store'), [
            'code' => 'BCP',
            'name' => 'Banco de Crédito',
        ]);

        $response->assertRedirect(route('admin.banks.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('banks', [
            'organization_id' => $this->org->id,
            'code' => 'BCP',
            'name' => 'Banco de Crédito',
            'is_active' => true,
        ]);
    }

    public function test_bank_code_must_be_unique_per_org(): void
    {
        $this->actingAsJwt($this->admin);

        Bank::create([
            'organization_id' => $this->org->id,
            'code' => 'BCP',
            'name' => 'BCP',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('admin.banks.store'), [
            'code' => 'BCP',
            'name' => 'Duplicado',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_admin_can_deactivate_bank(): void
    {
        $this->actingAsJwt($this->admin);

        $bank = Bank::create([
            'organization_id' => $this->org->id,
            'code' => 'IBK',
            'name' => 'Interbank',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->delete(route('admin.banks.deactivate', $bank));

        $response->assertRedirect(route('admin.banks.index'));
        $this->assertDatabaseHas('banks', ['id' => $bank->id, 'is_active' => false]);
    }

    public function test_operator_cannot_create_bank(): void
    {
        $operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);
        $this->actingAsJwt($operator);

        $response = $this->post(route('admin.banks.store'), [
            'code' => 'HACK',
            'name' => 'Hack',
        ]);

        $response->assertForbidden();
    }
}
