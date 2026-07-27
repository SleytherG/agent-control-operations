<?php

namespace App\Modules\IdentityAccess\Services;

class RefreshTokenService
{
    public function __construct(private string $pepper) {}

    public function generate(): string
    {
        return base64_encode(random_bytes(32));
    }

    public function hash(string $token): string
    {
        return hash_hmac('sha256', $token, $this->pepper);
    }
}
