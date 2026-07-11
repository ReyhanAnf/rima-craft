<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'reseller_status', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function contact()
    {
        return $this->hasOne(Contact::class);
    }

    public function productionWages()
    {
        return $this->hasMany(ProductionArtisanWage::class, 'artisan_id');
    }

    public function artisanJobAssignments()
    {
        return $this->hasMany(ArtisanJobAssignment::class, 'artisan_id');
    }

    // -------------------------------------------------------------------------
    // Role helpers
    // -------------------------------------------------------------------------

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    // -------------------------------------------------------------------------
    // Permission helpers
    // -------------------------------------------------------------------------

    public function hasPermission(string $permissionName): bool
    {
        if ($this->hasRole('dev-admin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $permissionName))
            ->exists();
    }

    public function hasAllPermissions(array $permissionNames): bool
    {
        foreach ($permissionNames as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    // -------------------------------------------------------------------------
    // Reseller status helpers
    // -------------------------------------------------------------------------

    /**
     * Whether this user is a reseller (regardless of verification status).
     */
    public function isReseller(): bool
    {
        return $this->hasRole('reseller');
    }

    /**
     * Whether the reseller account has been verified by admin.
     */
    public function isVerifiedReseller(): bool
    {
        return $this->reseller_status === 'verified';
    }

    /**
     * Whether the reseller account is pending verification.
     */
    public function isPendingReseller(): bool
    {
        return $this->reseller_status === 'pending';
    }
}
