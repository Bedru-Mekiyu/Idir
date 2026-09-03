<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditIdirProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return __('idir.settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('idir.idir_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('membership_basis')
                    ->label(__('idir.membership_basis'))
                    ->placeholder(__('idir.membership_basis_help'))
                    ->maxLength(255),
                TextInput::make('region')
                    ->label(__('idir.region'))
                    ->maxLength(255),
                TextInput::make('sub_city')
                    ->label(__('idir.sub_city'))
                    ->maxLength(255),
                TextInput::make('woreda')
                    ->label(__('idir.woreda'))
                    ->maxLength(255),
            ]);
    }
}
