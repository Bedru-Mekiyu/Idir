<?php

namespace App\Filament\Widgets;

use App\Models\Idir;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class TenantStatusBannerWidget extends Widget
{
    protected string $view = 'filament.widgets.tenant-status-banner';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10; // Appear at very top

    public function getTenant(): ?Idir
    {
        return Filament::getTenant();
    }

    public static function canView(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant && ! $tenant->isActive();
    }
}
