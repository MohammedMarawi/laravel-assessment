<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class StoreSubscriptionRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
        ];
    }

    
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product ID is required.',
            'product_id.integer'  => 'Product ID must be a valid integer.',
            'product_id.exists'   => 'The selected product does not exist.',
        ];
    }
}
