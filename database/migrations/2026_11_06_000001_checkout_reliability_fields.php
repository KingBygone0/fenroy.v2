<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guest checkouts also get a pending order row, so user_id must be optional.
        Schema::table('pending_orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('substitution', 40)->nullable()->after('delivery_window');
        });

        // Un-double-encode items saved as a JSON string inside JSON.
        DB::table('orders')->select('id', 'items')->orderBy('id')->chunkById(200, function ($orders) {
            foreach ($orders as $order) {
                $decoded = json_decode((string) $order->items, true);
                if (is_string($decoded) && is_array(json_decode($decoded, true))) {
                    DB::table('orders')->where('id', $order->id)->update(['items' => $decoded]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('substitution');
        });
    }
};
