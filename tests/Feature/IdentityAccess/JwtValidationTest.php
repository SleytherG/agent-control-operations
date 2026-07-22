<?php

namespace Tests\Feature\IdentityAccess;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_signature_algorithm_issuer_audience_and_claims_are_rejected(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
