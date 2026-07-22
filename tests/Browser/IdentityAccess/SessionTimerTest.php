<?php

namespace Tests\Browser\IdentityAccess;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SessionTimerTest extends DuskTestCase
{
    public function test_timer_calculates_remaining_time_from_meta_tag(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }
}
