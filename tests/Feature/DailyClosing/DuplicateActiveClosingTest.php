<?php

namespace Tests\Feature\DailyClosing;

use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Operations\Models\Operation;
use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DuplicateActiveClosingTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private BankAgent $agent;
    private OperationType $type;
    private string $businessDate;

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

        $region = Region::create(['organization_id' => $this->org->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $province = Province::create(['organization_id' => $this->org->id, 'region_id' => $region->id, 'name' => 'Lima', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $district = District::create(['organization_id' => $this->org->id, 'province_id' => $province->id, 'name' => 'Miraflores', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $store = Store::create(['organization_id' => $this->org->id, 'district_id' => $district->id, 'code' => 'ST-001', 'name' => 'Test Store', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $bank = Bank::create(['organization_id' => $this->org->id, 'code' => 'BCP', 'name' => 'BCP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->agent = BankAgent::create(['organization_id' => $this->org->id, 'store_id' => $store->id, 'bank_id' => $bank->id, 'code' => 'AG-001', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->type = OperationType::create(['organization_id' => $this->org->id, 'bank_id' => null, 'name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->businessDate = now()->format('Y-m-d');
    }

    public function test_unique_constraint_prevents_two_active_closures_same_agent_date(): void
    {
        Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->type->id,
            'user_id' => $this->admin->id, 'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);

        $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $this->assertEquals(1, DailyClosure::where('status', DailyClosure::STATUS_ACTIVO)->count());

        $response = $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, DailyClosure::where('status', DailyClosure::STATUS_ACTIVO)->count());
    }

    public function test_uniqueness_constraint_allows_after_confirm_and_regenerate(): void
    {
        $operation = Operation::create([
            'organization_id' => $this->org->id, 'store_id' => $this->agent->store_id,
            'bank_agent_id' => $this->agent->id, 'operation_type_id' => $this->type->id,
            'user_id' => $this->admin->id, 'amount' => 100.00, 'currency' => 'PEN',
            'effective_at' => $this->businessDate . ' 10:00:00', 'recorded_at' => now(),
            'status' => Operation::STATUS_ACTIVE,
            'idempotency_key' => \Illuminate\Support\Str::random(64),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);

        $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $closure = DailyClosure::first();
        $this->post(route('daily-closures.confirm', $closure));

        $this->assertEquals(DailyClosure::STATUS_CONFIRMADO, $closure->fresh()->status);

        $nextDate = now()->subDay()->format('Y-m-d');
        $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $nextDate,
        ]);

        $this->assertEquals(2, DailyClosure::count());
    }
}
