<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    
    public function createSubscription(User $user, int $productId): Subscription
    {
        try {
            $product = Product::findOrFail($productId);

            // Check if user already has an active subscription for this product
            if ($user->hasActiveSubscription($productId)) {
                throw new \Exception('User already has an active subscription for this product.');
            }

            $subscription = DB::transaction(function () use ($user, $product) {
                return Subscription::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'status' => 'pending',
                    'starts_at' => null,
                    'expires_at' => null,
                ]);
            });

            Log::info("Subscription created", [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

            return $subscription;

        } catch (\Exception $e) {
            Log::error("Failed to create subscription", [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);

            throw $e;
        }
    }

    
    public function getUserSubscriptions(User $user, array $filters = [])
    {
        $query = $user->subscriptions()->with(['product', 'payments']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->latest()->get();
    }

    
    public function getSubscription(int $subscriptionId, User $user): Subscription
    {
        $subscription = Subscription::with(['product', 'payments'])
            ->findOrFail($subscriptionId);

        if ($subscription->user_id !== $user->id) {
            throw new \Exception('Unauthorized access to subscription.');
        }

        return $subscription;
    }

    
    public function cancelSubscription(Subscription $subscription): bool
    {
        try {
            $subscription->cancel();

            Log::info("Subscription cancelled", [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to cancel subscription", [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);

            throw $e;
        }
    }

    
    public function expireSubscriptions(): int
    {
        try {
            $expiredCount = Subscription::active()
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired']);

            Log::info("Expired subscriptions", ['count' => $expiredCount]);

            return $expiredCount;

        } catch (\Exception $e) {
            Log::error("Failed to expire subscriptions", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    
    public function activateSubscription(Subscription $subscription, int $durationDays = 30): void
    {
        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addDays($durationDays),
        ]);

        Log::info("Subscription activated", [
            'subscription_id' => $subscription->id,
            'expires_at' => $subscription->expires_at,
        ]);
    }

    
    public function getUserStatistics(User $user): array
    {
        return [
            'total_subscriptions' => $user->subscriptions()->count(),
            'active_subscriptions' => $user->subscriptions()->where('status', 'active')->count(),
            'expired_subscriptions' => $user->subscriptions()->where('status', 'expired')->count(),
            'total_spent' => $user->payments()->paid()->sum('amount'),
            'pending_payments' => $user->payments()->unpaid()->count(),
        ];
    }
}
