<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Frequently queried in order history (AccountOrders, route guards)
            $table->index('customer_email', 'orders_customer_email_idx');

            // Queried on every Paystack verify() and webhook — must be fast
            $table->index('paystack_ref', 'orders_paystack_ref_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_email_idx');
            $table->dropIndex('orders_paystack_ref_idx');
        });
    }
};
