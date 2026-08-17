<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Tracks whether post-payment side effects (email, SMS, stock alerts)
            // have been dispatched. Null means verify() crashed before completing them;
            // the webhook uses this to send notifications as a fallback.
            $table->timestamp('notified_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });
    }
};
