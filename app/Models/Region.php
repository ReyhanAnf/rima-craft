<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'code',
        'parent_id',
        'type',
        'name',
    ];

    public function shippingRate()
    {
        return $this->hasOne(ShippingRate::class, 'region_id');
    }

    public function parent()
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Region::class, 'parent_id');
    }
}
