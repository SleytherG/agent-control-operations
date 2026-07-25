<?php

namespace Tests\Integration\IdentityAccess;

use App\Modules\IdentityAccess\Application\Actions\AuthenticateAndStartSession;
use App\Modules\IdentityAccess\Application\Actions\ResetOperatorPassword;
use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemporaryPasswordConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_double_authentication_creates_only_one_restricted_session(): void
    {
        $admin = User::factory()->administradorPropietario()->create();
        $operator = User::factory()->create(['organization_id' => $admin->organization_id]);
        $issued = app(ResetOperatorPassword::class)->execute($operator, $admin, 'password');
        $action = app(AuthenticateAndStartSession::class);

        $first = $action->execute($operator->username_normalized, $issued['temporary_password']);
        $second = $action->execute($operator->username_normalized, $issued['temporary_password']);

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame(1, AuthSession::whereNotNull('password_reset_id')->count());
    }
}
