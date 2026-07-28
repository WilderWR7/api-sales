<?php
namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function userCanCreateSaleSuccessfully(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $laptopInitialStock = 10;
        $mouseInitialStock = 5;

        $laptop = Product::create([
            'name' => 'Laptop Lenovo',
            'price' => 100.0,
            'stock' => $laptopInitialStock,
        ]);

        $mouse = Product::create([
            'name' => 'Mouse Wireless',
            'price' => 50.0,
            'stock' => $mouseInitialStock,
        ]);

        $laptopQuantity = 2;
        $mouseQuantity = 1;

        $laptopExpectedSubtotal = $laptop->price * $laptopQuantity;
        $mouseExpectedSubtotal = $mouse->price * $mouseQuantity;

        $laptopExpectedStock = $laptopInitialStock - $laptopQuantity;
        $mouseExpectedStock = $mouseInitialStock - $mouseQuantity;

        $expectedTotal = $laptopExpectedSubtotal + $mouseExpectedSubtotal;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/sales', [
                    'user_id' => $user->id,
                    'items' => [
                        [
                            'product_id' => $laptop->id,
                            'quantity' => $laptopQuantity,
                        ],
                        [
                            'product_id' => $mouse->id,
                            'quantity' => $mouseQuantity,
                        ]
                    ]
                ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'sale_id',
                'total'
            ])
            ->assertJson([
                'message' => 'Sale created successfully',
                'total' => $expectedTotal,
            ]);

        // Verify stock was decremented correctly
        $laptop->refresh();
        $mouse->refresh();
        $this->assertEquals($laptopExpectedStock, $laptop->stock);
        $this->assertEquals($mouseExpectedStock, $mouse->stock);

        // Verify database contains the sale record
        $this->assertDatabaseHas('sales', [
            'user_id' => $user->id,
            'total' => $expectedTotal,
        ]);

        // Verify database contains the sale details
        $saleId = $response->json('sale_id');
        $this->assertDatabaseHas('sale_details', [
            'sale_id' => $saleId,
            'product_id' => $laptop->id,
            'quantity' => $laptopQuantity,
            'price' => $laptop->price,
            'subtotal' => $laptopExpectedSubtotal,
        ]);
        $this->assertDatabaseHas('sale_details', [
            'sale_id' => $saleId,
            'product_id' => $mouse->id,
            'quantity' => $mouseQuantity,
            'price' => $mouse->price,
            'subtotal' => $mouseExpectedSubtotal,
        ]);
    }

    #[Test]
    public function userCannotCreateSaleWithInsufficientStock(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $availableStock = 1;
        $requestedQuantity = 2;
        $productName = 'Product Limited';
        $product = Product::create([
            'name' => $productName,
            'price' => 10.0,
            'stock' => $availableStock,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/sales', [
                    'user_id' => $user->id,
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'quantity' => $requestedQuantity,
                        ]
                    ]
                ]);

        $response->assertUnprocessable()
            ->assertJson([
                'message' => "Insufficient stock for product: {$productName}",
            ]);

        // Verify no sale was created in database (rollback check)
        $product->refresh();
        $this->assertEquals($availableStock, $product->stock);

        // Verify no sale was created in database (rollback check)
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_details', 0);
    }

    #[Test]
    public function unauthenticatedUserCannotCreateSale(): void
    {
        $response = $this->postJson('/api/sales', [
            'items' => [
                ['product_id' => 1, 'quantity' => 1]
            ]
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function userCannotCreateSaleWithEmptyItems(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/sales', [
                    'items' => []
                ]);

        $response->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors([
                'items' => 'At least one product item is required.'
            ]);
    }

    #[Test]
    public function userCannotCreateSaleWithNonExistentProduct(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $nonExistentProductId = 99999;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/sales', [
                    'items' => [
                        [
                            'product_id' => $nonExistentProductId,
                            'quantity' => 1,
                        ]
                    ]
                ]);

        $response->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors([
                'items.0.product_id' => 'The selected product does not exist.'
            ]);
    }

    #[Test]
    public function userCannotCreateSaleWithInvalidQuantity(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $product = Product::create([
            'name' => 'Valid Product',
            'price' => 20.0,
            'stock' => 10,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/sales', [
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'quantity' => 0, // Invalid: must be >= 1
                        ]
                    ]
                ]);

        $response->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors([
                'items.0.quantity' => 'The quantity for each product must be at least 1.'
            ]);
    }
}
