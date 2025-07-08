<?php

namespace Strichpunkt\LaravelAuthModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Strichpunkt\LaravelAuthModule\Services\AuthService;
use Strichpunkt\LaravelAuthModule\Http\Requests\LoginRequest;
use Strichpunkt\LaravelAuthModule\Http\Requests\RegisterRequest;
use Strichpunkt\LaravelAuthModule\Http\Requests\UpdateProfileRequest;
use Strichpunkt\LaravelAuthModule\Http\Requests\ChangePasswordRequest;

class AuthController extends BaseController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle user login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        try {
            $userModel = config('auth-module.user_model', \App\Models\User::class);
            config(['auth.providers.users.model' => $userModel]);
            
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->errorResponse(
                    config('auth-module.messages.login_failed', 'Invalid credentials'),
                    401
                );
            }

            $user = Auth::user();
            
            // Check if email verification is required
            if (config('auth-module.email_verification.enabled') && !$user->hasVerifiedEmail()) {
                return $this->errorResponse('Please verify your email address', 403);
            }

            return $this->successResponse([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('auth-module.jwt.ttl', 60) * 60
            ], config('auth-module.messages.login_success', 'Successfully logged in'));

        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle user registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userData = $request->validated();
            $userData['password'] = Hash::make($userData['password']);

            $userModel = config('auth-module.user_model', \App\Models\User::class);
            $user = $userModel::create($userData);

            // Send email verification if enabled
            if (config('auth-module.email_verification.enabled')) {
                $user->sendEmailVerificationNotification();
                
                return $this->successResponse([
                    'user' => $user,
                    'message' => 'Registration successful. Please verify your email address.'
                ], config('auth-module.messages.register_success', 'Successfully registered'));
            }

            // Auto login after registration
            $token = JWTAuth::fromUser($user);

            return $this->successResponse([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('auth-module.jwt.ttl', 60) * 60
            ], config('auth-module.messages.register_success', 'Successfully registered'));

        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            
            return $this->successResponse(
                null,
                config('auth-module.messages.logout_success', 'Successfully logged out')
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Refresh the access token
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh();
            
            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('auth-module.jwt.ttl', 60) * 60
            ], config('auth-module.messages.token_refreshed', 'Token successfully refreshed'));

        } catch (\Exception $e) {
            return $this->errorResponse('Token refresh failed: ' . $e->getMessage(), 401);
        }
    }

    /**
     * Get authenticated user information
     */
    public function me(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            return $this->successResponse(['user' => $user]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get user information: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $user->update($request->validated());

            return $this->successResponse([
                'user' => $user->fresh()
            ], 'Profile updated successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Profile update failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->errorResponse('Current password is incorrect', 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Invalidate all existing tokens
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->successResponse(null, 'Password changed successfully. Please login again.');

        } catch (\Exception $e) {
            return $this->errorResponse('Password change failed: ' . $e->getMessage(), 500);
        }
    }
} 