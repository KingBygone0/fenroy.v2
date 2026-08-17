<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Prevents two concurrent verify() calls from creating duplicate orders
            // for the same Paystack reference. MySQL allows multiple NULLs in a unique
            // index, so manually-created admin orders without a ref are unaffected.
            $table->unique('paystack_ref');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['paystack_ref']);
        });
    }
};
