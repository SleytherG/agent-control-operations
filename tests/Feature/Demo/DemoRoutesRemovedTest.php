<?php

namespace Tests\Feature\Demo;

use Tests\TestCase;

class DemoRoutesRemovedTest extends TestCase
{
    public function test_demo_login_returns_404(): void
    {
        $this->get('/demo/login')->assertNotFound();
    }

    public function test_demo_operator_dashboard_returns_404(): void
    {
        $this->get('/demo/operator/dashboard')->assertNotFound();
    }

    public function test_demo_admin_dashboard_returns_404(): void
    {
        $this->get('/demo/admin/dashboard')->assertNotFound();
    }

    public function test_demo_daily_closing_returns_404(): void
    {
        $this->get('/demo/daily-closing/1')->assertNotFound();
    }
}
