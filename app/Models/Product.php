<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'base_price', 'current_stock', 'image_path', 'media_assets'])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    public function productionResults()
    {
        return $this->hasMany(ProductionResult::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockAdjustments()
    {
        return $this->morphMany(StockAdjustment::class, 'adjustable');
    }

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'current_stock' => 'integer',
            'media_assets' => 'array',
        ];
    }
}
