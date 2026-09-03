<?php

namespace App\Filament\Resources;

use App\Enums\MeetingType;
use App\Filament\Resources\MeetingResource\Pages;
use App\Models\Meeting;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string | \UnitEnum | null $navigationGroup = 'የኮሚቴ አስተዳደር';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('meeting.meeting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('meeting.meetings');
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
                Select::make('type')
                    ->label(__('meeting.meeting_type'))
                    ->options([
                        MeetingType::GeneralAssembly->value => __('meeting.type.general_assembly'),
                        MeetingType::Executive->value => __('meeting.type.executive'),
                        MeetingType::Trustee->value => __('meeting.type.trustee'),
                    ])
                    ->default(MeetingType::GeneralAssembly->value)
                    ->required(),
                DatePicker::make('meeting_date')
                    ->label(__('meeting.meeting_date'))
                    ->default(now())
                    ->required(),
                Textarea::make('minutes')
                    ->label(__('meeting.minutes'))
                    ->rows(6)
                    ->nullable(),
                FileUpload::make('attachment_path')
                    ->label(__('meeting.attachment'))
                    ->directory('meetings/attachments')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meeting_date')
                    ->label(__('meeting.meeting_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('meeting.meeting_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof MeetingType ? $state->label() : ($state ? MeetingType::tryFrom($state)?->label() ?? $state : '')),
                TextColumn::make('minutes')
                    ->label(__('meeting.minutes'))
                    ->limit(50),
                TextColumn::make('recordedBy.full_name')
                    ->label(__('meeting.recorded_by')),
            ])
            ->defaultSort('meeting_date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label(__('meeting.meeting_type'))
                    ->options([
                        MeetingType::GeneralAssembly->value => __('meeting.type.general_assembly'),
                        MeetingType::Executive->value => __('meeting.type.executive'),
                        MeetingType::Trustee->value => __('meeting.type.trustee'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeetings::route('/'),
            'create' => Pages\CreateMeeting::route('/create'),
            'edit' => Pages\EditMeeting::route('/{record}/edit'),
        ];
    }
}
