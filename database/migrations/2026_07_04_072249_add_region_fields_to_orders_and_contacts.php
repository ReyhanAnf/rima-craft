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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('regions')->nullOnDelete();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('regions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['province_id', 'city_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['province_id', 'city_id']);
        });
    }
};
