<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'product_id',
        'qty',
        'allocated_cost_per_unit',
    ];

    protected $casts = [
        'qty' => 'integer',
        'allocated_cost_per_unit' => 'decimal:2',
    ];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
