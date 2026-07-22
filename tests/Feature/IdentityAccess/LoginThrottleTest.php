<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_five_failures_block_sixth_attempt(): void
    {
        $org = Organization::factory()->create();
        User::factory()->create([
            'organization_id' => $org->id,
            'username_normalized' => 'blocked',
            'password' => Hash::make('correct'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'identifier' => 'blocked',
                'password' => 'wrong',
            ])->assertStatus(401);
        }

        $this->post('/login', [
            'identifier' => 'blocked',
            'password' => 'correct',
        ])->assertStatus(429);
    }

    public function test_successful_login_resets_throttle(): void
    {
        $org = Organization::factory()->create();
        User::factory()->create([
            'organization_id' => $org->id,
            'username_normalized' => 'reset',
            'password' => Hash::make('correct'),
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'identifier' => 'reset',
                'password' => 'wrong',
            ])->assertStatus(401);
        }

        $this->post('/login', [
            'identifier' => 'reset',
            'password' => 'correct',
        ])->assertRedirect('/home');
    }
}
