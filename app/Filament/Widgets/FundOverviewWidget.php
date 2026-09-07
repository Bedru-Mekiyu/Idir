<?php

namespace App\Filament\Widgets;

use App\Enums\ClaimStatus;
use App\Enums\MemberStatus;
use App\Models\Claim;
use App\Models\Contribution;
use App\Models\Disbursement;
use App\Models\Member;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FundOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        $idirId = $tenant?->id;

        if (! $idirId) {
            return [];
        }

        $totalContributions = (float) Contribution::where('idir_id', $idirId)
            ->where(function ($q) {
                $q->whereNull('chapa_status')
                    ->orWhere('chapa_status', 'verified');
            })
            ->sum('amount');

        $totalDisbursements = (float) Disbursement::where('idir_id', $idirId)->sum('amount');
        $fundBalance = $totalContributions - $totalDisbursements;

        $activeCount = Member::where('idir_id', $idirId)->where('status', MemberStatus::Active)->count();
        $arrearsCount = Member::where('idir_id', $idirId)->where('status', MemberStatus::InArrears)->count();
        $pendingClaimsCount = Claim::where('idir_id', $idirId)->whereIn('status', [ClaimStatus::Pending, ClaimStatus::UnderReview])->count();

        return [
            Stat::make(__('idir.fund_balance'), number_format($fundBalance, 2).' ብር')
                ->description('የእድሩ አጠቃላይ የተጣራ ቀሪ ገንዘብ')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($fundBalance >= 0 ? 'success' : 'danger'),

            Stat::make('ንቁ አባላት (Active Members)', $activeCount)
                ->description("ያልከፈሉ አባላት፦ {$arrearsCount}")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('በመጠባበቅ ላይ ያሉ ጥያቄዎች', $pendingClaimsCount)
                ->description('የኮሚቴ ፈቃድ የሚጠብቁ')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($pendingClaimsCount > 0 ? 'warning' : 'gray'),
        ];
    }
}
