<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionArtisanWage extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'artisan_id',
        'amount',
        'work_description',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }
}
