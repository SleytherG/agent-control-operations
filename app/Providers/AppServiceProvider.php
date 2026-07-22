<?php

namespace App\Providers;

use App\Modules\IdentityAccess\Services\JwtTokenService;
use App\Modules\IdentityAccess\Services\RefreshTokenService;
use Illuminate\Support\ServiceProvider;
use Psr\Clock\ClockInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ClockInterface::class, fn () => new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            }
        });

        $this->app->singleton(JwtTokenService::class, function () {
            $signingKey = config('session-security.jwt.signing_key');

            throw_if(empty($signingKey), \RuntimeException::class,
                'JWT_SIGNING_KEY no puede estar vacía. Configure una clave HMAC-SHA256 de al menos 256 bits.'
            );

            return new JwtTokenService(
                signingKey: $signingKey,
                issuer: config('session-security.jwt.issuer'),
                audience: config('session-security.jwt.audience'),
                clock: $this->app->make(ClockInterface::class),
            );
        });

        $this->app->singleton(RefreshTokenService::class, function () {
            return new RefreshTokenService(
                pepper: config('session-security.refresh.pepper', ''),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
