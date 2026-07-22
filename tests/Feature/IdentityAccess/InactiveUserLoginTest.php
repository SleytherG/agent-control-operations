<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveUserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_gets_generic_error_on_login(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
