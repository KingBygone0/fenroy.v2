<?php

namespace App\Providers;

use App\Listeners\LogLoginActivity;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Order::observe(OrderObserver::class);

        // Login activity logging
        $listener = new LogLoginActivity(app(Request::class));
        Event::listen(Login::class,  [$listener, 'handleLogin']);
        Event::listen(Failed::class, [$listener, 'handleFailed']);

        // Rate-limit Livewire's component update endpoint: 120 calls per minute per user/IP
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(['web', 'throttle:120,1']);
        });

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
