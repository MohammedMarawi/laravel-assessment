<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'subscription_id',
        'user_id',
        'transaction_id',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
        'metadata',
        'paid_at',
    ];

    
    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    
    public function markAsRefunded(): void
    {
        $this->update(['status' => 'refunded']);
    }

    
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    
    public function isPending(): bool
    {
        return $this->status === 'unpaid';
    }
}
