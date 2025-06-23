<?php

namespace Strichpunkt\LaravelAuthModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Strichpunkt\LaravelAuthModule\Http\Requests\ForgotPasswordRequest;
use Strichpunkt\LaravelAuthModule\Http\Requests\ResetPasswordRequest;
use Strichpunkt\LaravelAuthModule\Services\PasswordResetService;

class PasswordResetController extends BaseController
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Send password reset link to email
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $email = $request->validated()['email'];
            
            // Check if user exists
            $userModel = config('auth-module.user_model', \App\Models\User::class);
            $user = $userModel::where('email', $email)->first();
            
            if (!$user) {
                // Don't reveal if user exists or not for security
                return $this->successResponse(
                    null,
                    config('auth-module.messages.password_reset_sent', 'Password reset link sent to your email')
                );
            }

            // Generate reset token
            $token = $this->passwordResetService->createToken($email);
            
            // Send email (you would implement your email logic here)
            $this->passwordResetService->sendResetEmail($user, $token);

            return $this->successResponse(
                null,
                config('auth-module.messages.password_reset_sent', 'Password reset link sent to your email')
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send reset link: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reset password using token
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Verify token
            if (!$this->passwordResetService->verifyToken($data['email'], $data['token'])) {
                return $this->errorResponse('Invalid or expired reset token', 400);
            }

            // Get user and update password
            $userModel = config('auth-module.user_model', \App\Models\User::class);
            $user = $userModel::where('email', $data['email'])->first();
            
            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            $user->update([
                'password' => Hash::make($data['password'])
            ]);

            // Delete the reset token
            $this->passwordResetService->deleteToken($data['email']);

            return $this->successResponse(
                null,
                config('auth-module.messages.password_reset_success', 'Password successfully reset')
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Password reset failed: ' . $e->getMessage(), 500);
        }
    }
} 