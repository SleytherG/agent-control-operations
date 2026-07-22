<?php

namespace Tests\Integration\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefreshTransactionFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_rollback_does_not_classify_as_replay(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
