<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'user_id',
        'total'
    ];

    /**
     * Create a sale and its details from an array of items.
     *
     * @param int $userId
     * @param array $items
     * @return self
     * @throws \Exception
     */
    public static function createFromItems(int $userId, array $items): self
    {
        return DB::transaction(function () use ($userId, $items) {
            $productIds = array_column($items, 'product_id');
            // 1. Bulk select and lock all requested products in a single query (ordered to prevent deadlocks)
            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0.0;
            $detailsToInsert = [];

            // 2. Perform stock validation and subtotal calculations in memory
            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product ID {$item['product_id']} not found.");
                }

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;
                $product->stock -= $item['quantity'];
                $product->save();

                $detailsToInsert[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // 3. Create the Sale record (timestamps are automatically managed by Eloquent)
            $sale = self::create([
                'user_id' => $userId,
                'total' => $total,
            ]);

            // 4. Attach sale_id to all details and bulk insert them in a single query
            foreach ($detailsToInsert as &$detail) {
                $detail['sale_id'] = $sale->id;
            }
            unset($detail);

            SaleDetail::insert($detailsToInsert);

            return $sale;
        });
    }

    /**
     * Get the user who made the purchase.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Sale>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the details or items of the sale.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<SaleDetail, Sale>
     */
    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
