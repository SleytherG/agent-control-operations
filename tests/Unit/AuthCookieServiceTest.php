<?php

namespace Tests\Unit;

use App\Modules\IdentityAccess\Services\AuthCookieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthCookieServiceTest extends TestCase
{
    private AuthCookieService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuthCookieService();
    }

    public function test_with_auth_cookies_accepts_response(): void
    {
        $response = new Response('ok');

        $result = $this->service->withAuthCookies($response, 'access', 'refresh', 300);

        $this->assertInstanceOf(Response::class, $result);
    }

    public function test_with_auth_cookies_accepts_redirect_response(): void
    {
        $response = new RedirectResponse('/home');

        $result = $this->service->withAuthCookies($response, 'access', 'refresh', 300);

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function test_clear_auth_cookies_accepts_response(): void
    {
        $response = new Response('ok');

        $result = $this->service->clearAuthCookies($response);

        $this->assertInstanceOf(Response::class, $result);
    }

    public function test_clear_auth_cookies_accepts_redirect_response(): void
    {
        $response = new RedirectResponse('/login');

        $result = $this->service->clearAuthCookies($response);

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
