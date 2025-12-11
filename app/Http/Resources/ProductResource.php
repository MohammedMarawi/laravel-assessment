<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ProductResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'status' => $this->status,
            'duration_days' => $this->duration_days,

            // Get the first media URL from 'products' collection
            // Returns null if no media exists
            'image_url' => $this->getFirstMediaUrl('products') ?: null,

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
