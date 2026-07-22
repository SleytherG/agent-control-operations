<?php

namespace Tests\Integration\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeactivateUserRaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivation_race_with_login_and_refresh_preserves_consistency(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
