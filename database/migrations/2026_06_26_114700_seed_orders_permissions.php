<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $viewOrders = Permission::firstOrCreate(['name' => 'view-orders']);
            $manageOrders = Permission::firstOrCreate(['name' => 'manage-orders']);

            $roles = Role::whereIn('name', ['owner', 'operator', 'super-admin'])->get();
            foreach ($roles as $role) {
                $role->permissions()->syncWithoutDetaching([$viewOrders->id, $manageOrders->id]);
            }
        } catch (\Exception $e) {
            // Ignore failure if tables do not exist yet during migration bootstrap
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            $viewOrders = Permission::where('name', 'view-orders')->first();
            $manageOrders = Permission::where('name', 'manage-orders')->first();

            if ($viewOrders && $manageOrders) {
                $roles = Role::whereIn('name', ['owner', 'operator', 'super-admin'])->get();
                foreach ($roles as $role) {
                    $role->permissions()->detach([$viewOrders->id, $manageOrders->id]);
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }
    }
};
