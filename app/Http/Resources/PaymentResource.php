<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PaymentResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_id' => $this->subscription_id,
            'user_id' => $this->user_id,
            'transaction_id' => $this->transaction_id,
            'stripe_session_id' => $this->stripe_session_id,
            'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'paid_at' => $this->paid_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Include related subscription if loaded
            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),
        ];
    }
}
