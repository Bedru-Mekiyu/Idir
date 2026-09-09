<?php

namespace App\Filament\Admin\Resources;

use App\Enums\NotificationType;
use App\Filament\Admin\Resources\IdirResource\Pages;
use App\Jobs\SendNotificationJob;
use App\Models\Idir;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IdirResource extends Resource
{
    protected static ?string $model = Idir::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'እድሮች (All Idirs)';

    public static function getModelLabel(): string
    {
        return 'እድር (Idir)';
    }

    public static function getPluralModelLabel(): string
    {
        return 'እድሮች (All Idirs)';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Idir::where('status', 'pending_approval')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('የእድር ስም (Idir Name)')
                    ->disabled(),
                TextInput::make('membership_basis')
                    ->label('የአባልነት መሠረት (Membership Basis)')
                    ->disabled(),
                TextInput::make('region')
                    ->label('ክልል / ከተማ')
                    ->disabled(),
                TextInput::make('sub_city')
                    ->label('ክፍለ ከተማ')
                    ->disabled(),
                TextInput::make('woreda')
                    ->label('ወረዳ')
                    ->disabled(),
                TextInput::make('status')
                    ->label('ሁኔታ (Status)')
                    ->disabled(),
                TextInput::make('settings.dues_amount')
                    ->label('ወርሃዊ መዋጮ (Dues Amount)')
                    ->disabled(),
                TextInput::make('settings.fund_balance')
                    ->label('የፈንድ መጠን (Fund Balance ETB)')
                    ->disabled(),
                Textarea::make('rejection_reason')
                    ->label('የውድቅ የተደረገበት ምክንያት (Rejection Reason)')
                    ->visible(fn (?Idir $record) => $record?->isRejected())
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('የእድር ስም (Idir Name)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('region')
                    ->label('ክልል / ከተማ')
                    ->toggleable(),

                TextColumn::make('sub_city')
                    ->label('ክፍለ ከተማ')
                    ->searchable(),

                TextColumn::make('members_count')
                    ->label('የአባላት ብዛት')
                    ->counts('members')
                    ->sortable(),

                TextColumn::make('settings.fund_balance')
                    ->label('የፈንድ መጠን (ETB)')
                    // Deterministic currency formatting that does not require the intl extension.
                    ->formatStateUsing(fn ($state): string => 'ETB '.number_format((float) $state, 2))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('ሁኔታ (Status)')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending_approval' => 'warning',
                        'suspended' => 'danger',
                        'rejected' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'ንቁ (Active)',
                        'pending_approval' => 'በማረጋገጥ ላይ (Pending Approval)',
                        'suspended' => 'የታገደ (Suspended)',
                        'rejected' => 'ውድቅ የተደረገ (Rejected)',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('የተመዘገበበት ቀን')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),

                // APPROVE ACTION
                Action::make('approve')
                    ->label('አጽድቅ (Approve)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('እድር ማጽደቅ (Approve Idir)')
                    ->modalDescription('ይህ እድር ሲጸድቅ ወዲያውኑ ንቁ ይሆናል፤ ሰብሳቢውና አባላቱም መዋጮ መቀበልና መመዝገብ ይችላሉ።')
                    ->visible(fn (Idir $record): bool => $record->isPendingApproval())
                    ->action(function (Idir $record) {
                        $record->update([
                            'status' => 'active',
                            'approved_at' => now(),
                            'rejection_reason' => null,
                        ]);

                        // Notify the founder via the audited queue job (SMS + Telegram + NotificationEvent).
                        $founder = $record->founder();
                        if ($founder) {
                            SendNotificationJob::dispatch(
                                $record->id,
                                $founder->id,
                                NotificationType::IdirApproved,
                            );
                        }

                        Notification::make()
                            ->title('እድሩ በተሳካ ሁኔታ ጸድቋል!')
                            ->body("የ {$record->name} እድር ወደ ንቁ ሁኔታ ተቀይሯል።")
                            ->success()
                            ->send();
                    }),

                // REJECT ACTION
                Action::make('reject')
                    ->label('ውድቅ አድርግ (Reject)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('እድር ውድቅ ማድረግ (Reject Idir)')
                    ->modalDescription('እባክዎ የእድሩ ምዝገባ ውድቅ የተደረገበትን ምክንያት ያስገቡ። ለሰብሳቢው በኤስኤምኤስ ይላካል።')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('የውድቅ የተደረገበት ምክንያት (Rejection Reason)')
                            ->placeholder('ለምሳሌ፡ የቀረበው የሕግ ደንብ ያልተሟላ በመሆኑ...')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (Idir $record): bool => $record->isPendingApproval())
                    ->action(function (Idir $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'rejected_at' => now(),
                        ]);

                        // Notify the founder via the audited queue job (SMS + Telegram + NotificationEvent).
                        $founder = $record->founder();
                        if ($founder) {
                            SendNotificationJob::dispatch(
                                $record->id,
                                $founder->id,
                                NotificationType::IdirRejected,
                                ['reason' => $data['rejection_reason']],
                            );
                        }

                        Notification::make()
                            ->title('የእድር ምዝገባው ውድቅ ተደርጓል')
                            ->danger()
                            ->send();
                    }),

                // SUSPEND ACTION
                Action::make('suspend')
                    ->label('እድር አግድ (Suspend)')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('እድር ማገድ (Suspend Idir)')
                    ->modalDescription('እድሩ ሲታገድ ኮሚቴውና አባላቱ ወደ ሲስተም መግባት አይችሉም።')
                    ->visible(fn (Idir $record): bool => $record->isActive())
                    ->action(function (Idir $record) {
                        $record->update(['status' => 'suspended']);
                        Notification::make()
                            ->title('እድሩ በጊዜያዊነት ታግዷል')
                            ->danger()
                            ->send();
                    }),

                // REACTIVATE ACTION
                Action::make('reactivate')
                    ->label('እድር መልስ (Reactivate)')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('እድር ማንቃት (Reactivate Idir)')
                    ->modalDescription('እድሩ እንደገና ንቁ ይሆናል፤ ኮሚቴውና አባላቱም አገልግሎቱን መጠቀም ይችላሉ።')
                    ->visible(fn (Idir $record): bool => $record->isSuspended())
                    ->action(function (Idir $record) {
                        $record->update(['status' => 'active']);
                        Notification::make()
                            ->title('እድሩ ወደ ንቁ ሁኔታ ተመልሷል')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('ሁኔታ (Status)')
                    ->options([
                        'pending_approval' => 'በማረጋገጥ ላይ (Pending Approval)',
                        'active' => 'ንቁ (Active)',
                        'suspended' => 'የታገደ (Suspended)',
                        'rejected' => 'ውድቅ የተደረገ (Rejected)',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIdirs::route('/'),
            'view' => Pages\ViewIdir::route('/{record}'),
        ];
    }
}
