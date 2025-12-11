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

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    #[Test]
    public function test_user_can_export_subscriptions(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Subscription::factory()->count(3)->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->get('/api/subscriptions/export');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    #[Test]
    public function test_export_with_status_filter(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Subscription::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
        ]);

        Subscription::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'cancelled',
        ]);

        Sanctum::actingAs($user);

        $response = $this->get('/api/subscriptions/export?status=active');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_admin_can_export_all_subscriptions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        Subscription::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        Subscription::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->get('/api/subscriptions/export');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_unauthenticated_user_cannot_export(): void
    {
        $response = $this->getJson('/api/subscriptions/export');

        $response->assertStatus(401);
    }
}
