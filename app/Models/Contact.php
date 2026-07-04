<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['type', 'user_id', 'name', 'email', 'phone', 'address', 'company_name', 'business_type', 'province_id', 'city_id'])]
class Contact extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the user that owns the contact.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Region::class, 'province_id');
    }

    public function city()
    {
        return $this->belongsTo(Region::class, 'city_id');
    }
}
