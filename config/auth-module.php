<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Module Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Laravel Auth Module package.
    | You can customize routes, middleware, email settings, and more.
    |
    */

    'routes' => [
        /*
        |--------------------------------------------------------------------------
        | Route Prefix
        |--------------------------------------------------------------------------
        |
        | The prefix for all authentication routes. Default is 'auth'.
        | This will create routes like: /auth/login, /auth/register, etc.
        |
        */
        'prefix' => 'auth',

        /*
        |--------------------------------------------------------------------------
        | Route Middleware
        |--------------------------------------------------------------------------
        |
        | Middleware applied to all authentication routes.
        |
        */
        'middleware' => ['auth-api'],

        /*
        |--------------------------------------------------------------------------
        | API Routes
        |--------------------------------------------------------------------------
        |
        | Enable or disable specific authentication routes.
        |
        */
        'enable_routes' => [
            'login' => true,
            'register' => true,
            'logout' => true,
            'refresh' => true,
            'me' => true,
            'forgot_password' => true,
            'reset_password' => true,
            'verify_email' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    |
    | JWT token configuration for authentication.
    |
    */
    'jwt' => [
        'secret' => env('JWT_SECRET'),
        'ttl' => env('JWT_TTL', 60), // minutes
        'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // minutes
        'algo' => env('JWT_ALGO', 'HS256'),
        'required_claims' => [
            'iss',
            'iat',
            'exp',
            'nbf',
            'sub',
            'jti',
        ],
        'persistent_claims' => [],
        'lock_subject' => true,
        'leeway' => env('JWT_LEEWAY', 0),
        'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),
        'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),
        'decrypt_cookies' => false,
        'providers' => [
            'jwt' => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,
            'auth' => Tymon\JWTAuth\Providers\Auth\Illuminate::class,
            'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model to use for authentication.
    |
    */
    'user_model' => env('AUTH_MODULE_USER_MODEL', App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Password Reset Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for password reset functionality.
    |
    */
    'password_reset' => [
        'token_length' => 64,
        'token_expiry' => 60, // minutes
        'table' => 'password_reset_tokens',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    |
    | Configuration for email verification.
    |
    */
    'email_verification' => [
        'enabled' => env('AUTH_MODULE_EMAIL_VERIFICATION', true),
        'token_expiry' => 60, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for authentication endpoints.
    |
    */
    'rate_limiting' => [
        'login' => 'throttle:5,1', // 5 attempts per minute
        'register' => 'throttle:3,1', // 3 attempts per minute
        'forgot_password' => 'throttle:3,1', // 3 attempts per minute
        'reset_password' => 'throttle:3,1', // 3 attempts per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Custom validation rules for authentication fields.
    |
    */
    'validation' => [
        'email' => 'required|email|max:255',
        'password' => 'required|string|min:8|confirmed',
        'name' => 'required|string|max:255',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Messages
    |--------------------------------------------------------------------------
    |
    | Customize response messages for different authentication scenarios.
    |
    */
    'messages' => [
        'login_success' => 'Successfully logged in',
        'login_failed' => 'Invalid credentials',
        'register_success' => 'Successfully registered',
        'logout_success' => 'Successfully logged out',
        'token_refreshed' => 'Token successfully refreshed',
        'password_reset_sent' => 'Password reset link sent to your email',
        'password_reset_success' => 'Password successfully reset',
        'email_verified' => 'Email successfully verified',
        'unauthorized' => 'Unauthorized',
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable additional authentication features.
    |
    */
    'features' => [
        'two_factor_auth' => env('AUTH_MODULE_2FA', false),
        'social_login' => env('AUTH_MODULE_SOCIAL_LOGIN', false),
        'account_lockout' => env('AUTH_MODULE_ACCOUNT_LOCKOUT', true),
        'password_history' => env('AUTH_MODULE_PASSWORD_HISTORY', false),
    ],
];
