<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    
    protected $fillable = [
        'user_id',
        'product_id',
        'status',
        'starts_at',
        'expires_at',
        'stripe_subscription_id',
    ];

    
    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '<=', now());
    }

    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->expires_at
            && $this->expires_at->isFuture();
    }

    
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    
    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'starts_at' => now(),
        ]);
    }

    
    public function expire(): void
    {
        $this->update(['status' => 'expired']);
    }

    
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
