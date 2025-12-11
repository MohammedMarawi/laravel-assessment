<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Mockery;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    #[Test]
    public function test_user_can_view_their_payments(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Payment::factory()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'status' => 'paid',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta',
            ]);
    }

    #[Test]
    public function test_user_can_create_checkout_session(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 99.99]);
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);

        // Mock PaymentService to avoid real Stripe calls
        $this->mock(PaymentService::class, function ($mock) use ($subscription) {
            $mock->shouldReceive('createCheckoutSession')
                ->once()
                ->andReturn([
                    'session_id' => 'cs_test_123',
                    'session_url' => 'https://checkout.stripe.com/test',
                    'payment_id' => 1,
                ]);
        });

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/payments/checkout', [
            'subscription_id' => $subscription->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    #[Test]
    public function test_user_cannot_checkout_other_users_subscription(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user1);

        $response = $this->postJson('/api/payments/checkout', [
            'subscription_id' => $subscription->id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function test_webhook_requires_stripe_signature(): void
    {
        $response = $this->postJson('/api/webhook/stripe', [
            'type' => 'checkout.session.completed',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Missing Stripe-Signature header',
            ]);
    }

    #[Test]
    public function test_payment_success_endpoint(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'stripe_session_id' => 'cs_test_123',
            'status' => 'paid',
        ]);

        $response = $this->getJson('/api/payment/success?session_id=cs_test_123');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }
}
