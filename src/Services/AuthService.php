<?php

namespace Strichpunkt\LaravelAuthModule\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Strichpunkt\LaravelAuthModule\Models\LoginLog;

class AuthService
{
    /**
     * Create a new user
     */
    public function createUser(array $userData): object
    {
        $userModel = config('auth-module.user_model', \App\Models\User::class);
        
        $userData['password'] = Hash::make($userData['password']);
        
        return $userModel::create($userData);
    }

    /**
     * Authenticate user and return token
     */
    public function authenticateUser(array $credentials): ?string
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            return null;
        }

        return $token;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?object
    {
        $userModel = config('auth-module.user_model', \App\Models\User::class);
        
        return $userModel::where('email', $email)->first();
    }

    /**
     * Verify user password
     */
    public function verifyPassword(object $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }

    /**
     * Update user password
     */
    public function updatePassword(object $user, string $newPassword): bool
    {
        return $user->update([
            'password' => Hash::make($newPassword)
        ]);
    }

    /**
     * Refresh JWT token
     */
    public function refreshToken(): string
    {
        return JWTAuth::refresh();
    }

    /**
     * Invalidate JWT token
     */
    public function invalidateToken(): bool
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(): ?object
    {
        return Auth::user();
    }

    /**
     * Generate JWT token for user
     */
    public function generateTokenForUser(object $user): string
    {
        return JWTAuth::fromUser($user);
    }

    /**
     * Log a login attempt (success or failure)
     */
    public function logLoginAttempt(string $email, bool $success, ?int $userId = null): void
    {
        if (!config('auth-module.login_logging.enabled', true)) {
            return; // feature disabled
        }

        $data = [
            'email' => $email,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string)request()->userAgent(), 0, 1000),
            'success' => $success,
        ];

        // Persist to database (ignore errors to not block login)
        try {
            $table = config('auth-module.login_logging.table', 'login_logs');
            $model = new LoginLog();
            $model->setTable($table);
            $model->fill($data);
            $model->save();
        } catch (\Throwable $e) {
            Log::debug('AuthModule login log db failure: '.$e->getMessage());
        }

        // Also write to application log channel
        try {
            $channel = config('auth-module.login_logging.log_channel', config('logging.default'));
            Log::channel($channel)->info('Login attempt', $data);
        } catch (\Throwable $e) {
            Log::debug('AuthModule login log channel failure: '.$e->getMessage());
        }
    }
} 