<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pending orders are kept a week past expiry so unmatched payments can still be traced.
Illuminate\Support\Facades\Schedule::call(function () {
    \App\Models\PendingOrder::where('expires_at', '<', now()->subDays(7))->delete();
})->daily()->name('prune-pending-orders');
