<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Order::observe(OrderObserver::class);

        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Rate limit: 5 login attempts per minute per IP
        RateLimiter::for('login', fn (Request $r) =>
            Limit::perMinute(5)->by($r->ip())
        );

        // Rate limit: 3 password reset requests per hour per IP
        RateLimiter::for('password.reset', fn (Request $r) =>
            Limit::perHour(3)->by($r->ip())
        );
    }
}
