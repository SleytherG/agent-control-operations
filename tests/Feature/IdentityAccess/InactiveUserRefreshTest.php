<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveUserRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_refresh_session(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
