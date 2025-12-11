<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
        Permission::create(['name' => 'manage products']);

        // Assign permission to admin role
        Role::findByName('admin')->givePermissionTo('manage products');
    }

    #[Test]
    public function test_authenticated_user_can_view_products(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(5)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta',
            ]);
    }

    #[Test]
    public function test_authenticated_user_can_create_product(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/products', [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'title', 'price'],
            ]);

        $this->assertDatabaseHas('products', [
            'title' => 'Test Product',
        ]);
    }

    #[Test]
    public function test_products_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['status' => 'active']);
        Product::factory()->create(['status' => 'inactive']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/products?status=active');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        foreach ($data as $product) {
            $this->assertEquals('active', $product['status']);
        }
    }

    #[Test]
    public function test_products_can_be_searched(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['title' => 'Laravel Course']);
        Product::factory()->create(['title' => 'PHP Masterclass']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/products?search=Laravel');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        $this->assertStringContainsString('Laravel', $data[0]['title']);
    }

    #[Test]
    public function test_product_can_be_updated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/products/{$product->id}", [
            'title' => 'Updated Title',
            'price' => 149.99,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Updated Title',
        ]);
    }

    #[Test]
    public function test_product_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }
}
