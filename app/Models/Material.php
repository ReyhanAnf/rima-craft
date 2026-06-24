<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'unit', 'min_stock', 'current_stock', 'last_buy_price'])]
class Material extends Model
{
    use HasFactory, SoftDeletes;
    
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function productionMaterials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    public function stockAdjustments()
    {
        return $this->morphMany(StockAdjustment::class, 'adjustable');
    }

    protected function casts(): array
    {
        return [
            'min_stock' => 'integer',
            'current_stock' => 'integer',
            'last_buy_price' => 'decimal:2',
        ];
    }
}
