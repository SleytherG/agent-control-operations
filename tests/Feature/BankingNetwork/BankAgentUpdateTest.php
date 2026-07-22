<?php

namespace Tests\Feature\BankingNetwork;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Store;
use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAgentUpdateTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private BankAgent $agent;

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

        $region = Region::create([
            'organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $province = Province::create([
            'organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $district = District::create([
            'organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $store = Store::create([
            'organization_id' => $this->org->id, 'district_id' => $district->id,
            'code' => 'ST-001', 'name' => 'Tienda Test', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $bank = Bank::create([
            'organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->agent = BankAgent::create([
            'organization_id' => $this->org->id, 'store_id' => $store->id,
            'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_admin_can_update_bank_agent(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->patch(route('admin.bank-agents.update', $this->agent), [
            'store_id' => $this->agent->store_id,
            'bank_id' => $this->agent->bank_id,
            'code' => 'AG-002',
            'terminal_code' => 'TERM-002',
        ]);

        $response->assertRedirect(route('admin.bank-agents.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('bank_agents', [
            'id' => $this->agent->id,
            'code' => 'AG-002',
            'terminal_code' => 'TERM-002',
        ]);
    }

    public function test_bank_agent_update_creates_audit_log(): void
    {
        $this->actingAsJwt($this->admin);

        $this->patch(route('admin.bank-agents.update', $this->agent), [
            'store_id' => $this->agent->store_id,
            'bank_id' => $this->agent->bank_id,
            'code' => 'AG-003',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => BankAgent::class,
            'entity_id' => $this->agent->id,
            'action' => 'bank_agent.updated',
        ]);
    }
}
