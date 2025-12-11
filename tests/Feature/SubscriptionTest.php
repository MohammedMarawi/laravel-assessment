<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles for tests
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    #[Test]
    public function test_user_can_view_their_subscriptions(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Subscription::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/subscriptions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    #[Test]
    public function test_user_can_create_subscription(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/subscriptions', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function test_user_cannot_view_other_users_subscriptions(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        $subscription = Subscription::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/subscriptions/{$subscription->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function test_user_can_cancel_their_subscription(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/subscriptions/{$subscription->id}/cancel");

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'cancelled',
        ]);
    }

    #[Test]
    public function test_user_can_view_statistics(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/subscriptions/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'total_subscriptions',
                    'active_subscriptions',
                ],
            ]);
    }
}
