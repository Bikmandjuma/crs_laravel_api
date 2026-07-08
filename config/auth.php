<?php

return [

    'defaults' => [
        'guard' => 'api', // Default guard
        'passwords' => 'users', // Default password reset option
    ],


    'guards' => [
        'api' => [
            'driver' => 'jwt', // JSON Web Token authentication
            'provider' => 'users', // Users provider
        ],

        'admin' => [
            'driver' => 'jwt', // JSON Web Token authentication for admins
            'provider' => 'admins', // Admins provider
        ],

        'user' => [
            'driver' => 'jwt', // JSON Web Token authentication for users
            'provider' => 'users', // Users provider
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class, // Model for users
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class, // Model for admins
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users', // Matches the users provider
            'table' => 'password_reset_tokens', // Password reset tokens table
            'expire' => 60, // Token expiry time in minutes
            'throttle' => 60, // Throttle password reset attempts
        ],
    ],

    'password_timeout' => 10800, // Timeout for password confirmation in seconds

];
