<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_lists_only_sanitized_password_reset_events_with_pagination(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);

        foreach (range(1, 30) as $index) {
            AuditLog::create([
                'organization_id' => $admin->organization_id,
                'actor_user_id' => $admin->id,
                'action' => $index % 2 ? 'password_reset.issued' : 'password_reset.completed',
                'entity_type' => User::class,
                'entity_id' => $operator->id,
                'before_values' => ['status' => 'PENDING'],
                'after_values' => ['status' => 'COMPLETED', 'reset_public_id' => (string) Str::uuid()],
                'occurred_at' => now()->subMinutes($index),
                'correlation_id' => (string) Str::uuid(),
                'created_at' => now(),
            ]);
        }

        $this->actingAsJwt($admin);
        $response = $this->get(route('admin.users.password-resets.index', [
            'user' => $operator,
            'status' => 'COMPLETED',
        ]))->assertOk();

        $response->assertViewHas('events', fn ($events) => $events->perPage() === 25);
        $response->assertSee('Completado')->assertDontSee('password_hash');
    }
}
