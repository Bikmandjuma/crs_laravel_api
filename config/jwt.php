<?php

return [
    'secret' => env('JWT_SECRET'),

    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    'ttl' => 60,  // Time-to-live for JWT token in minutes

    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),  // Refresh TTL in minutes

    'algo' => env('JWT_ALGO', 'HS256'),  // JWT algorithm

    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],

    'persistent_claims' => [
        // 'foo',
        // 'bar',
    ],

    'lock_subject' => true,  // Lock the subject of the JWT token

    'leeway' => env('JWT_LEEWAY', 0),  // Allow some leeway for token expiration

    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),  // Enable blacklist for revoked tokens

    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),  // Grace period for blacklist

    'show_black_list_exception' => env('JWT_SHOW_BLACKLIST_EXCEPTION', true),  // Show exception for blacklisted tokens

    'decrypt_cookies' => false,  // Whether to decrypt cookies

    'providers' => [
        'jwt' => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,  // JWT provider
        'user' => Tymon\JWTAuth\Providers\User\Illuminate::class,  // User provider
        'auth' => Tymon\JWTAuth\Providers\Auth\Illuminate::class,  // Auth provider
        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,  // Storage provider
    ],
];
