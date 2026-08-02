<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('type')->default('info'); // info, warning, success, danger
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);
            $table->string('url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed permission manage-announcements and attach to admin/operator roles
        $permission = Permission::firstOrCreate(['name' => 'manage-announcements']);

        $allowedRoles = ['dev-admin', 'super-admin', 'owner', 'operator'];
        $roles = Role::whereIn('name', $allowedRoles)->get();

        foreach ($roles as $role) {
            if (!$role->permissions()->where('permission_id', $permission->id)->exists()) {
                $role->permissions()->attach($permission->id);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');

        $permission = Permission::where('name', 'manage-announcements')->first();
        if ($permission) {
            $permission->roles()->detach();
            $permission->delete();
        }
    }
};
