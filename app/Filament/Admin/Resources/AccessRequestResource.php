<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AccessRequestResource\Pages;
use App\Models\AccessRequest;
use App\Services\AfroMessageService;
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

class AccessRequestResource extends Resource
{
    protected static ?string $model = AccessRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'የማኔጀርነት ጥያቄዎች (Access Requests)';

    public static function getModelLabel(): string
    {
        return 'የማኔጀርነት ጥያቄ (Access Request)';
    }

    public static function getPluralModelLabel(): string
    {
        return 'የማኔጀርነት ጥያቄዎች (Access Requests)';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = AccessRequest::where('status', 'pending')->count();

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
                TextInput::make('user.name')
                    ->label('አመልካች (User Name)')
                    ->disabled(),
                TextInput::make('user.phone')
                    ->label('ስልክ ቁጥር (Phone)')
                    ->disabled(),
                TextInput::make('user.email')
                    ->label('ኢሜይል (Email)')
                    ->disabled(),
                TextInput::make('idir_name')
                    ->label('የታሰበው እድር ስም (Proposed Idir Name)')
                    ->disabled(),
                TextInput::make('membership_basis')
                    ->label('የአባልነት መሠረት (Membership Basis)')
                    ->disabled(),
                TextInput::make('region')
                    ->label('ክልል / ከተማ (Region)')
                    ->disabled(),
                TextInput::make('sub_city')
                    ->label('ክፍለ ከተማ / ወረዳ (Sub-city)')
                    ->disabled(),
                TextInput::make('status')
                    ->label('ሁኔታ (Status)')
                    ->disabled(),
                Textarea::make('purpose')
                    ->label('የማመልከቻው ዓላማ (Purpose & Details)')
                    ->rows(3)
                    ->disabled(),
                Textarea::make('denial_reason')
                    ->label('የውድቅ የተደረገበት ምክንያት (Denial Reason)')
                    ->visible(fn (?AccessRequest $record) => $record?->isDenied())
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('አመልካች (User Name)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.phone')
                    ->label('ስልክ ቁጥር (Phone)')
                    ->searchable(),

                TextColumn::make('idir_name')
                    ->label('የታሰበው እድር (Proposed Idir)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('region')
                    ->label('ክልል / ከተማ')
                    ->toggleable(),

                TextColumn::make('purpose')
                    ->label('ዓላማ (Purpose)')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('ሁኔታ (Status)')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'granted' => 'success',
                        'pending' => 'warning',
                        'denied' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'granted' => 'የተፈቀደ (Granted)',
                        'pending' => 'በግምገማ ላይ (Pending)',
                        'denied' => 'ውድቅ የተደረገ (Denied)',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('የቀረበበት ቀን (Date Submitted)')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),

                // GRANT ACTION
                Action::make('grant')
                    ->label('ፈቃድ ስጥ (Grant)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('የማኔጀርነት ፈቃድ ማጽደቅ (Grant Manager Privilege)')
                    ->modalDescription('ይህ ተጠቃሚ እድር እንዲመሠርት እና እንዲያስተዳድር ፈቃድ መስጠት ይፈልጋሉ? ተጠቃሚው በኤስኤምኤስ እና በሲስተሙ ውስጥ ማሳወቂያ ይደርሰዋል።')
                    ->modalSubmitActionLabel('ፈቃድ አጽድቅ (Confirm Grant)')
                    ->visible(fn (AccessRequest $record): bool => $record->isPending())
                    ->action(function (AccessRequest $record) {
                        $record->update([
                            'status' => 'granted',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $record->user->update([
                            'can_create_idir' => true,
                        ]);

                        // Send SMS notification
                        if ($record->user->phone) {
                            $sms = 'እንኳን ደስ አለዎት! የማኔጀርነት ፈቃድ ጥያቄዎ በፕላትፎርም ባለቤቱ ጸድቋል። አሁን ወደ ሲስተሙ በመግባት አዲሱን እድርዎን መመዝገብ ይችላሉ።';
                            app(AfroMessageService::class)->sendSms($record->user->phone, $sms);
                        }

                        Notification::make()
                            ->title('የማኔጀርነት ፈቃድ ተሰጥቷል!')
                            ->body("ለ {$record->user->name} እድር የመመሥረት ፈቃድ በተሳካ ሁኔታ ተሰጥቷል።")
                            ->success()
                            ->send();
                    }),

                // DENY ACTION
                Action::make('deny')
                    ->label('ውድቅ አድርግ (Deny)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('የማኔጀርነት ጥያቄ ውድቅ ማድረግ (Deny Access Request)')
                    ->modalDescription('እባክዎ ጥያቄው ውድቅ የተደረገበትን ምክንያት ያስገቡ። ምክንያቱ ለተጠቃሚው በኤስኤምኤስ ይላካል።')
                    ->modalSubmitActionLabel('ውድቅ ማድረጉን አረጋግጥ (Confirm Denial)')
                    ->form([
                        Textarea::make('denial_reason')
                            ->label('የውድቅ የተደረገበት ምክንያት (Denial Reason)')
                            ->placeholder('ለምሳሌ፡ የቀረበው መረጃ ያልተሟላ በመሆኑ...')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (AccessRequest $record): bool => $record->isPending())
                    ->action(function (AccessRequest $record, array $data) {
                        $record->update([
                            'status' => 'denied',
                            'denial_reason' => $data['denial_reason'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        // Send SMS notification
                        if ($record->user->phone) {
                            $sms = "የማኔጀርነት ፈቃድ ጥያቄዎ ውድቅ ተደርጓል። ምክንያት፦ {$data['denial_reason']}";
                            app(AfroMessageService::class)->sendSms($record->user->phone, $sms);
                        }

                        Notification::make()
                            ->title('የማኔጀርነት ጥያቄው ውድቅ ተደርጓል')
                            ->body("ውሳኔው ለ {$record->user->name} በኤስኤምኤስ ተልኳል።")
                            ->danger()
                            ->send();
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('ሁኔታ (Status)')
                    ->options([
                        'pending' => 'በግምገማ ላይ (Pending)',
                        'granted' => 'የተፈቀደ (Granted)',
                        'denied' => 'ውድቅ የተደረገ (Denied)',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessRequests::route('/'),
            'view' => Pages\ViewAccessRequest::route('/{record}'),
        ];
    }
}
