<?php

namespace Tests\Feature\IdentityAccess;

use Tests\TestCase;

class ExpiredAccessTokenTest extends TestCase
{
    public function test_expired_access_token_is_rejected(): void
    {
        $response = $this->withCookie('__Host-access_token', 'expired.jwt.token')
            ->get('/home');

        $response->assertRedirect('/login');
    }

    public function test_malformed_jwt_is_rejected(): void
    {
        $response = $this->withCookie('__Host-access_token', 'not-a-jwt')
            ->get('/home');

        $response->assertRedirect('/login');
    }
}
