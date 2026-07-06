<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // null  = not a reseller
            // pending  = registered as reseller, awaiting admin verification
            // verified = admin approved, full reseller access
            // rejected = admin rejected
            $table->string('reseller_status')->nullable()->default(null)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('reseller_status');
        });
    }
};
