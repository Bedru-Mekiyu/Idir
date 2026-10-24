<?php

namespace App\Filament\Pages;

use App\Enums\MemberStatus;
use App\Enums\NotificationType;
use App\Jobs\SendNotificationJob;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SendAnnouncement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'የኮሚቴ አስተዳደር';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.send-announcement';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'ማስታወቂያ ላክ';
    }

    public static function canAccess(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant && $tenant->isActive();
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('message')
                    ->label('የማስታወቂያ ጽሑፍ')
                    ->helperText('ለሁሉም ንቁ አባላት SMS፣ Telegram፣ እና ኢሜይል ይላካል (እንደ ምርጫቸው)።')
                    ->rows(5)
                    ->required()
                    ->minLength(10)
                    ->maxLength(500),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $validated = $this->form->getState();

        $idir = Filament::getTenant();
        $members = Member::where('idir_id', $idir->id)->where('status', MemberStatus::Active)->get();

        foreach ($members as $member) {
            SendNotificationJob::dispatch(
                $idir->id,
                $member->id,
                NotificationType::GeneralAnnouncement,
                ['message' => $validated['message']]
            );
        }

        Notification::make()
            ->title("ማስታወቂያ ለ {$members->count()} አባላት ወደ ቅርጫ ተላከ")
            ->success()
            ->send();

        $this->form->fill();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('ለሁሉም አባላት ላክ')
                ->icon('heroicon-o-paper-airplane')
                ->action('send'),
        ];
    }
}
