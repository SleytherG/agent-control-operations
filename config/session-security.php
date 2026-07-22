<?php

return [

    'jwt' => [
        'signing_key' => env('JWT_SIGNING_KEY'),
        'issuer' => env('JWT_ISSUER', 'control-operaciones-agente'),
        'audience' => env('JWT_AUDIENCE', 'control-operaciones-agente'),
        'access_ttl' => (int) env('JWT_ACCESS_TTL', 300),
    ],

    'session' => [
        'absolute_ttl' => (int) env('JWT_ABSOLUTE_SESSION_TTL', 28800),
    ],

    'refresh' => [
        'pepper' => env('REFRESH_PEPPER'),
        'length' => 32,
    ],

    'cookies' => [
        'access_name' => '__Host-access_token',
        'refresh_name' => '__Host-refresh_token',
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'same_site' => 'strict',
        'path' => '/',
    ],

    'throttle' => [
        'max_attempts' => 5,
        'decay_seconds' => 60,
    ],

    'history' => [
        'default_page_size' => 25,
        'max_page_size' => 100,
    ],

];
