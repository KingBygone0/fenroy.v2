<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class StockAlertService
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function checkAfterOrder(array $items): void
    {
        $slugs = array_column($items, 'slug');
        if (empty($slugs)) return;

        $lowStock = Product::whereIn('slug', $slugs)
            ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->where('stock', '>', 0)
            ->get();

        $outOfStock = Product::whereIn('slug', $slugs)
            ->where('stock', 0)
            ->get();

        if ($lowStock->isEmpty() && $outOfStock->isEmpty()) return;

        try {
            $sms     = new ArkeselService();
            $admin   = config('arkesel.admin_phone');

            foreach ($outOfStock as $p) {
                Log::warning("Fenroy: {$p->name} (SKU: {$p->sku}) is OUT OF STOCK.");
                if ($admin) {
                    $sms->send($admin, "Fenroy ALERT: \"{$p->name}\" is OUT OF STOCK. Restock urgently.");
                }
            }

            foreach ($lowStock as $p) {
                Log::warning("Fenroy: {$p->name} (SKU: {$p->sku}) is low — only {$p->stock} left.");
                if ($admin) {
                    $sms->send($admin, "Fenroy ALERT: \"{$p->name}\" is low stock — only {$p->stock} unit(s) left.");
                }
            }
        } catch (\Throwable $e) {
            Log::error('StockAlertService failed: ' . $e->getMessage());
        }
    }
}
