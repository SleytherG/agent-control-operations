<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginWithUsernameTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_username_creates_session_and_redirects(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create([
            'organization_id' => $org->id,
            'username_normalized' => 'jperez',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post('/login', [
            'identifier' => 'jperez',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard.operator'));
        $this->assertEquals(1, AuthSession::where('user_id', $user->id)->count());
        $session = AuthSession::where('user_id', $user->id)->first();
        $this->assertNotNull($session->public_id);
        $this->assertEquals('ACTIVE', $session->status->value);
    }
}
