<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by_member_id'] = auth()->user()?->member?->id;
        $data['deletion_requested'] = false;
        $data['deletion_confirmed'] = false;

        return $data;
    }
}
