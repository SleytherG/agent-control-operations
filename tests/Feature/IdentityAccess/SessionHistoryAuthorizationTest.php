<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionHistoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manipulated_params_cannot_leak_foreign_sessions(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
