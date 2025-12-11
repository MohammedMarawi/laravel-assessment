<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class ProductFactory extends Factory
{
    
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 9.99, 299.99),
            'status' => fake()->randomElement(['active', 'inactive']),
            'duration_days' => fake()->randomElement([30, 60, 90, 365]),
        ];
    }

    
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    
    public function monthly(): static
    {
        return $this->state(fn(array $attributes) => [
            'duration_days' => 30,
        ]);
    }

    
    public function yearly(): static
    {
        return $this->state(fn(array $attributes) => [
            'duration_days' => 365,
        ]);
    }
}
