<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            // Labor cost (upah tenaga kerja) — recorded separately in cash ledger
            $table->decimal('labor_cost', 15, 2)->default(0)->after('additional_cost');
            // Overhead cost (listrik, gas, dll) — recorded separately in cash ledger
            $table->decimal('overhead_cost', 15, 2)->default(0)->after('labor_cost');
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn(['labor_cost', 'overhead_cost']);
        });
    }
};
