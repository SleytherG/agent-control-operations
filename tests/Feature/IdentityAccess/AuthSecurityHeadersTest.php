<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_include_no_store_headers(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }

    public function test_responses_do_not_leak_secrets(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
