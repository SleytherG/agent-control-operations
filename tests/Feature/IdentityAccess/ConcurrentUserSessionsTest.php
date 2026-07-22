<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Models\AuthSession;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConcurrentUserSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_login_creates_independent_session(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create([
            'organization_id' => $org->id,
            'username_normalized' => 'multi',
            'password' => Hash::make('secret123'),
        ]);

        $this->post('/login', [
            'identifier' => 'multi',
            'password' => 'secret123',
        ]);

        $this->post('/login', [
            'identifier' => 'multi',
            'password' => 'secret123',
        ]);

        $this->assertEquals(2, AuthSession::where('user_id', $user->id)->count());
    }
}
