<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Subscription;
use App\Policies\SubscriptionPolicy;


class AuthServiceProvider extends ServiceProvider
{
    
    protected $policies = [
        // Register Subscription model with its policy
        // This enables using $this->authorize('action', $subscription) in controllers
        Subscription::class => SubscriptionPolicy::class,
    ];

    
    public function boot(): void
    {
        // Register policies defined in $policies array
        $this->registerPolicies();

        
        Gate::before(function ($user, $ability) {
            // Admins bypass all authorization checks
            // Return null to let the normal authorization check proceed for non-admins
            return $user->hasRole('admin') ? true : null;
        });
    }
}
