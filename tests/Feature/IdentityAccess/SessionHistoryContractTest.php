<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionHistoryContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_and_pagination_respect_contract(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
