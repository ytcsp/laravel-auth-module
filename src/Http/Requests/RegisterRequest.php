<?php

namespace Strichpunkt\LaravelAuthModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
        $userModel = config('auth-module.user_model', \App\Models\User::class);
        $userTable = (new $userModel)->getTable();

        return [
            'name' => config('auth-module.validation.name', 'required|string|max:255'),
            'email' => config('auth-module.validation.email', 'required|email|max:255') . '|unique:' . $userTable,
            'password' => config('auth-module.validation.password', 'required|string|min:8|confirmed'),
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name may not be greater than 255 characters',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email address is already registered',
            'email.max' => 'Email may not be greater than 255 characters',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ];
    }
} 