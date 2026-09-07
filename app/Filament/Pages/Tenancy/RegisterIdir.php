<?php

namespace App\Filament\Pages\Tenancy;

use App\Enums\CommitteeRole;
use App\Enums\MemberStatus;
use App\Models\ContributionRule;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\PayoutTriggerType;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class RegisterIdir extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('idir.create_idir');
    }

    public function mount(): void
    {
        parent::mount();

        if (auth()->check()) {
            $user = auth()->user();

            if (! $user->isPhoneVerified()) {
                redirect()->route('phone.verify')->send();

                return;
            }

            if (! $user->canCreateIdir()) {
                session()->flash('warning', 'እድር ከመመዝገብዎ በፊት የፕላትፎርም ባለቤቱ ፈቃድ ያስፈልጋል። ጥያቄዎ በግምገማ ላይ ነው።');
                redirect()->route('access-request')->send();

                return;
            }
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make(__('idir.basic_info'))
                        ->schema([
                            TextInput::make('name')
                                ->label(__('idir.idir_name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('membership_basis')
                                ->label(__('idir.membership_basis'))
                                ->placeholder(__('idir.membership_basis_help'))
                                ->maxLength(255),
                            TextInput::make('region')
                                ->label(__('idir.region'))
                                ->maxLength(255),
                            TextInput::make('sub_city')
                                ->label(__('idir.sub_city'))
                                ->maxLength(255),
                            TextInput::make('woreda')
                                ->label(__('idir.woreda'))
                                ->maxLength(255),
                        ]),

                    Wizard\Step::make(__('idir.dues_config'))
                        ->schema([
                            TextInput::make('dues_amount')
                                ->label(__('idir.dues_amount'))
                                ->numeric()
                                ->prefix('ብር')
                                ->default(config('idir.defaults.dues_amount', 200.00))
                                ->required(),
                            Select::make('dues_frequency')
                                ->label(__('idir.dues_frequency'))
                                ->options([
                                    'weekly' => __('idir.frequency.weekly'),
                                    'monthly' => __('idir.frequency.monthly'),
                                    'quarterly' => __('idir.frequency.quarterly'),
                                    'annually' => __('idir.frequency.annually'),
                                ])
                                ->default(config('idir.defaults.dues_frequency', 'monthly'))
                                ->required(),
                            TextInput::make('late_fee_amount')
                                ->label(__('idir.late_fee'))
                                ->numeric()
                                ->prefix('ብር')
                                ->default(config('idir.defaults.late_fee_amount', 50.00)),
                            TextInput::make('late_fee_grace_days')
                                ->label(__('idir.grace_period'))
                                ->numeric()
                                ->default(config('idir.defaults.late_fee_grace_days', 7)),
                        ]),

                    Wizard\Step::make(__('idir.payout_config'))
                        ->schema([
                            TextInput::make('vesting_period_days')
                                ->label(__('idir.vesting_period'))
                                ->numeric()
                                ->default(config('idir.defaults.vesting_period_days', 90))
                                ->required(),
                            TextInput::make('required_approvals')
                                ->label(__('idir.required_approvals'))
                                ->numeric()
                                ->minValue(1)
                                ->default(config('idir.defaults.required_approvals', 2))
                                ->required(),
                            CheckboxList::make('payout_triggers')
                                ->label(__('idir.payout_triggers'))
                                ->options([
                                    'death' => 'ሞት (Death)',
                                    'wedding' => 'ሰርግ (Wedding)',
                                    'emergency' => 'ድንገተኛ አደጋ (Emergency)',
                                ])
                                ->default(['death', 'emergency'])
                                ->columns(1),
                        ]),
                ])
                    ->submitAction(new HtmlString('<button type="submit" wire:loading.attr="disabled" class="fi-btn fi-btn-size-md fi-btn-color-primary px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow transition">እድር መዝግብ (Register Idir)</button>'))
                    ->columnSpanFull(),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $user = auth()->user();

            // 1. Create the Idir tenant record with pending_approval status
            $idir = Idir::create([
                'name' => $data['name'],
                'membership_basis' => $data['membership_basis'] ?? null,
                'region' => $data['region'] ?? null,
                'sub_city' => $data['sub_city'] ?? null,
                'woreda' => $data['woreda'] ?? null,
                'locale' => 'am',
                'status' => 'pending_approval',
            ]);

            // 2. Attach current user as tenant user
            $idir->users()->attach($user);

            // 3. Create IdirSetting
            $triggers = $data['payout_triggers'] ?? ['death', 'emergency'];
            IdirSetting::create([
                'idir_id' => $idir->id,
                'dues_amount' => $data['dues_amount'] ?? 200.00,
                'dues_frequency' => $data['dues_frequency'] ?? 'monthly',
                'late_fee_amount' => $data['late_fee_amount'] ?? 50.00,
                'late_fee_grace_days' => $data['late_fee_grace_days'] ?? 7,
                'vesting_period_days' => $data['vesting_period_days'] ?? 90,
                'required_approvals' => $data['required_approvals'] ?? 2,
                'enabled_payout_triggers' => $triggers,
                'fund_balance' => 0.00,
            ]);

            // 4. Create default ContributionRule
            ContributionRule::create([
                'idir_id' => $idir->id,
                'category_name' => 'መደበኛ (Standard)',
                'amount' => $data['dues_amount'] ?? 200.00,
                'frequency' => $data['dues_frequency'] ?? 'monthly',
                'is_default' => true,
            ]);

            // 5. Seed default PayoutTriggerTypes
            $triggerLabels = [
                'death' => 'ሞት',
                'wedding' => 'ሰርግ',
                'emergency' => 'ድንገተኛ አደጋ',
            ];
            foreach ($triggers as $triggerName) {
                PayoutTriggerType::create([
                    'idir_id' => $idir->id,
                    'name' => $triggerName,
                    'label_am' => $triggerLabels[$triggerName] ?? $triggerName,
                    'default_payout_amount' => $triggerName === 'death' ? 5000.00 : 2000.00,
                    'is_active' => true,
                ]);
            }

            // 6. Create Member record for the founder as Chairperson
            Member::create([
                'idir_id' => $idir->id,
                'user_id' => $user->id,
                'full_name' => $user->name,
                'phone' => $user->phone ?? '0911000000',
                'join_date' => now()->toDateString(),
                'status' => MemberStatus::Active,
                'committee_role' => CommitteeRole::Chair,
            ]);

            Notification::make()
                ->title('የእድር ምዝገባ ጥያቄዎ ቀርቧል!')
                ->body("የ {$idir->name} ምዝገባ በፕላትፎርም አስተዳዳሪው እንዲጸድቅ ተልኳል። ውሳኔው ሲሰጥ በኤስኤምኤስ ይደርስዎታል።")
                ->warning()
                ->send();

            return $idir;
        });
    }
}
