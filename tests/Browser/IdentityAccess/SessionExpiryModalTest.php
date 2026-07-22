<?php

namespace Tests\Browser\IdentityAccess;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SessionExpiryModalTest extends DuskTestCase
{
    public function test_modal_shows_at_threshold_and_is_accessible(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }
}
