<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtisanJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'estimated_wage',
        'work_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'estimated_wage' => 'decimal:2',
        'work_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ArtisanJobAssignment::class);
    }
}
