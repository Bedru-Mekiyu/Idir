<?php

namespace App\Filament\Resources;

use App\Enums\ApprovalDecision;
use App\Enums\ClaimStatus;
use App\Enums\NotificationType;
use App\Filament\Resources\ClaimResource\Pages;
use App\Jobs\SendNotificationJob;
use App\Models\Claim;
use App\Models\ClaimApproval;
use App\Models\Member;
use App\Services\LedgerService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClaimResource extends Resource
{
    protected static ?string $model = Claim::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'የገንዘብና ሒሳብ መዝገብ';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('claim.claim');
    }

    public static function getPluralModelLabel(): string
    {
        return __('claim.claims');
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
                Select::make('member_id')
                    ->label(__('claim.claim'))
                    ->relationship('member', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rules([
                        function () {
                            return function ($attribute, $value, $fail) {
                                $member = Member::find($value);
                                if ($member && ! app(LedgerService::class)->isVested($member)) {
                                    $fail(__('member.vesting_not_met').' (አባል የብቃት ጊዜውን አላሟላም)');
                                }
                            };
                        },
                    ]),
                Select::make('payout_trigger_type_id')
                    ->label(__('claim.trigger_type'))
                    ->relationship('triggerType', 'label_am')
                    ->preload()
                    ->required(),
                TextInput::make('requested_amount')
                    ->label(__('claim.requested_amount'))
                    ->numeric()
                    ->prefix('ብር')
                    ->helperText('ካልተሞላ በደንቡ የተቀመጠው ነባሪ መጠን ይታያል (Optional: falls back to trigger default)')
                    ->nullable(),
                Textarea::make('description')
                    ->label(__('claim.description'))
                    ->required(),
                FileUpload::make('document_path')
                    ->label(__('claim.supporting_document'))
                    ->directory('claims/documents')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('common.date'))
                    ->dateTime('M d, Y')
                    ->sortable(),
                TextColumn::make('member.full_name')
                    ->label(__('member.member'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('triggerType.label_am')
                    ->label(__('claim.trigger_type'))
                    ->badge(),
                TextColumn::make('requested_amount')
                    ->label(__('claim.requested_amount'))
                    // Deterministic currency formatting that does not require the intl extension.
                    ->formatStateUsing(fn ($state): string => 'ETB '.number_format((float) $state, 2))
                    ->placeholder(fn (Claim $record) => $record->triggerType?->default_payout_amount ? number_format($record->triggerType->default_payout_amount, 2).' ብር (ነባሪ)' : 'አልተወሰነም'),
                TextColumn::make('status')
                    ->label(__('claim.status.label'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ClaimStatus ? $state->label() : ($state ? ClaimStatus::tryFrom($state)?->label() ?? $state : ''))
                    ->color(fn ($state) => $state instanceof ClaimStatus ? $state->color() : ($state ? ClaimStatus::tryFrom($state)?->color() ?? 'gray' : 'gray')),
                TextColumn::make('approvals_count')
                    ->label(__('claim.approval_count', ['count' => '', 'required' => '']))
                    ->counts('approvals')
                    ->badge()
                    ->color('primary'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('claim.status.label'))
                    ->options([
                        ClaimStatus::Pending->value => __('claim.status.pending'),
                        ClaimStatus::UnderReview->value => __('claim.status.under_review'),
                        ClaimStatus::Approved->value => __('claim.status.approved'),
                        ClaimStatus::Rejected->value => __('claim.status.rejected'),
                        ClaimStatus::Paid->value => __('claim.status.paid'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                // APPROVAL ACTION
                Action::make('approve')
                    ->label(__('claim.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form(function (Claim $record) {
                        $proposed = $record->getProposedAmount();

                        return [
                            TextInput::make('approved_amount')
                                ->label(__('claim.proposed_amount'))
                                ->numeric()
                                ->prefix('ብር')
                                ->default($proposed)
                                ->required($proposed === null)
                                ->helperText('መጠኑን ከቀየሩ ምክንያት ማስገባት ግዴታ ነው (Override amount requires explanation below)'),
                            Textarea::make('remarks')
                                ->label(__('claim.remarks'))
                                ->placeholder('አስተያየት ካለዎት እዚህ ይጻፉ...'),
                        ];
                    })
                    ->action(function (Claim $record, array $data) {
                        $user = auth()->user();
                        $member = $user->member ?? Member::where('user_id', $user->id)->first();

                        if (! $member) {
                            Notification::make()->title('የኮሚቴ አባል መሆን አለብዎት')->danger()->send();

                            return;
                        }

                        // Duplicate check
                        if ($record->approvals()->where('approver_member_id', $member->id)->exists()) {
                            Notification::make()->title(__('claim.already_approved'))->warning()->send();

                            return;
                        }

                        $proposed = $record->getProposedAmount();
                        $finalAmount = $data['approved_amount'] ?? $proposed;

                        // Record approval
                        ClaimApproval::create([
                            'claim_id' => $record->id,
                            'approver_member_id' => $member->id,
                            'decision' => ApprovalDecision::Approved,
                            'approved_amount' => $finalAmount,
                            'remarks' => $data['remarks'] ?? null,
                            'decided_at' => now(),
                        ]);

                        $requiredApprovals = $record->idir->settings->required_approvals ?? 2;
                        $approvedCount = $record->approvals()->where('decision', ApprovalDecision::Approved->value)->count();

                        if ($approvedCount >= $requiredApprovals) {
                            /**
                             * Fix #3 Business Rule:
                             * The final disbursements.amount is the amount from the LAST (deciding) approval —
                             * whichever approval crosses the required-approvals threshold sets the paid amount.
                             */
                            $record->update(['status' => ClaimStatus::Approved]);

                            app(LedgerService::class)->recordDisbursement($record, [
                                'amount' => $finalAmount,
                                'method' => 'cash',
                                'recorded_by_member_id' => $member->id,
                                'notes' => "በኮሚቴ ሙሉ ፈቃድ የተፈጸመ ክፍያ (የመጨረሻ ፈቃድ ሰጪ፦ {$member->full_name})",
                            ]);

                            // Notify the filing member: claim approved + disbursement made.
                            SendNotificationJob::dispatch(
                                $record->idir_id,
                                $record->member_id,
                                NotificationType::ClaimApproved,
                                ['amount' => $finalAmount],
                            );

                            SendNotificationJob::dispatch(
                                $record->idir_id,
                                $record->member_id,
                                NotificationType::DisbursementMade,
                                ['amount' => $finalAmount],
                            );

                            Notification::make()
                                ->title('ጥያቄው ፀድቆ ክፍያ ተመዝግቧል!')
                                ->success()
                                ->send();
                        } else {
                            $record->update(['status' => ClaimStatus::UnderReview]);
                            Notification::make()
                                ->title("ፈቃድ ተመዝግቧል ({$approvedCount}/{$requiredApprovals})")
                                ->info()
                                ->send();
                        }
                    })
                    ->visible(fn (Claim $record) => in_array($record->status, [ClaimStatus::Pending, ClaimStatus::UnderReview])),

                // REJECTION ACTION (Fix #4)
                Action::make('reject')
                    ->label(__('claim.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('remarks')
                            ->label(__('claim.remarks'))
                            ->placeholder(__('claim.remarks_required_for_rejection'))
                            ->required(), // Fix #4: remarks is required for rejection
                    ])
                    ->action(function (Claim $record, array $data) {
                        $user = auth()->user();
                        $member = $user->member ?? Member::where('user_id', $user->id)->first();

                        if (! $member) {
                            Notification::make()->title('የኮሚቴ አባል መሆን አለብዎት')->danger()->send();

                            return;
                        }

                        /**
                         * Fix #4 Rejection rule:
                         * A single rejection from any one committee approver immediately sets the claim
                         * to ClaimStatus::Rejected and stops further approvals.
                         * A rejected claim cannot be re-approved.
                         */
                        ClaimApproval::create([
                            'claim_id' => $record->id,
                            'approver_member_id' => $member->id,
                            'decision' => ApprovalDecision::Rejected,
                            'remarks' => $data['remarks'],
                            'decided_at' => now(),
                        ]);

                        $record->update(['status' => ClaimStatus::Rejected]);

                        // Notify the filing member that their claim was rejected, with the reason.
                        SendNotificationJob::dispatch(
                            $record->idir_id,
                            $record->member_id,
                            NotificationType::ClaimRejected,
                            ['reason' => $data['remarks']],
                        );

                        Notification::make()
                            ->title('ጥያቄው ውድቅ ተደርጓል')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (Claim $record) => in_array($record->status, [ClaimStatus::Pending, ClaimStatus::UnderReview])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClaims::route('/'),
            'create' => Pages\CreateClaim::route('/create'),
        ];
    }
}
