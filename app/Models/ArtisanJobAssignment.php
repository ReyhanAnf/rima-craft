<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtisanJobAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_job_id',
        'artisan_id',
        'assigned_wage',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_wage' => 'decimal:2',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ArtisanJob::class, 'artisan_job_id');
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }
}
