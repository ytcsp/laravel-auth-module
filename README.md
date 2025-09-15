# Laravel Authentication Module

A comprehensive Laravel package that provides a complete authentication system with JWT tokens, including login, registration, password reset, email verification, and middleware protection.

## Features

- 🔐 **JWT Authentication** - Secure token-based authentication
- 👥 **User Registration** - Complete user registration with validation
- 🔑 **Login/Logout** - Secure user authentication
- 🔄 **Token Refresh** - Automatic token renewal
- 📧 **Email Verification** - Optional email verification system
- 🔒 **Password Reset** - Forgot password and reset functionality
- 🛡️ **Middleware Protection** - Ready-to-use authentication middleware
- ⚙️ **Configurable** - Extensive configuration options
- 🚀 **Auto-Registration** - Automatic route and service registration
- 📝 **Validation** - Comprehensive form request validation
- 🎨 **Customizable** - Easy to extend and customize

## Requirements

- PHP 8.1 or higher
- Laravel 10.0, 11.0, or 12.0
- JWT Auth package (included as dependency)

## Installation

1. **Install the package via Composer:**

```bash
composer require strichpunkt/laravel-auth-module
```

### Install Without Packagist (Direct VCS Repository)

If you do NOT want to publish the package on Packagist, require it directly from Git (GitHub, GitLab, etc.). Add a VCS repository entry to your application's `composer.json`:

```json
{
  "repositories": [
    { "type": "vcs", "url": "git@github.com:ytcsp/laravel-auth-module.git" }
  ],
  "require": {
    "ytcsp/laravel-auth-module": "dev-main"
  }
}
```

Then run:

```bash
composer update ytcsp/laravel-auth-module
```

For stability and caching, create git tags (e.g. `v1.0.0`) and require a version:

```bash
composer require ytcsp/laravel-auth-module:^1.0
```

Optional (dev branch alias) — add to this package `composer.json` so consumers can use `^1.1` semantics while tracking main:

```json
"extra": {
  "branch-alias": {
    "dev-main": "1.1.x-dev"
  }
}
```

If you already have a published config file and the package adds new config keys, merge them manually or republish with:

```bash
php artisan vendor:publish --tag=auth-module-config --force
```

2. **Publish the configuration file:**

```bash
php artisan vendor:publish --tag=auth-module-config
```

3. **Publish and run the migrations:**

```bash
php artisan vendor:publish --tag=auth-module-migrations
php artisan migrate
```

4. **Publish JWT configuration (if not already done):**

```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

5. **Generate JWT secret:**

```bash
php artisan jwt:secret
```

6. **Add JWT secret to your `.env` file:**

```env
JWT_SECRET=your_jwt_secret_here
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

## Configuration

The package configuration file is located at `config/auth-module.php`. Here you can customize:

- Route prefixes and middleware
- JWT settings
- Email verification settings
- Password reset configuration
- Rate limiting
- Validation rules
- Response messages
- Feature toggles

### Basic Configuration

```php
// config/auth-module.php
return [
    'routes' => [
        'prefix' => 'auth',  // All routes will be prefixed with /auth
        'middleware' => ['auth-api'],
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
    
    'user_model' => App\Models\User::class,
    
    'email_verification' => [
        'enabled' => true,
    ],
    
    // ... more configuration options
];
```

## API Endpoints

The package automatically registers the following API endpoints:

### Authentication Routes

| Method | Endpoint | Description | Middleware |
|--------|----------|-------------|------------|
| POST | `/auth/login` | User login | `throttle:5,1` |
| POST | `/auth/register` | User registration | `throttle:3,1` |
| POST | `/auth/logout` | User logout | `auth.module` |
| POST | `/auth/refresh` | Refresh JWT token | `auth.module` |
| GET | `/auth/me` | Get user info | `auth.module` |

### Password Reset Routes

| Method | Endpoint | Description | Middleware |
|--------|----------|-------------|------------|
| POST | `/auth/forgot-password` | Send reset link | `throttle:3,1` |
| POST | `/auth/reset-password` | Reset password | `throttle:3,1` |

### Email Verification Routes

| Method | Endpoint | Description | Middleware |
|--------|----------|-------------|------------|
| POST | `/auth/email/verify/{id}/{hash}` | Verify email | `signed` |
| POST | `/auth/email/resend` | Resend verification | `auth.module` |

### Profile Routes

| Method | Endpoint | Description | Middleware |
|--------|----------|-------------|------------|
| PUT | `/auth/profile` | Update profile | `auth.module` |
| PUT | `/auth/password` | Change password | `auth.module` |

## Usage Examples

### Registration

```bash
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Successfully registered",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### Login

```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Protected Route Access

```bash
curl -X GET http://localhost:8000/auth/me \
  -H "Authorization: Bearer your_jwt_token_here"
```

### Password Reset

```bash
# Request reset
curl -X POST http://localhost:8000/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com"}'

# Reset password
curl -X POST http://localhost:8000/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "token": "reset_token_here",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

## Middleware Usage

The package provides two middleware options:

### 1. Required Authentication (`auth.module`)

```php
// In your routes/api.php
Route::middleware(['auth.module'])->group(function () {
    Route::get('/protected', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });
});
```

### 2. Optional Authentication (`jwt.auth`)

```php
// In your routes/api.php
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/optional-auth', function (Request $request) {
        $user = $request->user(); // Will be null if not authenticated
        return response()->json(['user' => $user]);
    });
});
```

## Service Usage

You can use the authentication service directly in your code:

```php
use Strichpunkt\LaravelAuthModule\Services\AuthService;
use Strichpunkt\LaravelAuthModule\Facades\AuthModule;

// Via Dependency Injection
public function __construct(AuthService $authService)
{
    $this->authService = $authService;
}

// Create a user
$user = $this->authService->createUser([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123'
]);

// Via Facade
$user = AuthModule::createUser($userData);
$token = AuthModule::authenticateUser($credentials);
```

## User Model Requirements

Your User model should implement the necessary interfaces and traits:

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

## Customization

### Custom Validation Rules

Update the validation rules in the config file:

```php
'validation' => [
    'email' => 'required|email|max:255',
    'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
    'name' => 'required|string|max:255|min:2',
],
```

### Custom Messages

Customize response messages:

```php
'messages' => [
    'login_success' => 'Welcome back!',
    'login_failed' => 'These credentials do not match our records.',
    'register_success' => 'Account created successfully!',
    // ... more messages
],
```

### Route Customization

Enable/disable specific routes:

```php
'routes' => [
    'enable_routes' => [
        'login' => true,
        'register' => false, // Disable registration
        'forgot_password' => true,
        // ... other routes
    ],
],
```

## Rate Limiting

The package includes built-in rate limiting:

```php
'rate_limiting' => [
    'login' => '5,1',           // 5 attempts per minute
    'register' => '3,1',        // 3 attempts per minute
    'forgot_password' => '3,1', // 3 attempts per minute
    'reset_password' => '3,1',  // 3 attempts per minute
],
```

## Email Integration

To integrate with your email system, you can:

1. **Override the password reset email method** in `PasswordResetService`
2. **Create custom email templates** 
3. **Configure your mail driver** in Laravel's mail configuration

## Testing

The package includes comprehensive test coverage. To run tests:

```bash
composer test
```

## Security Features

- ✅ JWT token expiration and refresh
- ✅ Password hashing with bcrypt
- ✅ Rate limiting on sensitive endpoints
- ✅ CSRF protection via signed URLs for email verification
- ✅ Secure password reset tokens with expiration
- ✅ Input validation and sanitization
- ✅ Optional email verification

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you discover any security vulnerabilities, please send an e-mail to security@strichpunkt.com.

For general support and questions, please open an issue on GitHub. 