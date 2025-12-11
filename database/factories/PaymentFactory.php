<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


class PaymentFactory extends Factory
{
    
    public function definition(): array
    {
        $status = fake()->randomElement(['unpaid', 'paid', 'failed', 'refunded']);

        return [
            'subscription_id' => Subscription::factory(),
            'user_id' => User::factory(),
            'transaction_id' => 'TXN-' . strtoupper(Str::random(16)),
            'stripe_session_id' => fake()->optional(0.7)->uuid(),
            'stripe_payment_intent_id' => fake()->optional(0.7)->uuid(),
            'amount' => fake()->randomFloat(2, 9.99, 299.99),
            'currency' => fake()->randomElement(['usd', 'eur', 'gbp']),
            'status' => $status,
            'metadata' => [
                'product_name' => fake()->words(3, true),
                'user_email' => fake()->email(),
            ],
            'paid_at' => $status === 'paid' ? now() : null,
        ];
    }

    
    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    
    public function unpaid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'unpaid',
            'paid_at' => null,
        ]);
    }

    
    public function failed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'failed',
            'paid_at' => null,
        ]);
    }
}
