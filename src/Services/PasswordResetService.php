<?php

namespace Strichpunkt\LaravelAuthModule\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetService
{
    /**
     * Create a password reset token
     */
    public function createToken(string $email): string
    {
        $this->deleteExistingTokens($email);

        $token = Str::random(config('auth-module.password_reset.token_length', 64));

        DB::table(config('auth-module.password_reset.table', 'password_reset_tokens'))
            ->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

        return $token;
    }

    /**
     * Verify password reset token
     */
    public function verifyToken(string $email, string $token): bool
    {
        $record = DB::table(config('auth-module.password_reset.table', 'password_reset_tokens'))
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$record) {
            return false;
        }

        // Check if token has expired
        $expiryMinutes = config('auth-module.password_reset.token_expiry', 60);
        $expiryTime = Carbon::parse($record->created_at)->addMinutes($expiryMinutes);

        return Carbon::now()->isBefore($expiryTime);
    }

    /**
     * Delete password reset token
     */
    public function deleteToken(string $email): bool
    {
        return DB::table(config('auth-module.password_reset.table', 'password_reset_tokens'))
            ->where('email', $email)
            ->delete() > 0;
    }

    /**
     * Delete existing tokens for email
     */
    protected function deleteExistingTokens(string $email): void
    {
        DB::table(config('auth-module.password_reset.table', 'password_reset_tokens'))
            ->where('email', $email)
            ->delete();
    }

    /**
     * Send password reset email
     */
    public function sendResetEmail(object $user, string $token): void
    {
        // Here you would implement your email sending logic
        // This is a placeholder that you can customize based on your email requirements
        
        $resetUrl = url('/password/reset?token=' . $token . '&email=' . urlencode($user->email));
        
        // Example using Laravel's Mail facade
        // Mail::to($user->email)->send(new PasswordResetMail($user, $token, $resetUrl));
        
        // For now, we'll just log the reset information
        \Log::info('Password reset requested', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => $token,
            'reset_url' => $resetUrl
        ]);
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        $expiryMinutes = config('auth-module.password_reset.token_expiry', 60);
        $expiryTime = Carbon::now()->subMinutes($expiryMinutes);

        return DB::table(config('auth-module.password_reset.table', 'password_reset_tokens'))
            ->where('created_at', '<', $expiryTime)
            ->delete();
    }
} 