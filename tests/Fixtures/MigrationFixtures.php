<?php

namespace Tests\Fixtures;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Store;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\Region;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\BankingNetwork\Models\UserBankAgentAssignment;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Operations\Models\Operation;

class MigrationFixtures
{
    public Organization $org;
    public User $admin;
    public User $operator;
    public Region $region;
    public Province $province;
    public District $district;
    public Store $store;
    public Bank $bank;
    public BankAgent $bankAgent;
    public OperationType $type;
    public array $operations = [];

    public function create(): static
    {
        $this->org = Organization::factory()->create();

        $this->admin = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::ADMINISTRADOR_PROPIETARIO,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->operator = User::factory()->create([
            'organization_id' => $this->org->id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
            'password_changed_at' => now(),
        ]);

        $this->region = Region::create([
            'organization_id' => $this->org->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->province = Province::create([
            'organization_id' => $this->org->id,
            'region_id' => $this->region->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->district = District::create([
            'organization_id' => $this->org->id,
            'province_id' => $this->province->id,
            'name' => 'Miraflores',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->store = Store::create([
            'organization_id' => $this->org->id,
            'district_id' => $this->district->id,
            'code' => 'ST-FIXTURE',
            'name' => 'Tienda Fixture',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->bank = Bank::create([
            'organization_id' => $this->org->id,
            'code' => 'BANK-FIX',
            'name' => 'Banco Fixture',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->bankAgent = BankAgent::create([
            'organization_id' => $this->org->id,
            'store_id' => $this->store->id,
            'bank_id' => $this->bank->id,
            'code' => 'BA-FIXTURE',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UserBankAgentAssignment::create([
            'user_id' => $this->operator->id,
            'bank_agent_id' => $this->bankAgent->id,
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->type = OperationType::create([
            'organization_id' => $this->org->id,
            'bank_id' => null,
            'name' => 'Depósito Fixture',
            'cash_direction' => 'ENTRADA',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->operations[] = Operation::create([
                'organization_id' => $this->org->id,
                'store_id' => $this->store->id,
                'bank_agent_id' => $this->bankAgent->id,
                'operation_type_id' => $this->type->id,
                'user_id' => $this->operator->id,
                'amount' => 100.00 + ($i * 50),
                'currency' => 'PEN',
                'effective_at' => now()->subDays(7 - $i),
                'recorded_at' => now()->subDays(7 - $i),
                'status' => 'ACTIVE',
                'idempotency_key' => hash('sha256', 'migration-fixture-op-' . $i . '-' . time()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $this;
    }

    public function baselineCounts(): array
    {
        return [
            'stores' => Store::count(),
            'banks' => Bank::count(),
            'bank_agents' => BankAgent::count(),
            'user_bank_agent_assignments' => UserBankAgentAssignment::count(),
            'operation_types' => OperationType::count(),
            'operations' => Operation::count(),
        ];
    }
}
