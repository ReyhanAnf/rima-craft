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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // BCA Transfer, GoPay, OVO, dll
            $table->string('type'); // bank, ewallet, retail
            $table->string('code')->unique(); // bca, gopay, ovo
            $table->string('account_number')->nullable(); // No rek / no telp
            $table->string('account_name')->nullable(); // Nama pemilik
            $table->text('description')->nullable(); // Instruksi tambahan
            $table->string('icon')->nullable(); // Icon path
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
