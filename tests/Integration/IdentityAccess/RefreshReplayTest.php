<?php

namespace Tests\Integration\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefreshReplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_replay_of_consumed_refresh_token_revokes_session(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
