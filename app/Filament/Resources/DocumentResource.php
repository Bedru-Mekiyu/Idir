<?php

namespace App\Filament\Resources;

use App\Enums\DocumentCategory;
use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|\UnitEnum|null $navigationGroup = 'የኮሚቴ አስተዳደር';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('document.document');
    }

    public static function getPluralModelLabel(): string
    {
        return __('document.documents');
    }

    public static function canViewAny(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant && $tenant->isActive();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('document.title'))
                    ->required()
                    ->maxLength(255),
                Select::make('category')
                    ->label(__('document.category'))
                    ->options([
                        DocumentCategory::Bylaws->value => __('document.categories.bylaws'),
                        DocumentCategory::Receipt->value => __('document.categories.receipt'),
                        DocumentCategory::Correspondence->value => __('document.categories.correspondence'),
                        DocumentCategory::Other->value => __('document.categories.other'),
                    ])
                    ->default(DocumentCategory::Other->value)
                    ->required(),
                FileUpload::make('file_path')
                    ->label(__('document.upload_document'))
                    ->directory('idir/documents')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('document.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label(__('document.category'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof DocumentCategory ? $state->label() : ($state ? DocumentCategory::tryFrom($state)?->label() ?? $state : '')),
                TextColumn::make('uploadedBy.full_name')
                    ->label(__('document.uploaded_by')),
                IconColumn::make('deletion_requested')
                    ->label(__('document.deletion_requested'))
                    ->boolean()
                    ->color(fn ($state) => $state ? 'danger' : 'gray'),
                TextColumn::make('created_at')
                    ->label(__('common.date'))
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(__('document.category'))
                    ->options([
                        DocumentCategory::Bylaws->value => __('document.categories.bylaws'),
                        DocumentCategory::Receipt->value => __('document.categories.receipt'),
                        DocumentCategory::Correspondence->value => __('document.categories.correspondence'),
                        DocumentCategory::Other->value => __('document.categories.other'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('download')
                    ->label('አውርድ (Download)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),
                Action::make('request_deletion')
                    ->label(__('document.request_deletion'))
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Document $record) {
                        $record->update(['deletion_requested' => true]);
                        Notification::make()
                            ->title(__('document.deletion_requested'))
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Document $record) => ! $record->deletion_requested),
                Action::make('confirm_deletion')
                    ->label(__('document.confirm_deletion'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Document $record) {
                        $user = auth()->user();
                        $member = $user->member ?? Member::where('user_id', $user->id)->first();

                        if ($member && $record->uploaded_by_member_id === $member->id) {
                            Notification::make()
                                ->title(__('document.different_member_required'))
                                ->danger()
                                ->send();

                            return;
                        }

                        Storage::delete($record->file_path);
                        $record->delete();

                        Notification::make()
                            ->title('ሰነዱ ተሰርዟል')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Document $record) => $record->deletion_requested),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
        ];
    }
}
