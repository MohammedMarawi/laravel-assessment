<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get specific users
        $activeCustomer = User::where('email', 'ahmed@example.com')->first();
        $expiredCustomer = User::where('email', 'fatima@example.com')->first();
        $cancelledCustomer = User::where('email', 'omar@example.com')->first();
        $pendingCustomer = User::where('email', 'layla@example.com')->first();
        $failedPaymentCustomer = User::where('email', 'khalid@example.com')->first();
        $multiSubCustomer = User::where('email', 'noor@example.com')->first();
        $trialCustomer = User::where('email', 'mona@example.com')->first();
        $vipCustomer = User::where('email', 'youssef@example.com')->first();
        $refundedCustomer = User::where('email', 'hana@example.com')->first();

        // ========================================
        // SUCCESSFUL PAYMENTS
        // ========================================

        // Ahmed - Active subscription payment
        if ($activeCustomer) {
            $subscription = $activeCustomer->subscriptions()->first();
            if ($subscription) {
                Payment::create([
                    'user_id' => $activeCustomer->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => 'txn_' . Str::random(14),
                    'stripe_session_id' => 'cs_' . Str::random(24),
                    'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                    'amount' => $subscription->product->price,
                    'currency' => 'usd',
                    'status' => 'paid',
                    'paid_at' => now()->subDays(15),
                    'metadata' => [
                        'customer_email' => $activeCustomer->email,
                        'product_name' => $subscription->product->title,
                        'payment_method' => 'card',
                        'card_last4' => '4242',
                        'card_brand' => 'visa',
                    ],
                ]);
            }
        }

        // ========================================
        // EXPIRED SUBSCRIPTION PAYMENT (OLD)
        // ========================================

        // Fatima - Old successful payment for expired subscription
        if ($expiredCustomer) {
            $subscription = $expiredCustomer->subscriptions()->first();
            if ($subscription) {
                Payment::create([
                    'user_id' => $expiredCustomer->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => 'txn_' . Str::random(14),
                    'stripe_session_id' => 'cs_' . Str::random(24),
                    'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                    'amount' => $subscription->product->price,
                    'currency' => 'usd',
                    'status' => 'paid',
                    'paid_at' => now()->subDays(45),
                    'metadata' => [
                        'customer_email' => $expiredCustomer->email,
                        'product_name' => $subscription->product->title,
                        'payment_method' => 'card',
                        'card_last4' => '1234',
                        'card_brand' => 'mastercard',
                    ],
                ]);
            }
        }

        // ========================================
        // CANCELLED SUBSCRIPTION PAYMENT
        // ========================================

        // Omar - Payment before cancellation
        if ($cancelledCustomer) {
            $subscription = $cancelledCustomer->subscriptions()->first();
            if ($subscription) {
                Payment::create([
                    'user_id' => $cancelledCustomer->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => 'txn_' . Str::random(14),
                    'stripe_session_id' => 'cs_' . Str::random(24),
                    'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                    'amount' => $subscription->product->price,
                    'currency' => 'usd',
                    'status' => 'paid',
                    'paid_at' => now()->subMonths(3),
                    'metadata' => [
                        'customer_email' => $cancelledCustomer->email,
                        'product_name' => $subscription->product->title,
                        'payment_method' => 'card',
                        'card_last4' => '5678',
                        'card_brand' => 'visa',
                        'note' => 'User cancelled subscription after this payment',
                    ],
                ]);
            }
        }

        // ========================================
        // PENDING PAYMENTS
        // ========================================

        // Layla - Pending payment (checkout not completed)
        if ($pendingCustomer) {
            $subscription = $pendingCustomer->subscriptions()->first();
            if ($subscription) {
                Payment::create([
                    'user_id' => $pendingCustomer->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => 'pending_' . Str::random(14),
                    'stripe_session_id' => 'cs_' . Str::random(24),
                    'stripe_payment_intent_id' => null,
                    'amount' => $subscription->product->price,
                    'currency' => 'usd',
                    'status' => 'unpaid',
                    'paid_at' => null,
                    'metadata' => [
                        'customer_email' => $pendingCustomer->email,
                        'product_name' => $subscription->product->title,
                        'checkout_initiated_at' => now()->subHours(2)->toIso8601String(),
                    ],
                ]);
            }
        }

        // ========================================
        // FAILED PAYMENTS
        // ========================================

        // Khalid - Multiple failed payment attempts
        if ($failedPaymentCustomer) {
            $subscription = $failedPaymentCustomer->subscriptions()->first();
            if ($subscription) {
                // First failed attempt
                Payment::create([
                    'user_id' => $failedPaymentCustomer->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => 'failed_' . Str::random(14),
                    'stripe_session_id' => 'cs_' . Str::random(24),
                    'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                    'amount' => $subscription->product->price,
                    'currency' => 'usd',
                    'status' => 'failed',
                    'paid_at' => null,
                    'metadata' => [
                        'customer_email' => $failedPaymentCustomer->email,
                        'product_name' => $subscription->product->title,
                        'failure_reason' => 'card_declined',
                        'failure_message' => 'Your card was declined. Please try a different card.',
                        'attempted_at' => now()->subDays(3)->toIso8601String(),
                    ],
                ]);

                // Second failed attempt
                Payment::create([
                    'user_id' => $failedPaymentCustomer->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => 'failed_' . Str::random(14),
                    'stripe_session_id' => 'cs_' . Str::random(24),
                    'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                    'amount' => $subscription->product->price,
                    'currency' => 'usd',
                    'status' => 'failed',
                    'paid_at' => null,
                    'metadata' => [
                        'customer_email' => $failedPaymentCustomer->email,
                        'product_name' => $subscription->product->title,
                        'failure_reason' => 'insufficient_funds',
                        'failure_message' => 'Your card has insufficient funds.',
                        'attempted_at' => now()->subDays(1)->toIso8601String(),
                    ],
                ]);

                // Third failed attempt (different error)
                Payment::create([
                    'user_id' => $failedPaymentCustomer->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => 'failed_' . Str::random(14),
                    'stripe_session_id' => 'cs_' . Str::random(24),
                    'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                    'amount' => $subscription->product->price,
                    'currency' => 'usd',
                    'status' => 'failed',
                    'paid_at' => null,
                    'metadata' => [
                        'customer_email' => $failedPaymentCustomer->email,
                        'product_name' => $subscription->product->title,
                        'failure_reason' => 'expired_card',
                        'failure_message' => 'Your card has expired. Please update your payment method.',
                        'attempted_at' => now()->subHours(6)->toIso8601String(),
                    ],
                ]);
            }
        }

        // ========================================
        // MULTI-SUBSCRIPTION PAYMENTS
        // ========================================

        if ($multiSubCustomer) {
            $subscriptions = $multiSubCustomer->subscriptions;

            foreach ($subscriptions as $index => $subscription) {
                if ($subscription->status !== 'pending') {
                    Payment::create([
                        'user_id' => $multiSubCustomer->id,
                        'subscription_id' => $subscription->id,
                        'transaction_id' => 'txn_' . Str::random(14),
                        'stripe_session_id' => 'cs_' . Str::random(24),
                        'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                        'amount' => $subscription->product->price,
                        'currency' => 'usd',
                        'status' => 'paid',
                        'paid_at' => $subscription->starts_at,
                        'metadata' => [
                            'customer_email' => $multiSubCustomer->email,
                            'product_name' => $subscription->product->title,
                            'payment_method' => 'card',
                            'card_last4' => '9999',
                            'card_brand' => 'amex',
                        ],
                    ]);
                }
            }
        }

        // ========================================
        // VIP CUSTOMER - PAYMENT HISTORY
        // ========================================

        if ($vipCustomer) {
            $subscriptions = $vipCustomer->subscriptions()->orderBy('starts_at')->get();

            $cardBrands = ['visa', 'mastercard', 'amex'];
            $currencies = ['usd', 'eur', 'gbp'];

            foreach ($subscriptions as $index => $subscription) {
                if ($subscription->starts_at) {
                    Payment::create([
                        'user_id' => $vipCustomer->id,
                        'subscription_id' => $subscription->id,
                        'transaction_id' => 'txn_' . Str::random(14),
                        'stripe_session_id' => 'cs_' . Str::random(24),
                        'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                        'amount' => $subscription->product->price,
                        'currency' => $currencies[$index % count($currencies)],
                        'status' => 'paid',
                        'paid_at' => $subscription->starts_at,
                        'metadata' => [
                            'customer_email' => $vipCustomer->email,
                            'product_name' => $subscription->product->title,
                            'payment_method' => 'card',
                            'card_last4' => str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                            'card_brand' => $cardBrands[$index % count($cardBrands)],
                            'vip_customer' => true,
                            'payment_number' => $index + 1,
                        ],
                    ]);
                }
            }
        }

        // ========================================
        // REFUNDED PAYMENT
        // ========================================

        if ($refundedCustomer) {
            $subscription = $refundedCustomer->subscriptions()->first();
            if ($subscription) {
                Payment::create([
                    'user_id' => $refundedCustomer->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => 'txn_' . Str::random(14),
                    'stripe_session_id' => 'cs_' . Str::random(24),
                    'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                    'amount' => $subscription->product->price,
                    'currency' => 'usd',
                    'status' => 'refunded',
                    'paid_at' => now()->subDays(10),
                    'metadata' => [
                        'customer_email' => $refundedCustomer->email,
                        'product_name' => $subscription->product->title,
                        'payment_method' => 'card',
                        'card_last4' => '7777',
                        'card_brand' => 'visa',
                        'refund_reason' => 'Customer requested refund within 30-day policy',
                        'refunded_at' => now()->subDays(8)->toIso8601String(),
                        'refund_id' => 're_' . Str::random(14),
                    ],
                ]);
            }
        }

        // ========================================
        // PAYMENTS WITH DIFFERENT CURRENCIES
        // ========================================

        $subscriptions = Subscription::where('status', 'active')
            ->whereDoesntHave('payments')
            ->take(5)
            ->get();

        $currencies = ['usd', 'eur', 'gbp'];

        foreach ($subscriptions as $index => $subscription) {
            Payment::create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'transaction_id' => 'txn_' . Str::random(14),
                'stripe_session_id' => 'cs_' . Str::random(24),
                'stripe_payment_intent_id' => 'pi_' . Str::random(24),
                'amount' => $subscription->product->price,
                'currency' => $currencies[$index % count($currencies)],
                'status' => 'paid',
                'paid_at' => $subscription->starts_at,
                'metadata' => [
                    'customer_email' => $subscription->user->email,
                    'product_name' => $subscription->product->title,
                    'payment_method' => 'card',
                    'auto_generated' => true,
                ],
            ]);
        }
    }
}
