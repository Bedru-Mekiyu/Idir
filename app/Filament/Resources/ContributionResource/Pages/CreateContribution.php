<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use App\Filament\Resources\ContributionResource;
use App\Services\LedgerService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateContribution extends CreateRecord
{
    protected static string $resource = ContributionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['recorded_by_member_id'] = auth()->user()?->member?->id;
        $data['is_correction'] = false;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(LedgerService::class)->recordContribution($data);
    }
}
