<?php

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
        Schema::table('contacts', function (Blueprint $table) {
            // Add user_id for linking to user accounts
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            
            // Add email
            $table->string('email')->nullable()->after('name');
            
            // Add company_name for partners/suppliers
            $table->string('company_name')->nullable()->after('email');
            
            // Add business_type
            $table->string('business_type')->nullable()->after('company_name');
            
            // Update type enum to include 'partner'
            // Note: In production, use doctrine/dbal or raw SQL for enum modification
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'email', 'company_name', 'business_type']);
        });
    }
};
