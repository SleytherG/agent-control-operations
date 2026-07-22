<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecretLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_jwt_refresh_token_password_and_hashes_are_absent_from_logs(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
