<?php

namespace Tests\Integration\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentityAccessMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_run_up_and_down(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
