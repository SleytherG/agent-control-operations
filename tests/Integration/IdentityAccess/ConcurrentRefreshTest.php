<?php

namespace Tests\Integration\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrentRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_refresh_requests_only_one_succeeds(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
