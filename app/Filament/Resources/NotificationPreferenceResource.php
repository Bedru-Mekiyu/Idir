<?php

namespace App\Filament\Resources;

use App\Enums\NotificationType;
use App\Filament\Resources\NotificationPreferenceResource\Pages;
use App\Models\NotificationPreference;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotificationPreferenceResource extends Resource
{
    protected static ?string $model = NotificationPreference::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bell';
    protected static string | \UnitEnum | null $navigationGroup = 'የኮሚቴ አስተዳደር';
    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('notification.notification_preferences');
    }

    public static function getPluralModelLabel(): string
    {
        return __('notification.notification_preferences');
    }

    public static function canViewAny(): bool
    {
        $tenant = \Filament\Facades\Filament::getTenant();
        return $tenant && $tenant->isActive();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('sms_enabled')
                    ->label(__('notification.sms_enabled'))
                    ->default(true),
                Toggle::make('telegram_enabled')
                    ->label(__('notification.telegram_enabled'))
                    ->default(false),
                Toggle::make('in_app_enabled')
                    ->label(__('notification.in_app_enabled'))
                    ->default(true),
                Textarea::make('template_am')
                    ->label(__('notification.template_am'))
                    ->helperText('ተለዋዋጭ ቃላት፦ :member_name, :amount, :period, :idir_name, :trigger, :reason')
                    ->rows(4)
                    ->required(),
                Textarea::make('template_en')
                    ->label(__('notification.template_en'))
                    ->rows(4)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_type')
                    ->label(__('common.description'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof NotificationType ? $state->label() : ($state ? NotificationType::tryFrom($state)?->label() ?? $state : '')),
                IconColumn::make('sms_enabled')
                    ->label(__('notification.channel.sms'))
                    ->boolean(),
                IconColumn::make('telegram_enabled')
                    ->label(__('notification.channel.telegram'))
                    ->boolean(),
                IconColumn::make('in_app_enabled')
                    ->label(__('notification.channel.in_app'))
                    ->boolean(),
                TextColumn::make('template_am')
                    ->label(__('notification.template_am'))
                    ->limit(60),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationPreferences::route('/'),
            'edit' => Pages\EditNotificationPreference::route('/{record}/edit'),
        ];
    }
}
