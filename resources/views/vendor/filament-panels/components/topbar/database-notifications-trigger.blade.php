@php
    // Fallback for PHP builds without the intl extension (see AppServiceProvider).
    $formatNumber = fn (int|float $number): int|float|string => extension_loaded('intl') ? \Illuminate\Support\Number::format($number) : (int) $number;
@endphp

<x-filament::icon-button
    :badge="$unreadNotificationsCount ?: null"
    color="gray"
    :icon="\Filament\Support\Icons\Heroicon::OutlinedBell"
    :icon-alias="\Filament\View\PanelsIconAlias::TOPBAR_OPEN_DATABASE_NOTIFICATIONS_BUTTON"
    icon-size="lg"
    :label="
        $unreadNotificationsCount
        ? trans_choice('filament-panels::layout.actions.open_database_notifications.label_with_unread_count', $unreadNotificationsCount, ['count' => $formatNumber($unreadNotificationsCount)])
        : __('filament-panels::layout.actions.open_database_notifications.label')
    "
    class="fi-topbar-database-notifications-btn"
/>