<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Enums\NotificationType;
use App\Filament\Resources\MemberResource;
use App\Jobs\SendNotificationJob;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('member.add_member')),

            // Committee general announcement / broadcast to every active member at once.
            Action::make('broadcast')
                ->label(__('notification.send_announcement'))
                ->icon('heroicon-o-megaphone')
                ->color('info')
                ->visible(fn (): bool => (bool) auth()->user()?->member?->isCommitteeMember())
                ->form([
                    Textarea::make('message')
                        ->label(__('notification.announcement_message'))
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    $idir = Filament::getTenant();

                    $count = 0;
                    foreach ($idir->activeMembers as $member) {
                        SendNotificationJob::dispatch(
                            $idir->id,
                            $member->id,
                            NotificationType::GeneralAnnouncement,
                            ['message' => $data['message']],
                        );
                        $count++;
                    }

                    Notification::make()
                        ->title(__('notification.announcement_sent', ['count' => $count]))
                        ->success()
                        ->send();
                }),
        ];
    }
}
