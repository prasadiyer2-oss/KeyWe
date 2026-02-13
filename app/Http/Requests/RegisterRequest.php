<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow anyone to try registering
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'     => 'nullable|string',
            'email'    => 'nullable|email|unique:users,email',
            'phone'    => 'required|digits:10|unique:users,phone',
            'password' => 'nullable|string|confirmed|min:6',
        ];
    }
}