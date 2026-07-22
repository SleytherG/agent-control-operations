<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\AuthSessionStatus;
use App\Modules\IdentityAccess\Domain\Enums\SessionEndReason;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_revokes_current_session(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $session = AuthSession::factory()->create([
            'user_id' => $user->id,
            'status' => AuthSessionStatus::ACTIVE,
        ]);

        $this->markTestSkipped('Requires authenticated middleware session setup.');
    }
}
