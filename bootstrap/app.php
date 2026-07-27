<?php

use App\Http\Middleware\AddNoStoreHeaders;
use App\Http\Middleware\AssignCorrelationId;
use App\Http\Middleware\AuthenticateJwtSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', null));

        $middleware->web(append: [
            AssignCorrelationId::class,
            AddNoStoreHeaders::class,
        ]);

        $middleware->alias([
            'auth.jwt' => AuthenticateJwtSession::class,
            'no.store' => AddNoStoreHeaders::class,
            'correlation' => AssignCorrelationId::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->dontReport(\Lcobucci\JWT\Exception::class);
    })->create();
