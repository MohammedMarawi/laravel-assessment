<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class RegisterRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];
    }

    
    public function messages(): array
    {
        return [
            'name.required'     => 'Please provide your name.',
            'name.max'          => 'Name cannot exceed 255 characters.',
            'email.required'    => 'Email address is required.',
            'email.email'       => 'Please provide a valid email address.',
            'email.unique'      => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 8 characters.',
        ];
    }
}
