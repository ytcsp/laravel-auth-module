<?php

use Illuminate\Support\Facades\Route;
use Strichpunkt\LaravelAuthModule\Http\Controllers\AuthController;
use Strichpunkt\LaravelAuthModule\Http\Controllers\PasswordResetController;
use Strichpunkt\LaravelAuthModule\Http\Controllers\EmailVerificationController;

$config = config('auth-module');
$prefix = $config['routes']['prefix'] ?? 'auth';
$middleware = $config['routes']['middleware'] ?? ['auth-api'];
$enabledRoutes = $config['routes']['enable_routes'] ?? [];


Route::group([
    'prefix' => $prefix . "/v1",
], function () use ($enabledRoutes, $config) {
    
    // Authentication routes (no auth required)
    if ($enabledRoutes['login'] ?? true) {
        Route::post('login', [AuthController::class, 'login'])
            ->name('auth-module.login');
    }

    if ($enabledRoutes['register'] ?? true) {
        Route::post('register', [AuthController::class, 'register'])
            ->name('auth-module.register');
    }

    // Password reset routes
    if ($enabledRoutes['forgot_password'] ?? true) {
        Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
            ->name('auth-module.password.email');
    }

    if ($enabledRoutes['reset_password'] ?? true) {
        Route::post('reset-password', [PasswordResetController::class, 'reset'])
            ->name('auth-module.password.reset');
    }

    // Email verification routes
    if ($enabledRoutes['verify_email'] ?? true) {
        Route::post('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed'])
            ->name('auth-module.verification.verify');

        Route::post('email/resend', [EmailVerificationController::class, 'resend'])
            ->middleware(['auth.module', 'throttle:3,1'])
            ->name('auth-module.verification.send');
    }

    // Protected routes (require authentication)
    Route::group(['middleware' => ['auth.module']], function () use ($enabledRoutes) {
        
        if ($enabledRoutes['logout'] ?? true) {
            Route::post('logout', [AuthController::class, 'logout'])
                ->name('auth-module.logout');
        }

        if ($enabledRoutes['refresh'] ?? true) {
            Route::post('refresh', [AuthController::class, 'refresh'])
                ->name('auth-module.refresh');
        }

        if ($enabledRoutes['me'] ?? true) {
            Route::get('me', [AuthController::class, 'me'])
                ->name('auth-module.me');
        }

        // Additional user profile routes
        Route::put('profile', [AuthController::class, 'updateProfile'])
            ->name('auth-module.profile.update');
            
        Route::put('password', [AuthController::class, 'changePassword'])
            ->name('auth-module.password.change');
    });
}); 