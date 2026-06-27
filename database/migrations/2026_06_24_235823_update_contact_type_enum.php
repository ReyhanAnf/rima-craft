<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update enum type to include 'partner'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE contacts MODIFY COLUMN type ENUM('supplier', 'customer', 'crafter', 'partner') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum type (make sure no 'partner' records exist first)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE contacts MODIFY COLUMN type ENUM('supplier', 'customer', 'crafter') NOT NULL");
        }
    }
};
