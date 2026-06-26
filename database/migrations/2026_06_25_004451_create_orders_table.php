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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // ORD-20260625-001
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Optional for logged-in users
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->json('items'); // [{id, name, qty, price, subtotal}]
            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cod', 'transfer', 'ewallet'])->default('cod');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('payment_proof')->nullable(); // Upload bukti transfer
            $table->enum('order_method', ['whatsapp', 'form'])->default('form'); // How order was placed
            $table->string('whatsapp_url')->nullable(); // WA link if sent via WA
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
