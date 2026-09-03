<?php

namespace App\Filament\Resources;

use App\Enums\CommitteeRole;
use App\Enums\MemberStatus;
use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static string | \UnitEnum | null $navigationGroup = 'የአባላት አስተዳደር';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('member.member');
    }

    public static function getPluralModelLabel(): string
    {
        return __('member.members');
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
                TextInput::make('full_name')
                    ->label(__('member.full_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label(__('member.phone_number'))
                    ->tel()
                    ->required()
                    ->maxLength(20),
                DatePicker::make('join_date')
                    ->label(__('member.join_date'))
                    ->default(now())
                    ->required(),
                Select::make('status')
                    ->label(__('member.status.label'))
                    ->options([
                        MemberStatus::Active->value => __('member.status.active'),
                        MemberStatus::InArrears->value => __('member.status.in_arrears'),
                        MemberStatus::Excluded->value => __('member.status.excluded'),
                        MemberStatus::Inactive->value => __('member.status.inactive'),
                    ])
                    ->default(MemberStatus::Active->value)
                    ->required(),
                Select::make('committee_role')
                    ->label(__('member.role.label'))
                    ->options([
                        '' => __('member.role.member'),
                        CommitteeRole::Chair->value => __('member.role.chair'),
                        CommitteeRole::Secretary->value => __('member.role.secretary'),
                        CommitteeRole::Treasurer->value => __('member.role.treasurer'),
                    ])
                    ->disabled(fn () => !auth()->user()?->member?->isChair())
                    ->helperText(fn () => auth()->user()?->member?->isChair() ? null : 'የስራ ድርሻ መቀየር የሚችለው የእድሩ ሰብሳቢ ብቻ ነው።')
                    ->nullable(),
                TextInput::make('fayda_id')
                    ->label('Fayda ID (ፋይዳ ቁጥር)')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('member.full_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('member.phone_number'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('member.status.label'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof MemberStatus ? $state->label() : ($state ? MemberStatus::tryFrom($state)?->label() ?? $state : ''))
                    ->color(fn ($state) => $state instanceof MemberStatus ? $state->color() : ($state ? MemberStatus::tryFrom($state)?->color() ?? 'gray' : 'gray')),
                TextColumn::make('committee_role')
                    ->label(__('member.role.label'))
                    ->formatStateUsing(fn ($state) => $state instanceof CommitteeRole ? $state->label() : ($state ? CommitteeRole::tryFrom($state)?->label() ?? $state : __('member.role.member')))
                    ->badge()
                    ->color(fn ($state) => match ($state instanceof CommitteeRole ? $state->value : $state) {
                        'chair' => 'danger',
                        'secretary' => 'warning',
                        'treasurer' => 'info',
                        default => 'secondary',
                    })
                    ->placeholder(__('member.role.member')),
                IconColumn::make('fayda_verified')
                    ->label(__('member.fayda_verified'))
                    ->boolean(),
                TextColumn::make('join_date')
                    ->label(__('member.join_date'))
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('member.status.label'))
                    ->options([
                        MemberStatus::Active->value => __('member.status.active'),
                        MemberStatus::InArrears->value => __('member.status.in_arrears'),
                        MemberStatus::Excluded->value => __('member.status.excluded'),
                        MemberStatus::Inactive->value => __('member.status.inactive'),
                    ]),
                SelectFilter::make('committee_role')
                    ->label(__('member.role.label'))
                    ->options([
                        CommitteeRole::Chair->value => __('member.role.chair'),
                        CommitteeRole::Secretary->value => __('member.role.secretary'),
                        CommitteeRole::Treasurer->value => __('member.role.treasurer'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),

                // CHANGE ROLE ACTION (CHAIR ONLY)
                Action::make('change_role')
                    ->label(__('member.change_role'))
                    ->icon('heroicon-o-identification')
                    ->color('info')
                    ->modalHeading(__('member.change_role'))
                    ->modalDescription('ለዚህ አባል የሚሰጠውን የስራ ድርሻ ይምረጡ።')
                    ->form([
                        Select::make('committee_role')
                            ->label(__('member.role.label'))
                            ->options([
                                '' => __('member.role.member'),
                                CommitteeRole::Secretary->value => __('member.role.secretary'),
                                CommitteeRole::Treasurer->value => __('member.role.treasurer'),
                                CommitteeRole::Chair->value => __('member.role.chair'),
                            ])
                            ->default(fn (Member $record) => $record->committee_role?->value ?? '')
                            ->selectablePlaceholder(false),
                    ])
                    ->action(function (Member $record, array $data) {
                        $newRole = !empty($data['committee_role']) ? CommitteeRole::tryFrom($data['committee_role']) : null;

                        // Block demoting the last chair of the idir
                        if ($record->isChair() && $newRole !== CommitteeRole::Chair) {
                            $otherChairsCount = Member::where('idir_id', $record->idir_id)
                                ->where('committee_role', CommitteeRole::Chair)
                                ->where('id', '!=', $record->id)
                                ->count();

                            if ($otherChairsCount === 0) {
                                Notification::make()
                                    ->title(__('member.role_change_blocked_last_chair'))
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }

                        $record->update(['committee_role' => $newRole]);

                        // Ensure user is attached to tenant if promoted to committee
                        if ($newRole && $record->user_id) {
                            $record->idir->users()->syncWithoutDetaching([$record->user_id]);
                        }

                        Notification::make()
                            ->title(__('member.role_changed_success'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Member $record) => (bool) auth()->user()?->member?->isChair() && $record->status !== MemberStatus::Excluded && $record->status !== MemberStatus::Inactive),

                // CHAIR HANDOFF ACTION (CHAIR ONLY)
                Action::make('handover_chair')
                    ->label(__('member.handover_chair'))
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->modalHeading(__('member.handover_chair'))
                    ->modalDescription(fn (Member $record) => "ይህ እርምጃ ሙሉ የሰብሳቢነት ሥልጣንዎን ለ {$record->full_name} ያስረክባል። እርስዎ ወደመረጡት አዲስ ሚና ይቀየራሉ።")
                    ->form([
                        Select::make('outgoing_role')
                            ->label(__('member.outgoing_chair_role'))
                            ->options([
                                'secretary' => __('member.role.secretary'),
                                'treasurer' => __('member.role.treasurer'),
                                'member' => __('member.role.member'),
                            ])
                            ->default('member')
                            ->required(),
                        \Filament\Forms\Components\Checkbox::make('confirm_handover')
                            ->label(__('member.handover_confirmation'))
                            ->required()
                            ->rules(['accepted']),
                    ])
                    ->action(function (Member $record, array $data) {
                        $currentChair = auth()->user()?->member;
                        if (!$currentChair || !$currentChair->isChair() || $currentChair->idir_id !== $record->idir_id) {
                            Notification::make()
                                ->title('ይህንን እርምጃ ለመፈጸም ፈቃድ የለዎትም።')
                                ->danger()
                                ->send();
                            return;
                        }

                        // 1. Promote target member to Chair
                        $record->update([
                            'committee_role' => CommitteeRole::Chair,
                            'status' => MemberStatus::Active,
                        ]);

                        if ($record->user_id) {
                            $record->idir->users()->syncWithoutDetaching([$record->user_id]);
                        }

                        // 2. Assign outgoing chair's new role
                        $newRole = $data['outgoing_role'] === 'member' ? null : CommitteeRole::tryFrom($data['outgoing_role']);
                        $currentChair->update([
                            'committee_role' => $newRole,
                        ]);

                        Notification::make()
                            ->title(__('member.handover_success'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Member $record) => (bool) auth()->user()?->member?->isChair() && $record->id !== auth()->user()?->member?->id && $record->status === MemberStatus::Active),

                // DEACTIVATE MEMBER ACTION (CHAIR ONLY - VOLUNTARY / NON-DUES DEPARTURE)
                Action::make('deactivate')
                    ->label(__('member.deactivate_member'))
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->modalHeading(__('member.deactivate_member'))
                    ->modalDescription('አባሉ ከአባልነት ይሰናበታል፤ ነገር ግን የቀደመ የክፍያና የካሳ ታሪካቸው በሲስተሙ ሳይደለዝ ተጠብቆ ይቆያል።')
                    ->form([
                        TextInput::make('reason')
                            ->label(__('member.deactivate_reason'))
                            ->placeholder('ለምሳሌ፡ ወደ ሌላ ከተማ በመዛወራቸው...')
                            ->required(),
                    ])
                    ->action(function (Member $record, array $data) {
                        // Protect last chair
                        if ($record->isChair()) {
                            $otherChairsCount = Member::where('idir_id', $record->idir_id)
                                ->where('committee_role', CommitteeRole::Chair)
                                ->where('id', '!=', $record->id)
                                ->count();

                            if ($otherChairsCount === 0) {
                                Notification::make()
                                    ->title(__('member.last_chair_protection'))
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }

                        $record->update([
                            'status' => MemberStatus::Inactive,
                            'committee_role' => null,
                            'exclusion_reason' => $data['reason'] ?? 'በፈቃዳቸው የተሰናበቱ',
                            'excluded_at' => now(),
                        ]);

                        Notification::make()
                            ->title(__('member.deactivate_success'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Member $record) => (bool) auth()->user()?->member?->isChair() && $record->status !== MemberStatus::Inactive && $record->status !== MemberStatus::Excluded),

                // REACTIVATE INACTIVE MEMBER ACTION
                Action::make('reactivate')
                    ->label(__('member.reactivate_member'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('member.reactivate_member'))
                    ->modalDescription('ይህ አባል እንደገና ወደ ንቁ አባልነት ይመለሳል።')
                    ->action(function (Member $record) {
                        $record->update([
                            'status' => MemberStatus::Active,
                            'exclusion_reason' => null,
                            'excluded_at' => null,
                        ]);

                        Notification::make()
                            ->title(__('member.reactivate_success'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Member $record) => $record->status === MemberStatus::Inactive),

                // SEND WARNING (FOR IN-ARREARS MEMBERS)
                Action::make('send_warning')
                    ->label(__('member.send_warning'))
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Member $record) {
                        $record->update(['exclusion_warning_sent_at' => now()]);
                        Notification::make()
                            ->title(__('member.exclusion_warning_sent'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Member $record) => $record->status === MemberStatus::InArrears),

                // EXCLUDE ACTION (FOR IN-ARREARS MEMBERS AFTER WARNING)
                Action::make('exclude')
                    ->label(__('member.exclude_member'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('exclusion_reason')
                            ->label(__('member.exclusion_reason'))
                            ->required(),
                    ])
                    ->action(function (Member $record, array $data) {
                        if (is_null($record->exclusion_warning_sent_at)) {
                            Notification::make()
                                ->title(__('member.warning_required_first'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update([
                            'status' => MemberStatus::Excluded,
                            'committee_role' => null,
                            'exclusion_reason' => $data['exclusion_reason'],
                            'excluded_at' => now(),
                        ]);

                        Notification::make()
                            ->title(__('member.member_excluded'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Member $record) => $record->status === MemberStatus::InArrears && !is_null($record->exclusion_warning_sent_at)),
            ])
            ->bulkActions([
                // Physical mass deletion is removed to guarantee financial and historical ledger integrity
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
