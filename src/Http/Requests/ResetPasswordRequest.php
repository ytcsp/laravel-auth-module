<?php

namespace Strichpunkt\LaravelAuthModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => 'required|email|max:255',
            'password' => config('auth-module.validation.password', 'required|string|min:8|confirmed'),
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Reset token is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ];
    }
} 