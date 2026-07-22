<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrowserCloseExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_browser_close_derives_expiry_without_logout(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
