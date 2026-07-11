<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_artisan_wages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artisan_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('work_description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('artisan_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('estimated_wage', 15, 2)->default(0);
            $table->date('work_date')->nullable();
            $table->enum('status', ['open', 'in_progress', 'done', 'cancelled'])->default('open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('artisan_job_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artisan_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('assigned_wage', 15, 2)->nullable();
            $table->enum('status', ['interested', 'assigned', 'done', 'cancelled'])->default('interested');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['artisan_job_id', 'artisan_id']);
        });

        $viewPortal = Permission::firstOrCreate(['name' => 'view-artisan-portal']);
        $manageJobs = Permission::firstOrCreate(['name' => 'manage-artisan-jobs']);
        $artisanRole = Role::firstOrCreate(['name' => 'pengrajin']);
        $artisanRole->permissions()->syncWithoutDetaching([$viewPortal->id]);

        Role::whereIn('name', ['owner', 'operator', 'super-admin'])->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching([$manageJobs->id]));
    }

    public function down(): void
    {
        Schema::dropIfExists('artisan_job_assignments');
        Schema::dropIfExists('artisan_jobs');
        Schema::dropIfExists('production_artisan_wages');
    }
};
