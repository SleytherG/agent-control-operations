<?php

namespace Tests\Feature\DailyClosing;

use App\Modules\BankingNetwork\Models\Bank;
use App\Modules\BankingNetwork\Models\BankAgent;
use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClosingEmptyAgentTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;
    private BankAgent $agent;
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

        $this->businessDate = now()->format('Y-m-d');
    }

    public function test_agent_without_operations_shows_zero_metrics(): void
    {
        $this->actingAsJwt($this->admin);

        $response = $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $closure = DailyClosure::first();
        $this->assertNotNull($closure);
        $this->assertEquals(0, $closure->operation_count);
        $this->assertEquals(0, (float) (string) $closure->gross_amount);
        $this->assertEquals(0, (float) (string) $closure->cash_in);
        $this->assertEquals(0, (float) (string) $closure->cash_out);
        $this->assertEquals(0, (float) (string) $closure->net_movement);
        $this->assertFalse($closure->has_pending_confirm);
    }

    public function test_empty_closure_view_shows_zeros(): void
    {
        $this->actingAsJwt($this->admin);

        $this->post(route('daily-closures.store'), [
            'bank_agent_id' => $this->agent->id,
            'business_date' => $this->businessDate,
        ]);

        $closure = DailyClosure::first();
        $response = $this->get(route('daily-closures.show', $closure));
        $response->assertOk();
        $response->assertSee('Sin operaciones');
    }
}
