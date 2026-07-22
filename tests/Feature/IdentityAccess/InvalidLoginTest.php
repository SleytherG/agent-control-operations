<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InvalidLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_credentials_return_generic_error(): void
    {
        $org = Organization::factory()->create();
        User::factory()->create([
            'organization_id' => $org->id,
            'username_normalized' => 'existing',
            'password' => Hash::make('secret'),
        ]);

        $response = $this->post('/login', [
            'identifier' => 'existing',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
        $this->assertStringContainsString('invalid', strtolower($response->content()));
        $this->assertStringNotContainsString('wrong password', strtolower($response->content()));
        $this->assertStringNotContainsString('not found', strtolower($response->content()));
    }

    public function test_nonexistent_user_gets_same_error_as_wrong_password(): void
    {
        $response = $this->post('/login', [
            'identifier' => 'nonexistent',
            'password' => 'anything',
        ]);

        $response->assertStatus(401);
        $this->assertStringNotContainsString('not found', strtolower($response->content()));
    }
}
