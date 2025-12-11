<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        // Get users by email for precise seeding
        $activeCustomer = User::where('email', 'ahmed@example.com')->first();
        $expiredCustomer = User::where('email', 'fatima@example.com')->first();
        $cancelledCustomer = User::where('email', 'omar@example.com')->first();
        $pendingCustomer = User::where('email', 'layla@example.com')->first();
        $failedPaymentCustomer = User::where('email', 'khalid@example.com')->first();
        $multiSubCustomer = User::where('email', 'noor@example.com')->first();
        $trialCustomer = User::where('email', 'mona@example.com')->first();
        $vipCustomer = User::where('email', 'youssef@example.com')->first();
        $refundedCustomer = User::where('email', 'hana@example.com')->first();

        // Get products
        $basicMonthly = Product::where('title', 'Basic Monthly Plan')->first();
        $basicYearly = Product::where('title', 'Basic Yearly Plan')->first();
        $proMonthly = Product::where('title', 'Professional Monthly Plan')->first();
        $proYearly = Product::where('title', 'Professional Yearly Plan')->first();
        $enterpriseMonthly = Product::where('title', 'Enterprise Monthly Plan')->first();
        $trialPlan = Product::where('title', 'Starter Trial Plan')->first();
        $laravelCourse = Product::where('title', 'Laravel Mastery Course')->first();
        $vueCourse = Product::where('title', 'Vue.js Complete Guide')->first();
        $fullStackBundle = Product::where('title', 'Full Stack Developer Bundle')->first();

        // ========================================
        // ACTIVE SUBSCRIPTIONS
        // ========================================

        // Ahmed - Active monthly subscription
        if ($activeCustomer && $proMonthly) {
            Subscription::create([
                'user_id' => $activeCustomer->id,
                'product_id' => $proMonthly->id,
                'status' => 'active',
                'starts_at' => now()->subDays(15),
                'expires_at' => now()->addDays(15),
                'stripe_subscription_id' => 'sub_' . Str::random(14),
            ]);
        }

        // ========================================
        // EXPIRED SUBSCRIPTIONS
        // ========================================

        // Fatima - Expired subscription (was active, now expired)
        if ($expiredCustomer && $basicMonthly) {
            Subscription::create([
                'user_id' => $expiredCustomer->id,
                'product_id' => $basicMonthly->id,
                'status' => 'expired',
                'starts_at' => now()->subDays(45),
                'expires_at' => now()->subDays(15),
                'stripe_subscription_id' => 'sub_' . Str::random(14),
            ]);
        }

        // ========================================
        // CANCELLED SUBSCRIPTIONS
        // ========================================

        // Omar - Cancelled subscription (user cancelled before expiry)
        if ($cancelledCustomer && $proYearly) {
            Subscription::create([
                'user_id' => $cancelledCustomer->id,
                'product_id' => $proYearly->id,
                'status' => 'cancelled',
                'starts_at' => now()->subMonths(3),
                'expires_at' => now()->addMonths(9), // Still has time but cancelled
                'stripe_subscription_id' => 'sub_' . Str::random(14),
            ]);
        }

        // ========================================
        // PENDING SUBSCRIPTIONS
        // ========================================

        // Layla - Pending subscription (waiting for payment)
        if ($pendingCustomer && $enterpriseMonthly) {
            Subscription::create([
                'user_id' => $pendingCustomer->id,
                'product_id' => $enterpriseMonthly->id,
                'status' => 'pending',
                'starts_at' => null,
                'expires_at' => null,
                'stripe_subscription_id' => null,
            ]);
        }

        // ========================================
        // FAILED PAYMENT SUBSCRIPTIONS
        // ========================================

        // Khalid - Subscription with failed payment attempt
        if ($failedPaymentCustomer && $proMonthly) {
            Subscription::create([
                'user_id' => $failedPaymentCustomer->id,
                'product_id' => $proMonthly->id,
                'status' => 'pending',
                'starts_at' => null,
                'expires_at' => null,
                'stripe_subscription_id' => null,
            ]);
        }

        // ========================================
        // MULTI-SUBSCRIPTION USER
        // ========================================

        if ($multiSubCustomer) {
            // Active course subscription
            if ($laravelCourse) {
                Subscription::create([
                    'user_id' => $multiSubCustomer->id,
                    'product_id' => $laravelCourse->id,
                    'status' => 'active',
                    'starts_at' => now()->subMonths(2),
                    'expires_at' => now()->addMonths(10),
                    'stripe_subscription_id' => 'sub_' . Str::random(14),
                ]);
            }

            // Active platform subscription
            if ($proMonthly) {
                Subscription::create([
                    'user_id' => $multiSubCustomer->id,
                    'product_id' => $proMonthly->id,
                    'status' => 'active',
                    'starts_at' => now()->subDays(10),
                    'expires_at' => now()->addDays(20),
                    'stripe_subscription_id' => 'sub_' . Str::random(14),
                ]);
            }

            // Expired old subscription
            if ($basicMonthly) {
                Subscription::create([
                    'user_id' => $multiSubCustomer->id,
                    'product_id' => $basicMonthly->id,
                    'status' => 'expired',
                    'starts_at' => now()->subMonths(6),
                    'expires_at' => now()->subMonths(5),
                    'stripe_subscription_id' => 'sub_' . Str::random(14),
                ]);
            }
        }

        // ========================================
        // TRIAL SUBSCRIPTION
        // ========================================

        // Mona - Trial subscription
        if ($trialCustomer && $trialPlan) {
            Subscription::create([
                'user_id' => $trialCustomer->id,
                'product_id' => $trialPlan->id,
                'status' => 'active',
                'starts_at' => now()->subDays(3),
                'expires_at' => now()->addDays(4),
                'stripe_subscription_id' => null, // Trial - no Stripe
            ]);
        }

        // ========================================
        // VIP CUSTOMER - LONG HISTORY
        // ========================================

        if ($vipCustomer) {
            // Old expired subscription (2 years ago)
            if ($basicMonthly) {
                Subscription::create([
                    'user_id' => $vipCustomer->id,
                    'product_id' => $basicMonthly->id,
                    'status' => 'expired',
                    'starts_at' => now()->subYears(2),
                    'expires_at' => now()->subYears(2)->addMonth(),
                    'stripe_subscription_id' => 'sub_' . Str::random(14),
                ]);
            }

            // Upgraded subscription (1 year ago)
            if ($proMonthly) {
                Subscription::create([
                    'user_id' => $vipCustomer->id,
                    'product_id' => $proMonthly->id,
                    'status' => 'expired',
                    'starts_at' => now()->subYear(),
                    'expires_at' => now()->subMonths(11),
                    'stripe_subscription_id' => 'sub_' . Str::random(14),
                ]);
            }

            // Current yearly subscription
            if ($proYearly) {
                Subscription::create([
                    'user_id' => $vipCustomer->id,
                    'product_id' => $proYearly->id,
                    'status' => 'active',
                    'starts_at' => now()->subMonths(6),
                    'expires_at' => now()->addMonths(6),
                    'stripe_subscription_id' => 'sub_' . Str::random(14),
                ]);
            }

            // Course subscription
            if ($fullStackBundle) {
                Subscription::create([
                    'user_id' => $vipCustomer->id,
                    'product_id' => $fullStackBundle->id,
                    'status' => 'active',
                    'starts_at' => now()->subMonths(3),
                    'expires_at' => now()->addMonths(9),
                    'stripe_subscription_id' => 'sub_' . Str::random(14),
                ]);
            }
        }

        // ========================================
        // REFUNDED CUSTOMER
        // ========================================

        // Hana - Had a subscription that was refunded
        if ($refundedCustomer && $enterpriseMonthly) {
            Subscription::create([
                'user_id' => $refundedCustomer->id,
                'product_id' => $enterpriseMonthly->id,
                'status' => 'cancelled',
                'starts_at' => now()->subDays(10),
                'expires_at' => now()->subDays(10), // Immediately cancelled after refund
                'stripe_subscription_id' => 'sub_' . Str::random(14),
            ]);
        }

        // ========================================
        // ADDITIONAL RANDOM SUBSCRIPTIONS
        // ========================================

        $randomUsers = User::whereDoesntHave('subscriptions')
            ->where('email', 'not like', '%@example.com')
            ->take(5)
            ->get();

        $activeProducts = Product::where('status', 'active')
            ->whereNotNull('duration_days')
            ->get();

        foreach ($randomUsers as $user) {
            if ($activeProducts->isNotEmpty()) {
                $product = $activeProducts->random();
                $status = collect(['active', 'pending', 'expired'])->random();

                $startsAt = match ($status) {
                    'active' => now()->subDays(rand(1, 15)),
                    'expired' => now()->subDays(rand(30, 90)),
                    'pending' => null,
                };

                $expiresAt = match ($status) {
                    'active' => now()->addDays(rand(15, 30)),
                    'expired' => now()->subDays(rand(1, 30)),
                    'pending' => null,
                };

                Subscription::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'status' => $status,
                    'starts_at' => $startsAt,
                    'expires_at' => $expiresAt,
                    'stripe_subscription_id' => $status !== 'pending' ? 'sub_' . Str::random(14) : null,
                ]);
            }
        }
    }
}
