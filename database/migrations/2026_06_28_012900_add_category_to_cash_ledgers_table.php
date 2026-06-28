<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_ledgers', function (Blueprint $table) {
            // Category to classify each ledger entry for detailed reporting
            $table->string('category', 50)->default('other')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('cash_ledgers', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
