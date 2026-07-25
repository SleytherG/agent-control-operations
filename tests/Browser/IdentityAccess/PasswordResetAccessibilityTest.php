<?php

namespace Tests\Browser\IdentityAccess;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PasswordResetAccessibilityTest extends DuskTestCase
{
    public function test_modal_focus_moves_to_modal_on_open_and_returns_on_close(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }

    public function test_keyboard_navigation_works_within_reset_modal(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }

    public function test_copy_button_announces_success_to_screen_reader(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }

    public function test_secret_is_removed_from_dom_when_modal_closes(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }

    public function test_back_button_does_not_reexpose_secret(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }

    public function test_page_reload_does_not_reexpose_secret(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }

    public function test_reset_modal_is_usable_across_mobile_tablet_and_desktop_viewports(): void
    {
        $this->markTestSkipped('Requires full browser integration.');
    }
}
