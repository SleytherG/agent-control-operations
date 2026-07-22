<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_200_when_database_is_available(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }

    public function test_health_endpoint_returns_503_when_database_is_unavailable(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }

    public function test_health_endpoint_does_not_expose_sensitive_data(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
