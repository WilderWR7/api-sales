<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'user_id',
        'total'
    ];

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
