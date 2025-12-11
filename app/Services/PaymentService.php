<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PaymentService
{
    
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    
    public function handleWebhook(string $payload, string $signature): array
    {
        // Verify webhook signature to ensure authenticity
        $event = $this->verifyWebhookSignature($payload, $signature);

        // Log the event for audit trail
        Log::channel('stripe')->info('Processing webhook event', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        // Route to appropriate handler based on event type
        switch ($event->type) {
            case 'checkout.session.completed':
                // Handle successful checkout payment
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                // Handle recurring payment success
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;

            case 'customer.subscription.updated':
                // Handle subscription updates (plan changes, etc.)
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                // Handle subscription cancellation/deletion
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                // Handle failed payments
                $this->handlePaymentFailed($event->data->object);
                break;

            default:
                // Log unhandled events for monitoring
                Log::channel('stripe')->info('Unhandled webhook event type', [
                    'type' => $event->type,
                    'id' => $event->id,
                ]);
        }

        return [
            'event_type' => $event->type,
            'event_id' => $event->id,
            'processed' => true,
        ];
    }

    
    public function createCheckoutSession(Subscription $subscription, array $params): array
    {
        try {
            $product = $subscription->product;
            $user = $subscription->user;

            // Create pending payment record
            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'transaction_id' => $this->generateTransactionId(),
                'amount' => $product->price,
                'currency' => $params['currency'] ?? 'usd',
                'status' => 'unpaid',
                'metadata' => [
                    'product_name' => $product->title,
                    'user_email' => $user->email,
                ],
            ]);

            // Create Stripe Checkout Session
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $params['currency'] ?? 'usd',
                        'product_data' => [
                            'name' => $product->title,
                            'description' => $product->description ?? '',
                        ],
                        'unit_amount' => (int)($product->price * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $params['success_url'] ?? route('payment.success', ['session_id' => '{CHECKOUT_SESSION_ID}']),
                'cancel_url' => $params['cancel_url'] ?? route('payment.cancel'),
                'client_reference_id' => $subscription->id,
                'customer_email' => $user->email,
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                ],
            ]);

            // Update payment with Stripe session ID
            $payment->update([
                'stripe_session_id' => $session->id,
            ]);

            Log::channel('stripe')->info('Checkout session created', [
                'session_id' => $session->id,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
            ]);

            return [
                'session_id' => $session->id,
                'session_url' => $session->url,
                'payment_id' => $payment->id,
            ];
        } catch (\Exception $e) {
            Log::channel('stripe')->error('Failed to create checkout session', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);

            throw $e;
        }
    }

    
    protected function handleCheckoutSessionCompleted(object $session): void
    {
        $this->handleSuccessfulPayment($session->id);
    }

    
    public function handleSuccessfulPayment(string $sessionId): void
    {
        try {
            $session = StripeSession::retrieve($sessionId);

            $payment = Payment::where('stripe_session_id', $sessionId)->first();

            if (!$payment) {
                Log::channel('stripe')->warning('Payment not found for session', [
                    'session_id' => $sessionId,
                ]);
                return;
            }

            if ($payment->isPaid()) {
                Log::channel('stripe')->info('Payment already processed', [
                    'payment_id' => $payment->id,
                ]);
                return;
            }

            // Use transaction for data integrity
            DB::transaction(function () use ($payment, $session) {
                // Update payment status
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'stripe_payment_intent_id' => $session->payment_intent,
                ]);

                // Activate subscription
                $subscription = $payment->subscription;
                $product = $subscription->product;

                $subscription->update([
                    'status' => 'active',
                    'starts_at' => now(),
                    'expires_at' => now()->addDays($product->duration_days ?? 30),
                    'stripe_subscription_id' => $session->subscription ?? null,
                ]);
            });

            Log::channel('stripe')->info('Payment processed successfully', [
                'payment_id' => $payment->id,
                'subscription_id' => $payment->subscription_id,
            ]);
        } catch (\Exception $e) {
            Log::channel('stripe')->error('Failed to handle successful payment', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);

            throw $e;
        }
    }

    
    public function handleInvoicePaymentSucceeded(object $invoice): void
    {
        try {
            $stripeSubscriptionId = $invoice->subscription;

            if (!$stripeSubscriptionId) {
                return;
            }

            $subscription = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();

            if (!$subscription) {
                Log::channel('stripe')->warning('Subscription not found for invoice', [
                    'stripe_subscription_id' => $stripeSubscriptionId,
                ]);
                return;
            }

            DB::transaction(function () use ($subscription, $invoice) {
                // Create payment record for recurring payment
                $payment = Payment::create([
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'transaction_id' => $this->generateTransactionId(),
                    'stripe_payment_intent_id' => $invoice->payment_intent,
                    'amount' => $invoice->amount_paid / 100, // Convert from cents
                    'currency' => $invoice->currency,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                        'type' => 'recurring',
                    ],
                ]);

                // Extend subscription
                $product = $subscription->product;
                $subscription->update([
                    'expires_at' => $subscription->expires_at->addDays($product->duration_days ?? 30),
                ]);

                Log::channel('stripe')->info('Invoice payment processed', [
                    'payment_id' => $payment->id,
                    'subscription_id' => $subscription->id,
                ]);
            });
        } catch (\Exception $e) {
            Log::channel('stripe')->error('Failed to handle invoice payment', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id ?? null,
            ]);
        }
    }

    
    protected function handleSubscriptionUpdated(object $stripeSubscription): void
    {
        try {
            $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

            if (!$subscription) {
                Log::channel('stripe')->warning('Subscription not found for update', [
                    'stripe_subscription_id' => $stripeSubscription->id,
                ]);
                return;
            }

            // Map Stripe status to local status
            $statusMap = [
                'active' => 'active',
                'past_due' => 'active', // Still active but payment overdue
                'canceled' => 'cancelled',
                'unpaid' => 'suspended',
                'incomplete' => 'pending',
                'incomplete_expired' => 'expired',
                'trialing' => 'active',
                'paused' => 'suspended',
            ];

            $newStatus = $statusMap[$stripeSubscription->status] ?? 'pending';

            $subscription->update([
                'status' => $newStatus,
            ]);

            Log::channel('stripe')->info('Subscription updated', [
                'subscription_id' => $subscription->id,
                'stripe_status' => $stripeSubscription->status,
                'new_status' => $newStatus,
            ]);
        } catch (\Exception $e) {
            Log::channel('stripe')->error('Failed to handle subscription update', [
                'error' => $e->getMessage(),
                'stripe_subscription_id' => $stripeSubscription->id ?? null,
            ]);
        }
    }

    
    protected function handleSubscriptionDeleted(object $stripeSubscription): void
    {
        try {
            $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

            if (!$subscription) {
                Log::channel('stripe')->warning('Subscription not found for deletion', [
                    'stripe_subscription_id' => $stripeSubscription->id,
                ]);
                return;
            }

            $subscription->update([
                'status' => 'cancelled',
            ]);

            Log::channel('stripe')->info('Subscription cancelled via webhook', [
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            Log::channel('stripe')->error('Failed to handle subscription deletion', [
                'error' => $e->getMessage(),
                'stripe_subscription_id' => $stripeSubscription->id ?? null,
            ]);
        }
    }

    
    protected function handlePaymentFailed(object $paymentIntent): void
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

            if ($payment) {
                $payment->markAsFailed();

                Log::channel('stripe')->warning('Payment failed', [
                    'payment_id' => $payment->id,
                    'payment_intent_id' => $paymentIntent->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('stripe')->error('Failed to handle payment failure', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntent->id ?? null,
            ]);
        }
    }

    
    private function generateTransactionId(): string
    {
        return 'TXN-' . strtoupper(Str::random(16));
    }

    
    public function verifyWebhookSignature(string $payload, string $signature): \Stripe\Event
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret')
        );
    }
}
