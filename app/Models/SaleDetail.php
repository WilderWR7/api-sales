<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'subtotal'
    ];

    /**
     * Get the sale that owns this detail.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Sale, SaleDetail>
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product associated with this sale detail.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, SaleDetail>
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
