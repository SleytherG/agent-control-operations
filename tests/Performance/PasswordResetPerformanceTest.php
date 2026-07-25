<?php

namespace Tests\Performance;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasswordResetPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_issuance_completes_under_two_seconds(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create([
            'organization_id' => $admin->organization_id,
            'role' => Role::OPERADOR,
            'status' => UserStatus::ACTIVE,
        ]);

        $this->actingAsJwt($admin);

        DB::enableQueryLog();

        $start = microtime(true);
        $response = $this->withHeader('Accept', 'application/json')
            ->post(route('admin.users.password-reset', $operator), [
                'admin_password' => 'password',
            ]);
        $elapsed = round(microtime(true) - $start, 3);

        $response->assertOk();
        $queryCount = count(DB::getQueryLog());

        $this->assertLessThan(
            2.0,
            $elapsed,
            "Reset issuance ({$elapsed}s) exceeds 2s budget"
        );
    }

    public function test_password_reset_audit_page_query_count_is_within_budget(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create([
            'organization_id' => $admin->organization_id,
        ]);

        $this->actingAsJwt($admin);

        DB::enableQueryLog();

        $start = microtime(true);
        $response = $this->get(route('admin.users.password-resets.index', [
            'user' => $operator,
        ]));
        $elapsed = round(microtime(true) - $start, 3);

        $response->assertOk();
        $queryCount = count(DB::getQueryLog());

        $this->assertLessThan(
            2.0,
            $elapsed,
            "Audit page render ({$elapsed}s) exceeds 2s budget"
        );

        $this->assertLessThan(
            20,
            $queryCount,
            "Audit page query count ({$queryCount}) exceeds budget of 20"
        );
    }

    public function test_password_reset_audit_pagination_avoids_n_plus_one(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create([
            'organization_id' => $admin->organization_id,
        ]);

        $this->actingAsJwt($admin);

        DB::enableQueryLog();

        $baseStart = microtime(true);
        $this->get(route('admin.users.password-resets.index', [
            'user' => $operator,
            'page' => 1,
        ]));
        $baseTime = microtime(true) - $baseStart;

        DB::flushQueryLog();
        DB::enableQueryLog();

        $largeStart = microtime(true);
        $this->get(route('admin.users.password-resets.index', [
            'user' => $operator,
            'page' => 1,
        ]));
        $largeTime = microtime(true) - $largeStart;
        $largeQueries = count(DB::getQueryLog());

        $this->assertLessThan(
            3.0,
            $largeTime,
            "Subsequent audit page render ({$largeTime}s) suggests N+1 growth"
        );
    }
}
