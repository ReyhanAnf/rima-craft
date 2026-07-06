<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashLedger extends Model
{
    use HasFactory;

    // Category constants for classifying ledger entries
    const CATEGORY_SALE_INCOME           = 'sale_income';
    const CATEGORY_PURCHASE_EXPENSE      = 'purchase_expense';
    const CATEGORY_PRODUCTION_MATERIAL   = 'production_material';
    const CATEGORY_PRODUCTION_LABOR      = 'production_labor';
    const CATEGORY_PRODUCTION_OVERHEAD   = 'production_overhead';
    const CATEGORY_MANUAL                = 'manual';
    const CATEGORY_OTHER                 = 'other';

    protected $fillable = [
        'account_id',
        'date',
        'type',
        'category',
        'amount',
        'balance_after',
        'description',
        'payment_label',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
