<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUserPrice extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the product that owns this price override.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user (reseller) that owns this price override.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
