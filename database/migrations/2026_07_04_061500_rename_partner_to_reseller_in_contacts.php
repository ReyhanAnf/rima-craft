<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename contact type 'partner' to 'reseller' in the contacts table.
     */
    public function up(): void
    {
        // Step 1: Update existing 'partner' records to 'reseller'
        DB::statement("UPDATE contacts SET type = 'reseller' WHERE type = 'partner'");

        // Step 2: Update enum to replace 'partner' with 'reseller' (MySQL only — SQLite uses TEXT)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE contacts MODIFY COLUMN type ENUM('supplier', 'customer', 'crafter', 'reseller') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Revert: rename 'reseller' back to 'partner'
            DB::statement("ALTER TABLE contacts MODIFY COLUMN type ENUM('supplier', 'customer', 'crafter', 'partner', 'reseller') NOT NULL");
            DB::statement("UPDATE contacts SET type = 'partner' WHERE type = 'reseller'");
            DB::statement("ALTER TABLE contacts MODIFY COLUMN type ENUM('supplier', 'customer', 'crafter', 'partner') NOT NULL");
        } else {
            DB::statement("UPDATE contacts SET type = 'partner' WHERE type = 'reseller'");
        }
    }
};
