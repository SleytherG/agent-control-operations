<?php

namespace Tests\Feature\Migration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClosuresMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrated_closures_have_agent_id(): void
    {
        $org = \App\Modules\Organization\Models\Organization::factory()->create();

        $agent = \App\Modules\Agents\Models\Agent::create([
            'organization_id' => $org->id,
            'code' => 'CL-MIG', 'name' => 'Closure Agent', 'city' => 'Lima',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        \DB::table('_migration_map')->insert([
            'old_table' => 'bank_agents', 'old_id' => 50, 'new_agent_id' => $agent->id,
            'created_at' => now(),
        ]);

        \DB::table('daily_closures')->insert([
            'organization_id' => $org->id,
            'bank_agent_id' => 50,
            'business_date' => '2026-07-23',
            'status' => \App\Modules\DailyClosing\Models\DailyClosure::STATUS_ACTIVO,
            'agent_id' => null,
            'opening_cash' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNull(\DB::table('daily_closures')->first()->agent_id);

        $migration = include database_path('migrations/2026_07_23_000008_migrate_daily_closures_data.php');
        (new $migration())->up();

        $closure = \DB::table('daily_closures')->first();
        $this->assertNotNull($closure->agent_id);
        $this->assertEquals('2026-07-23', $closure->business_date);
        $this->assertEquals(\App\Modules\DailyClosing\Models\DailyClosure::STATUS_ACTIVO, $closure->status);
    }
}
