<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CommitteePanelProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\IdirServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    CommitteePanelProvider::class,
    HorizonServiceProvider::class,
    IdirServiceProvider::class,
];
