<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Auth\Access\HandlesAuthorization;


class SubscriptionPolicy
{
    use HandlesAuthorization;

    
    public function viewAny(User $user): bool
    {
        // Any authenticated user can view subscriptions list
        // (they will only see their own subscriptions via controller logic)
        return true;
    }

    
    public function view(User $user, Subscription $subscription): bool
    {
        // User can view their own subscription OR admin can view any
        return $user->id === $subscription->user_id || $user->hasRole('admin');
    }

    
    public function create(User $user): bool
    {
        // Any authenticated user can create a subscription
        return true;
    }

    
    public function update(User $user, Subscription $subscription): bool
    {
        // Only the subscription owner can update (cancel) it
        return $user->id === $subscription->user_id;
    }

    
    public function delete(User $user, Subscription $subscription): bool
    {
        // Only admins can delete subscriptions
        return $user->hasRole('admin');
    }

    
    public function restore(User $user, Subscription $subscription): bool
    {
        // Only admins can restore soft-deleted subscriptions
        return $user->hasRole('admin');
    }

    
    public function forceDelete(User $user, Subscription $subscription): bool
    {
        // Only admins can permanently delete subscriptions
        return $user->hasRole('admin');
    }
}
