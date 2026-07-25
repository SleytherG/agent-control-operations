<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginWithEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_normalized_email(): void
    {
        $org = Organization::factory()->create();
        User::factory()->create([
            'organization_id' => $org->id,
            'email_normalized' => 'ana@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->post('/login', [
            'identifier' => '  ana@EXAMPLE.com  ',
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard.operator'));
    }

    public function test_login_with_space_padded_email(): void
    {
        $org = Organization::factory()->create();
        User::factory()->create([
            'organization_id' => $org->id,
            'email_normalized' => 'luis@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->post('/login', [
            'identifier' => ' luis@example.com ',
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard.operator'));
    }
}
