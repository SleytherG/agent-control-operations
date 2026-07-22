<?php

namespace App\Modules\IdentityAccess\Services;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Psr\Clock\ClockInterface;

class JwtTokenService
{
    private Configuration $config;
    private string $issuer;
    private string $audience;

    public function __construct(
        string $signingKey,
        string $issuer,
        string $audience,
        private ClockInterface $clock,
    ) {
        $this->issuer = $issuer;
        $this->audience = $audience;
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($signingKey),
        );
    }

    public function issue(string $sub, string $sid): array
    {
        $now = $this->clock->now();
        $ttl = config('session-security.jwt.access_ttl', 300);
        $expiresAt = $now->modify("+{$ttl} seconds");

        $token = $this->config->builder()
            ->issuedBy($this->issuer)
            ->permittedFor($this->audience)
            ->relatedTo($sub)
            ->withClaim('sid', $sid)
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->getToken($this->config->signer(), $this->config->signingKey());

        return [
            'token' => $token->toString(),
            'expires_at' => $expiresAt,
        ];
    }

    public function validate(string $jwt): ?array
    {
        try {
            $token = $this->config->parser()->parse($jwt);

            $constraints = [
                new SignedWith($this->config->signer(), $this->config->signingKey()),
                new IssuedBy($this->issuer),
                new PermittedFor($this->audience),
                new StrictValidAt($this->clock),
            ];

            $this->config->validator()->assert($token, ...$constraints);

            return [
                'sub' => $token->claims()->get('sub'),
                'sid' => $token->claims()->get('sid'),
                'jti' => $token->claims()->get('jti'),
                'exp' => $token->claims()->get('exp'),
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
