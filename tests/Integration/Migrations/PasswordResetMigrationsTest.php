<?php

namespace Tests\Integration\Migrations;

use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\PasswordReset;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PasswordResetMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_schema_and_nullable_session_link_are_available(): void
    {
        $this->assertTrue(Schema::hasColumns('password_resets', [
            'public_id', 'organization_id', 'user_id', 'initiated_by_user_id', 'status',
            'issued_at', 'expires_at', 'consumed_at', 'completed_at', 'superseded_at', 'reason',
        ]));
        $this->assertTrue(Schema::hasColumn('auth_sessions', 'password_reset_id'));

        $session = AuthSession::factory()->create();

        $this->assertNull($session->password_reset_id);
    }

    public function test_only_one_session_can_link_to_a_password_reset(): void
    {
        $operator = User::factory()->create();
        $admin = User::factory()->administradorPropietario()->create([
            'organization_id' => $operator->organization_id,
        ]);
        $reset = PasswordReset::factory()->create([
            'organization_id' => $operator->organization_id,
            'user_id' => $operator->id,
            'initiated_by_user_id' => $admin->id,
        ]);

        AuthSession::factory()->create([
            'user_id' => $operator->id,
            'password_reset_id' => $reset->id,
        ]);

        $this->expectException(QueryException::class);

        AuthSession::factory()->create([
            'user_id' => $operator->id,
            'password_reset_id' => $reset->id,
        ]);
    }
}
