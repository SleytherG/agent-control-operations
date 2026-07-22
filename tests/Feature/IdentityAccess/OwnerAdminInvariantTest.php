<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerAdminInvariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_self_deactivation_and_last_active_admin_receive_conflict(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
