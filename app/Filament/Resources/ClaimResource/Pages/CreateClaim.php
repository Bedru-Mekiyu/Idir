<?php

namespace App\Filament\Resources\ClaimResource\Pages;

use App\Enums\ClaimStatus;
use App\Filament\Resources\ClaimResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClaim extends CreateRecord
{
    protected static string $resource = ClaimResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = ClaimStatus::Pending;

        return $data;
    }
}
