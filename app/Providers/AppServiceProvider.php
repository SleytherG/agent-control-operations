<?php

namespace App\Providers;

use App\Modules\IdentityAccess\Services\JwtTokenService;
use Illuminate\Support\ServiceProvider;
use Psr\Clock\ClockInterface;
use Psr\Clock\Clock;

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
            return new JwtTokenService(
                signingKey: config('session-security.jwt.signing_key'),
                issuer: config('session-security.jwt.issuer'),
                audience: config('session-security.jwt.audience'),
                clock: $this->app->make(ClockInterface::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
