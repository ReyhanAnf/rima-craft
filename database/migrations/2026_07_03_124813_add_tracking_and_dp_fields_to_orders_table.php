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
            $table->decimal('down_payment_amount', 15, 2)->default(0)->after('total');
            $table->decimal('remaining_balance', 15, 2)->default(0)->after('down_payment_amount');
            $table->string('tracking_number', 100)->nullable()->after('remaining_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['down_payment_amount', 'remaining_balance', 'tracking_number']);
        });
    }
};
