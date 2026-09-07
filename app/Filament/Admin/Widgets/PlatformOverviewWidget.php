<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ClaimStatus;
use App\Enums\MemberStatus;
use App\Models\AccessRequest;
use App\Models\Claim;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalIdirs = Idir::count();
        $activeIdirs = Idir::where('status', 'active')->count();
        $pendingIdirs = Idir::where('status', 'pending_approval')->count();
        $totalMembers = Member::where('status', '!=', MemberStatus::Excluded)->count();
        $totalAggregateFunds = (float) IdirSetting::sum('fund_balance');
        $totalPendingClaims = Claim::whereIn('status', [ClaimStatus::Pending, ClaimStatus::UnderReview])->count();

        $pendingAccessRequests = AccessRequest::where('status', 'pending')->count();

        return [
            Stat::make('ጠቅላላ እድሮች (Total Idirs)', "{$totalIdirs} ማህበራት")
                ->description("{$activeIdirs} ንቁ | {$pendingIdirs} በማረጋገጥ ላይ")
                ->descriptionIcon('heroicon-m-building-library')
                ->color('primary'),

            Stat::make('የማኔጀርነት ጥያቄዎች (Access Requests)', "{$pendingAccessRequests} ጥያቄዎች")
                ->description($pendingAccessRequests > 0 ? 'የባለቤት ውሳኔ የሚጠብቁ' : 'ውሳኔ የሚጠብቅ የለም')
                ->descriptionIcon('heroicon-m-key')
                ->color($pendingAccessRequests > 0 ? 'warning' : 'gray'),

            Stat::make('በማረጋገጥ ላይ ያሉ እድሮች (Pending Idirs)', "{$pendingIdirs} እድሮች")
                ->description($pendingIdirs > 0 ? 'የባለቤት ውሳኔ የሚጠብቁ' : 'ውሳኔ የሚጠብቅ የለም')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingIdirs > 0 ? 'warning' : 'gray'),

            Stat::make('ጠቅላላ አባላት (Total Platform Members)', "{$totalMembers} አባላት")
                ->description('በሁሉም እድሮች የተመዘገቡ')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('ጠቅላላ ድምር ፈንድ (Aggregate Funds)', number_format($totalAggregateFunds, 2).' ብር')
                ->description('የሁሉም እድሮች የጋራ ድምር')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
