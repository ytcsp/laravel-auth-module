<?php

namespace Strichpunkt\LaravelAuthModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationController extends BaseController
{
    /**
     * Verify email address
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            $userModel = config('auth-module.user_model', \App\Models\User::class);
            $user = $userModel::findOrFail($request->route('id'));

            if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
                return $this->errorResponse('Invalid verification link', 400);
            }

            if ($user->hasVerifiedEmail()) {
                return $this->successResponse(
                    null,
                    config('auth-module.messages.email_verified', 'Email already verified')
                );
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return $this->successResponse(
                null,
                config('auth-module.messages.email_verified', 'Email successfully verified')
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Email verification failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Resend email verification notification
     */
    public function resend(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user->hasVerifiedEmail()) {
                return $this->errorResponse('Email is already verified', 400);
            }

            $user->sendEmailVerificationNotification();

            return $this->successResponse(
                null,
                'Verification email sent successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send verification email: ' . $e->getMessage(), 500);
        }
    }
} 