<?php

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentityAccessPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_cycle_p95_under_two_seconds(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }

    public function test_auth_cycle_query_count_is_within_budget(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
