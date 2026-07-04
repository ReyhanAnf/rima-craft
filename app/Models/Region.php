<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'shipping_cost',
    ];

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
        ];
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
