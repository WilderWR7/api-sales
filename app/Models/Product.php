<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock',
        'image'
    ];

    /**
     * Get the sale details where this product was purchased.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<SaleDetail, Product>
     */
    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
