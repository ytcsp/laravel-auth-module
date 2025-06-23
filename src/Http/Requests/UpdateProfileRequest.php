<?php

namespace Strichpunkt\LaravelAuthModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
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
        $userId = Auth::id();

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:' . $userTable . ',email,' . $userId,
            'phone' => 'sometimes|nullable|string|max:20',
            'date_of_birth' => 'sometimes|nullable|date',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Name must be a string',
            'name.max' => 'Name may not be greater than 255 characters',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email address is already taken',
            'email.max' => 'Email may not be greater than 255 characters',
            'phone.string' => 'Phone must be a string',
            'phone.max' => 'Phone may not be greater than 20 characters',
            'date_of_birth.date' => 'Please provide a valid date',
        ];
    }
} 