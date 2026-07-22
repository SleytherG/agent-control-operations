<?php

namespace Tests\Browser\IdentityAccess;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SessionAccessibilityTest extends DuskTestCase
{
    public function test_keyboard_focus_and_screen_reader_accessibility(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }

    public function test_responsive_layout_across_viewports(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }
}
