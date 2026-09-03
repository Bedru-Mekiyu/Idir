<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IdirResource\Pages;
use App\Models\Idir;
use App\Services\AfroMessageService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IdirResource extends Resource
{
    protected static ?string $model = Idir::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';
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

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('የእድር ስም (Idir Name)')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('membership_basis')
                    ->label('የአባልነት መሠረት (Membership Basis)')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('region')
                    ->label('ክልል / ከተማ')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('sub_city')
                    ->label('ክፍለ ከተማ')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('woreda')
                    ->label('ወረዳ')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('status')
                    ->label('ሁኔታ (Status)')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('settings.dues_amount')
                    ->label('ወርሃዊ መዋጮ (Dues Amount)')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('settings.fund_balance')
                    ->label('የፈንድ መጠን (Fund Balance ETB)')
                    ->disabled(),
                \Filament\Forms\Components\Textarea::make('rejection_reason')
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
                    ->money('ETB')
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

                        // Send approval SMS to founder
                        $founder = $record->founder();
                        if ($founder && $founder->phone) {
                            $sms = "እንኳን ደስ አለዎት! የ{$record->name} እድር ምዝገባ ጥያቄዎ ጸድቋል። አሁን አባላትን መመዝገብና አገልግሎት መጀመር ይችላሉ።";
                            app(AfroMessageService::class)->sendSms($founder->phone, $sms);
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

                        // Send rejection SMS to founder
                        $founder = $record->founder();
                        if ($founder && $founder->phone) {
                            $sms = "የ{$record->name} እድር ምዝገባ ጥያቄዎ ውድቅ ተደርጓል። ምክንያት፦ {$data['rejection_reason']}";
                            app(AfroMessageService::class)->sendSms($founder->phone, $sms);
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
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('ሁኔታ (Status)')
                    ->options([
                        'pending_approval' => 'በማረጋገጥ ላይ (Pending Approval)',
                        'active' => 'ንቁ (Active)',
                        'suspended' => 'የታገደ (Suspended)',
                        'rejected' => 'ውድቅ የተደረገ (Rejected)',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIdirs::route('/'),
            'view' => Pages\ViewIdir::route('/{record}'),
        ];
    }
}
