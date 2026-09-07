<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Jobs
Schedule::command('idir:expire-pending-payments')->hourly();
Schedule::command('idir:check-arrears')->daily();
