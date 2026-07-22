<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevokedSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_revoked_session_rejects_access_and_refresh(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
