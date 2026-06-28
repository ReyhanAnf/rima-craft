<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Production extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'additional_cost',
        'labor_cost',
        'overhead_cost',
        'total_material_cost',
        'grand_total_cost',
        'notes',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'additional_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'total_material_cost' => 'decimal:2',
        'grand_total_cost' => 'decimal:2',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ProductionResult::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
