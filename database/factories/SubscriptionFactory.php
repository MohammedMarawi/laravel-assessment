<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class SubscriptionFactory extends Factory
{
    
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-1 month', 'now');
        $expiresAt = fake()->dateTimeBetween('now', '+1 year');

        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'status' => fake()->randomElement(['pending', 'active', 'expired', 'cancelled']),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'stripe_subscription_id' => fake()->optional(0.3)->uuid(),
        ];
    }

    
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }

    
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'starts_at' => null,
            'expires_at' => null,
        ]);
    }

    
    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'expired',
            'starts_at' => now()->subDays(60),
            'expires_at' => now()->subDays(30),
        ]);
    }
}
