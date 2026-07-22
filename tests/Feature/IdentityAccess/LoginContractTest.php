<?php

namespace Tests\Feature\IdentityAccess;

use Tests\TestCase;

class LoginContractTest extends TestCase
{
    public function test_login_form_shows_csrf_and_fields(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('_token');
        $response->assertSee('identifier');
        $response->assertSee('password');
    }

    public function test_login_post_requires_csrf(): void
    {
        $response = $this->post('/login', [
            'identifier' => 'test',
            'password' => 'test',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(419);
    }

    public function test_successful_login_has_not_store_header(): void
    {
        $this->markTestSkipped('Requires full implementation.');
    }
}
