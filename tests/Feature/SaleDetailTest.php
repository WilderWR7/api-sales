<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaleDetailTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticatedUserCannotAccessSalesSummary(): void
    {
        $response = $this->getJson('/api/sales/summary');

        $response->assertStatus(401);
    }

    #[Test]
    #[DataProvider('salesSummaryCasesProvider')]
    public function itReturnsExpectedSalesSummaryMetrics(\Closure $caseSetup): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        // Execute case setup, which creates test data and returns calculated expected JSON
        $expectedJson = $caseSetup($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/sales/summary');

        $response->assertStatus(200)
            ->assertJson($expectedJson);
    }

    public static function salesSummaryCasesProvider(): array
    {
        return [
            'multiple active and cancelled sales' => [
                function (User $user): array {
                    $laptopPrice = 100.0;
                    $mousePrice = 50.0;

                    $laptopQtySale1 = 2;
                    $mouseQtySale1 = 1;
                    $laptopQtySale2 = 1;
                    $mouseQtyCancelled = 1;

                    $laptop = Product::create([
                        'name' => 'Laptop Lenovo',
                        'price' => $laptopPrice,
                        'stock' => 10,
                    ]);

                    $mouse = Product::create([
                        'name' => 'Mouse Wireless',
                        'price' => $mousePrice,
                        'stock' => 10,
                    ]);

                    // 1. Sale 1
                    Sale::createFromItems($user->id, [
                        ['product_id' => $laptop->id, 'quantity' => $laptopQtySale1],
                        ['product_id' => $mouse->id, 'quantity' => $mouseQtySale1],
                    ]);

                    // 2. Sale 2
                    Sale::createFromItems($user->id, [
                        ['product_id' => $laptop->id, 'quantity' => $laptopQtySale2],
                    ]);

                    // 3. Sale 3 (Cancelled)
                    $saleToCancel = Sale::createFromItems($user->id, [
                        ['product_id' => $mouse->id, 'quantity' => $mouseQtyCancelled],
                    ]);
                    $saleToCancel->deleteWithStockRestoration();

                    // Calculate metrics dynamically
                    $sale1Total = ($laptopPrice * $laptopQtySale1) + ($mousePrice * $mouseQtySale1);
                    $sale2Total = ($laptopPrice * $laptopQtySale2);
                    $cancelledTotal = ($mousePrice * $mouseQtyCancelled);

                    $totalActiveRevenue = $sale1Total + $sale2Total;
                    $totalActiveSalesCount = 2;
                    $averageTicket = $totalActiveRevenue / $totalActiveSalesCount;
                    $totalItemsSold = $laptopQtySale1 + $mouseQtySale1 + $laptopQtySale2;
                    $topProductQuantity = $laptopQtySale1 + $laptopQtySale2;

                    return [
                        'total_revenue' => $totalActiveRevenue,
                        'total_sales_count' => $totalActiveSalesCount,
                        'average_ticket' => $averageTicket,
                        'total_items_sold' => $totalItemsSold,
                        'today_revenue' => $totalActiveRevenue,
                        'cancelled_sales_count' => 1,
                        'cancelled_revenue' => $cancelledTotal,
                        'top_product' => [
                            'name' => $laptop->name,
                            'total_quantity' => $topProductQuantity,
                        ],
                    ];
                },
            ],
            'empty database with no sales' => [
                function (User $user): array {
                    return [
                        'total_revenue' => 0.0,
                        'total_sales_count' => 0,
                        'average_ticket' => 0.0,
                        'total_items_sold' => 0,
                        'today_revenue' => 0.0,
                        'cancelled_sales_count' => 0,
                        'cancelled_revenue' => 0.0,
                        'top_product' => [
                            'name' => null,
                            'total_quantity' => 0,
                        ],
                    ];
                },
            ],
            'only cancelled sales' => [
                function (User $user): array {
                    $mousePrice = 50.0;
                    $mouseQuantity = 2;

                    $mouse = Product::create([
                        'name' => 'Mouse Wireless',
                        'price' => $mousePrice,
                        'stock' => 10,
                    ]);

                    $cancelledSale = Sale::createFromItems($user->id, [
                        ['product_id' => $mouse->id, 'quantity' => $mouseQuantity],
                    ]);
                    $cancelledSale->deleteWithStockRestoration();

                    $cancelledTotal = $mousePrice * $mouseQuantity;

                    return [
                        'total_revenue' => 0.0,
                        'total_sales_count' => 0,
                        'average_ticket' => 0.0,
                        'total_items_sold' => 0,
                        'today_revenue' => 0.0,
                        'cancelled_sales_count' => 1,
                        'cancelled_revenue' => $cancelledTotal,
                        'top_product' => [
                            'name' => null,
                            'total_quantity' => 0,
                        ],
                    ];
                },
            ],
        ];
    }
}
