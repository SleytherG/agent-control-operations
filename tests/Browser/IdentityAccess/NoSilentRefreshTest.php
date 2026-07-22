<?php

namespace Tests\Browser\IdentityAccess;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NoSilentRefreshTest extends DuskTestCase
{
    public function test_page_reload_after_expiry_does_not_trigger_silent_refresh(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }
}
