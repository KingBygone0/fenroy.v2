<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('paystack_ref')->nullable()->after('notes');
            $table->json('items')->nullable()->after('paystack_ref');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('items');
            $table->decimal('discount', 10, 2)->default(0)->after('delivery_fee');
            $table->string('coupon_code')->nullable()->after('discount');
            $table->string('delivery_window')->nullable()->after('coupon_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['paystack_ref', 'items', 'delivery_fee', 'discount', 'coupon_code', 'delivery_window']);
        });
    }
};
