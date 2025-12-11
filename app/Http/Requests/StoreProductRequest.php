<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class StoreProductRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'status'      => 'required|in:active,inactive',
            'file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    
    public function messages(): array
    {
        return [
            'title.required'  => 'Product title is required.',
            'title.max'       => 'Product title cannot exceed 255 characters.',
            'price.required'  => 'Product price is required.',
            'price.numeric'   => 'Price must be a valid number.',
            'price.min'       => 'Price cannot be negative.',
            'status.required' => 'Product status is required.',
            'status.in'       => 'Status must be either active or inactive.',
            'file.file'       => 'The uploaded file is invalid.',
            'file.mimes'      => 'File must be a JPG, JPEG, PNG, or PDF.',
            'file.max'        => 'File size cannot exceed 2MB.',
        ];
    }
}
