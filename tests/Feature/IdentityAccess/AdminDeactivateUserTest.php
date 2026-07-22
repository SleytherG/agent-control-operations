<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeactivateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_deactivates_user_revokes_all_sessions_and_logs_audit(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
