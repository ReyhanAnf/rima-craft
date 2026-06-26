<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['type', 'user_id', 'name', 'email', 'phone', 'address', 'company_name', 'business_type'])]
class Contact extends Model
{
    use HasFactory, SoftDeletes;
}
