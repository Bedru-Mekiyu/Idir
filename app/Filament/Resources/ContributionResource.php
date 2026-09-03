<?php

namespace App\Filament\Resources;

use App\Enums\ChapaStatus;
use App\Enums\ContributionType;
use App\Enums\PaymentMethod;
use App\Filament\Resources\ContributionResource\Pages;
use App\Models\Contribution;
use App\Models\Member;
use App\Services\LedgerService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContributionResource extends Resource
{
    protected static ?string $model = Contribution::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static string | \UnitEnum | null $navigationGroup = 'የገንዘብና ሒሳብ መዝገብ';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('contribution.contribution');
    }

    public static function getPluralModelLabel(): string
    {
        return __('contribution.ledger');
    }

    public static function canViewAny(): bool
    {
        $tenant = \Filament\Facades\Filament::getTenant();
        return $tenant && $tenant->isActive();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('member_id')
                    ->label(__('contribution.paid_for'))
                    ->relationship('member', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('paid_by_member_id')
                    ->label(__('contribution.paid_by'))
                    ->relationship('paidBy', 'full_name')
                    ->searchable()
                    ->preload()
                    ->helperText('ሌላ ሰው ከከፈለ ብቻ ይምረጡ (Optional if different from recipient)'),
                TextInput::make('amount')
                    ->label(__('contribution.amount'))
                    ->numeric()
                    ->prefix('ብር')
                    ->required(),
                Select::make('method')
                    ->label(__('contribution.method.label'))
                    ->options([
                        PaymentMethod::Cash->value => __('contribution.method.cash'),
                        PaymentMethod::Chapa->value => __('contribution.method.chapa'),
                    ])
                    ->default(PaymentMethod::Cash->value)
                    ->required(),
                Select::make('type')
                    ->label(__('contribution.type.label'))
                    ->options([
                        ContributionType::Cash->value => __('contribution.type.cash'),
                        ContributionType::InKind->value => __('contribution.type.in_kind'),
                    ])
                    ->default(ContributionType::Cash->value)
                    ->live()
                    ->required(),
                TextInput::make('in_kind_description')
                    ->label(__('contribution.in_kind_description'))
                    ->visible(fn ($get) => $get('type') === ContributionType::InKind->value)
                    ->maxLength(255),
                TextInput::make('period_covered')
                    ->label(__('contribution.period_covered'))
                    ->default(now()->format('Y-m'))
                    ->placeholder('2026-08')
                    ->required(),
                Textarea::make('notes')
                    ->label(__('common.notes'))
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('common.date'))
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                TextColumn::make('member.full_name')
                    ->label(__('contribution.paid_for'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('contribution.amount'))
                    ->money('ETB')
                    ->sortable()
                    ->color(fn (Contribution $record) => $record->amount < 0 ? 'danger' : 'success'),
                TextColumn::make('method')
                    ->label(__('contribution.method.label'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentMethod ? $state->label() : ($state ? PaymentMethod::tryFrom($state)?->label() ?? $state : '')),
                TextColumn::make('type')
                    ->label(__('contribution.type.label'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ContributionType ? $state->label() : ($state ? ContributionType::tryFrom($state)?->label() ?? $state : '')),
                TextColumn::make('period_covered')
                    ->label(__('contribution.period_covered'))
                    ->badge(),
                TextColumn::make('chapa_status')
                    ->label(__('common.status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ChapaStatus ? $state->label() : ($state ? ChapaStatus::tryFrom($state)?->label() ?? $state : 'ጥሬ ገንዘብ'))
                    ->color(fn ($state) => $state instanceof ChapaStatus ? $state->color() : ($state ? ChapaStatus::tryFrom($state)?->color() ?? 'success' : 'success')),
                IconColumn::make('is_correction')
                    ->label(__('contribution.is_correction'))
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-uturn-left')
                    ->falseIcon('heroicon-o-minus')
                    ->color(fn ($state) => $state ? 'warning' : 'gray'),
                TextColumn::make('recordedBy.full_name')
                    ->label(__('contribution.recorded_by'))
                    ->placeholder('ዲጂታል/ሲስተም'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('method')
                    ->label(__('contribution.method.label'))
                    ->options([
                        PaymentMethod::Cash->value => __('contribution.method.cash'),
                        PaymentMethod::Chapa->value => __('contribution.method.chapa'),
                    ]),
                SelectFilter::make('chapa_status')
                    ->label('የቻፓ ሁኔታ (Chapa Status)')
                    ->options([
                        ChapaStatus::Pending->value => __('contribution.pending'),
                        ChapaStatus::Verified->value => __('contribution.verified'),
                        ChapaStatus::Failed->value => __('contribution.failed'),
                        ChapaStatus::Expired->value => __('contribution.expired'),
                    ]),
                TernaryFilter::make('is_correction')
                    ->label(__('contribution.is_correction')),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('correction')
                    ->label(__('contribution.record_correction'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('reason')
                            ->label(__('contribution.correction_reason'))
                            ->required(),
                    ])
                    ->action(function (Contribution $record, array $data) {
                        app(LedgerService::class)->recordCorrection($record, $data['reason']);
                        Notification::make()
                            ->title('ማስተካከያ ተመዝግቧል')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Contribution $record) => !$record->is_correction && $record->amount > 0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContributions::route('/'),
            'create' => Pages\CreateContribution::route('/create'),
        ];
    }
}
