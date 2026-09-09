<?php

namespace App\Filament\Resources\ClaimResource\Pages;

use App\Enums\ClaimStatus;
use App\Enums\NotificationType;
use App\Filament\Resources\ClaimResource;
use App\Jobs\SendNotificationJob;
use Filament\Resources\Pages\CreateRecord;

class CreateClaim extends CreateRecord
{
    protected static string $resource = ClaimResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = ClaimStatus::Pending;

        return $data;
    }

    protected function afterCreate(): void
    {
        $claim = $this->record;

        // Notify every committee approver that a new claim awaits their review.
        foreach ($claim->idir->committeeMembers as $approver) {
            SendNotificationJob::dispatch(
                $claim->idir_id,
                $approver->id,
                NotificationType::ClaimFiled,
            );
        }
    }
}
