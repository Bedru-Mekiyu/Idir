<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class IdirServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge idir-specific service configs into the services config
        $idirServices = require config_path('services_idir.php');
        foreach ($idirServices as $key => $value) {
            config(["services.{$key}" => $value]);
        }
    }

    public function boot(): void
    {
        // Set the default locale to Amharic
        app()->setLocale(config('idir.default_locale', 'am'));
    }
}
