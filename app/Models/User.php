<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    
    public function hasActiveSubscription(int $productId): bool
    {
        return $this->subscriptions()
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }
}
