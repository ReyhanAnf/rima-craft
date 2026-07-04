<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRegionPrice extends Model
{
    protected $fillable = [
        'product_id',
        'region_id',
        'base_price',
        'reseller_price',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'reseller_price' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
