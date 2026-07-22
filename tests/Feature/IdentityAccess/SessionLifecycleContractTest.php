<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionLifecycleContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_and_logout_endpoints_enforce_csrf_and_cookies(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
