<?php

namespace App\Modules\IdentityAccess\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthCookieService
{
    private string $accessName;
    private string $refreshName;
    private bool $secure;
    private string $sameSite;
    private string $path;

    public function __construct()
    {
        $config = config('session-security.cookies');
        $this->accessName = $config['access_name'];
        $this->refreshName = $config['refresh_name'];
        $this->secure = $config['secure'];
        $this->sameSite = $config['same_site'];
        $this->path = $config['path'];
    }

    public function withAuthCookies(Response|RedirectResponse|JsonResponse $response, string $accessToken, string $refreshToken, int $ttl): Response|RedirectResponse|JsonResponse
    {
        return $response
            ->withCookie(cookie(
                $this->accessName, $accessToken, $ttl / 60,
                $this->path, null, $this->secure, true, false, $this->sameSite
            ))
            ->withCookie(cookie(
                $this->refreshName, $refreshToken, $ttl / 60,
                $this->path, null, $this->secure, true, false, $this->sameSite
            ));
    }

    public function clearAuthCookies(Response|RedirectResponse $response): Response|RedirectResponse
    {
        return $response
            ->withoutCookie($this->accessName, $this->path)
            ->withoutCookie($this->refreshName, $this->path);
    }
}
