<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'region_id',
        'shipping_cost',
    ];

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
        ];
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
