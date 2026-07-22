<?php

namespace Tests\Browser\IdentityAccess;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthenticationCleanupTest extends DuskTestCase
{
    public function test_expired_token_401_cleans_client_state(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }

    public function test_403_does_not_clean_authentication_state(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }
}
