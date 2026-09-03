<?php

namespace App\Filament\Resources\ClaimResource\Pages;

use App\Filament\Resources\ClaimResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClaims extends ListRecords
{
    protected static string $resource = ClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('claim.file_claim')),
        ];
    }
}
