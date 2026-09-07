<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Public endpoints must be limited per IP. The default inline `throttle:n,1`
        // keys by the authenticated user when one exists — but on these public
        // routes a client can rotate sessions (each signup logs the request in as
        // a fresh user), giving every request a new key and bypassing the limit.
        RateLimiter::for('register', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('phone-verify', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('otp-resend', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('access-request', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('member-login', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('member-lookup', fn (Request $request): Limit => Limit::perMinute(20)->by($request->ip()));

        // Filament's default views call Illuminate\Support\Number::format(),
        // which requires the intl extension. Some minimal PHP distributions
        // (e.g. Herd Lite) ship without intl, which would crash every
        // paginated list and the notifications bell. When intl is missing we
        // register intl-safe overrides that fall back to plain integers.
        if (! extension_loaded('intl')) {
            View::addNamespace('filament', resource_path('views/vendor/filament'));
            View::addNamespace('filament-panels', resource_path('views/vendor/filament-panels'));
        }
    }
}
