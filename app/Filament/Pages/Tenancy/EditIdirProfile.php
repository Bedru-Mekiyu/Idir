<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
                TextInput::make('chapa_subaccount_id')
                    ->label(__('idir.chapa_subaccount_id'))
                    ->helperText(__('idir.chapa_subaccount_id_help'))
                    ->maxLength(255),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['chapa_subaccount_id'] = $this->tenant->settings?->chapa_subaccount_id;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $subaccountId = $data['chapa_subaccount_id'] ?? null;
            unset($data['chapa_subaccount_id']);

            $record->update($data);

            $record->settings()->updateOrCreate([], ['chapa_subaccount_id' => $subaccountId]);

            return $record;
        });
    }
}
