<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerGates();
        
        try {
            if (Schema::hasTable('settings')) {
                foreach (\App\Models\Setting::all() as $setting) {
                    config()->set('settings.' . $setting->key, $setting->value);
                }
            }
        } catch (\Exception $e) {
            // Ignore if DB is not ready
        }
    }

    /**
     * Register authorization gates for all permissions.
     */
    protected function registerGates(): void
    {
        // Register gates dynamically from database permissions
        Gate::before(function (User $user, string $ability) {
            // Super admin has all permissions
            if ($user->hasRole('super-admin')) {
                return true;
            }
            
            return null;
        });

        // Load permissions when database is ready
        try {
            if (Schema::hasTable('permissions') && Schema::hasTable('roles')) {
                $permissions = Permission::all();
                
                foreach ($permissions as $permission) {
                    Gate::define($permission->name, function (User $user) use ($permission) {
                        return $user->hasPermission($permission->name);
                    });
                }
            }
        } catch (\Exception $e) {
            // Ignore if database is not ready
        }
    }
}
