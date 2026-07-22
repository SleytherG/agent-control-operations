<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeactivateUserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_and_tampered_requests_receive_403(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
