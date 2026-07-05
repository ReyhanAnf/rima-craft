<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure Kas Utama exists with ID 1
        DB::table('accounts')->insertOrIgnore([
            'id' => 1,
            'name' => 'Kas Utama',
            'type' => 'cash',
            'balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Point all existing transactions to Kas Utama (ID 1)
        DB::table('cash_ledgers')->update(['account_id' => 1]);
        DB::table('payments')->update(['account_id' => 1]);

        // 3. Recalculate cash balance of Kas Utama based on all ledgers
        $totalIn = DB::table('cash_ledgers')->where('account_id', 1)->where('type', 'in')->sum('amount');
        $totalOut = DB::table('cash_ledgers')->where('account_id', 1)->where('type', 'out')->sum('amount');
        DB::table('accounts')->where('id', 1)->update(['balance' => $totalIn - $totalOut]);

        // 4. Add payment_label column to cash_ledgers
        Schema::table('cash_ledgers', function (Blueprint $table) {
            $table->string('payment_label')->nullable()->after('description');
        });

        // 5. Backfill existing descriptions to default labels
        DB::table('cash_ledgers')->where('category', 'sale_income')->update(['payment_label' => 'Online']);
        DB::table('cash_ledgers')->where('category', 'purchase_expense')->update(['payment_label' => 'Cash']);
        DB::table('cash_ledgers')->whereNull('payment_label')->update(['payment_label' => 'Cash']);
    }

    public function down(): void
    {
        Schema::table('cash_ledgers', function (Blueprint $table) {
            $table->dropColumn('payment_label');
        });
    }
};
