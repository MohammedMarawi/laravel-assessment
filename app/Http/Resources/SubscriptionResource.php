<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class SubscriptionResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'status' => $this->status,
            'starts_at' => $this->starts_at?->toDateTimeString(),
            'expires_at' => $this->expires_at?->toDateTimeString(),
            'stripe_subscription_id' => $this->stripe_subscription_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Include related product if loaded
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
