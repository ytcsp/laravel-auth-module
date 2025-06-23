<?php

namespace Strichpunkt\LaravelAuthModule\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object createUser(array $userData)
 * @method static string|null authenticateUser(array $credentials)
 * @method static object|null getUserByEmail(string $email)
 * @method static bool verifyPassword(object $user, string $password)
 * @method static bool updatePassword(object $user, string $newPassword)
 * @method static string refreshToken()
 * @method static bool invalidateToken()
 * @method static object|null getAuthenticatedUser()
 * @method static string generateTokenForUser(object $user)
 *
 * @see \Strichpunkt\LaravelAuthModule\Services\AuthService
 */
class AuthModule extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Strichpunkt\LaravelAuthModule\Services\AuthService::class;
    }
} 