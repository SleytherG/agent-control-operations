<?php

namespace App\Modules\IdentityAccess\Services;

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

    public function withAuthCookies(Response $response, string $accessToken, string $refreshToken, int $ttl): Response
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

    public function clearAuthCookies(Response $response): Response
    {
        return $response
            ->withoutCookie($this->accessName, $this->path)
            ->withoutCookie($this->refreshName, $this->path);
    }
}
