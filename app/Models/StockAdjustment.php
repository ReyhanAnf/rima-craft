<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'adjustable_type',
        'adjustable_id',
        'previous_stock',
        'actual_stock',
        'quantity_difference',
        'reason',
        'user_id'
    ];

    public function adjustable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
