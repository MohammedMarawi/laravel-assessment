<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class CreateCheckoutRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'currency'        => 'sometimes|string|in:usd,eur,gbp',
            'success_url'     => ['sometimes', 'string', 'regex:/^https?:\/\/.+/i'],
            'cancel_url'      => ['sometimes', 'string', 'regex:/^https?:\/\/.+/i'],
        ];
    }

    
    public function messages(): array
    {
        return [
            'subscription_id.required' => 'Subscription ID is required.',
            'subscription_id.integer'  => 'Subscription ID must be a valid integer.',
            'subscription_id.exists'   => 'The selected subscription does not exist.',
            'currency.in'              => 'Currency must be USD, EUR, or GBP.',
            'success_url.url'          => 'Success URL must be a valid URL.',
            'cancel_url.url'           => 'Cancel URL must be a valid URL.',
        ];
    }
}
