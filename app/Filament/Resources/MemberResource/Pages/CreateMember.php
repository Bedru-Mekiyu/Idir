<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Enums\NotificationType;
use App\Filament\Resources\MemberResource;
use App\Jobs\SendNotificationJob;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function afterCreate(): void
    {
        $member = $this->record;

        // Welcome the newly registered member.
        SendNotificationJob::dispatch(
            $member->idir_id,
            $member->id,
            NotificationType::NewMemberWelcome,
        );
    }
}
