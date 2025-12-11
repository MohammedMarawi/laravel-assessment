<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_is_active(): void
    {
        $subscription = Subscription::factory()->create([
            'status' => 'active',
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertTrue($subscription->isActive());
    }

    public function test_subscription_is_not_active_when_expired(): void
    {
        $subscription = Subscription::factory()->create([
            'status' => 'active',
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($subscription->isActive());
    }

    public function test_subscription_is_not_active_when_cancelled(): void
    {
        $subscription = Subscription::factory()->create([
            'status' => 'cancelled',
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertFalse($subscription->isActive());
    }

    public function test_subscription_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $subscription->user);
        $this->assertEquals($user->id, $subscription->user->id);
    }

    public function test_subscription_belongs_to_product(): void
    {
        $product = Product::factory()->create();

        $subscription = Subscription::factory()->create([
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Product::class, $subscription->product);
        $this->assertEquals($product->id, $subscription->product->id);
    }
}
