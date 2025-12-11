<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    
    protected $fillable = [
        'title',
        'description',
        'price',
        'status',
        'duration_days',
    ];

    
    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('products')
            ->singleFile() // Only one file per product (replaces old file when new one uploaded)
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']);
    }
}
