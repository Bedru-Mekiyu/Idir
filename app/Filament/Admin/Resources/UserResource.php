<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'የሲስተም ተጠቃሚዎች (All Users)';

    public static function getModelLabel(): string
    {
        return 'ተጠቃሚ (User)';
    }

    public static function getPluralModelLabel(): string
    {
        return 'ተጠቃሚዎች (All Users)';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('ሙሉ ስም (Name)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('ኢሜይል (Email)')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('ስልክ ቁጥር (Phone)')
                    ->searchable(),

                IconColumn::make('is_platform_owner')
                    ->label('የፕላትፎርም ባለቤት (Super Admin)')
                    ->boolean(),

                TextColumn::make('idirs.name')
                    ->label('የተመዘገቡባቸው እድሮች (Idirs)')
                    ->badge()
                    ->color('primary')
                    ->separator(','),

                TextColumn::make('created_at')
                    ->label('የተመዘገበበት ቀን')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
