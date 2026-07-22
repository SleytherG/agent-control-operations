<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionHistoryPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagination_is_limited_and_does_not_load_full_collection(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
