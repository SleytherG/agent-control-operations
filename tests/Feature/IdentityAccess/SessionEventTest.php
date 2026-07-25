<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\SessionEventType;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\SessionEvent;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_event_create_includes_created_at(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $session = AuthSession::create([
            'public_id' => 'test-pid',
            'user_id' => $user->id,
            'status' => 'ACTIVE',
            'started_at' => now(),
            'access_expires_at' => now()->addMinutes(5),
            'absolute_expires_at' => now()->addHours(8),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $now = now();
        $event = SessionEvent::create([
            'auth_session_id' => $session->id,
            'user_id' => $user->id,
            'type' => SessionEventType::LOGIN->value,
            'occurred_at' => $now,
            'created_at' => $now,
        ]);

        $this->assertDatabaseHas('session_events', [
            'id' => $event->id,
            'auth_session_id' => $session->id,
            'user_id' => $user->id,
            'type' => 'LOGIN',
        ]);

        $fromDb = SessionEvent::find($event->id);
        $this->assertNotNull($fromDb->created_at);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $fromDb->created_at,
        );
    }
}
