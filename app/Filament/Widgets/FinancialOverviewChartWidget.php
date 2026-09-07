<?php

namespace App\Filament\Widgets;

use App\Enums\ChapaStatus;
use App\Models\Contribution;
use App\Models\Disbursement;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class FinancialOverviewChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'የወርሃዊ ገቢና ወጪ የፋይናንስ እንቅስቃሴ (Monthly Income vs Disbursements)';
    }

    protected function getData(): array
    {
        $tenant = Filament::getTenant();
        $idirId = $tenant?->id;

        if (! $idirId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $labels = [];
        $incomeData = [];
        $expenseData = [];

        // Last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $labels[] = $month->format('M Y');

            // Income (Contributions)
            $income = Contribution::where('idir_id', $idirId)
                ->where('period_covered', $monthKey)
                ->where('amount', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('chapa_status')
                        ->orWhere('chapa_status', ChapaStatus::Verified->value);
                })
                ->sum('amount');
            $incomeData[] = (float) $income;

            // Expense (Disbursements)
            $expense = Disbursement::where('idir_id', $idirId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            $expenseData[] = (float) $expense;
        }

        return [
            'datasets' => [
                [
                    'label' => 'የተሰበሰበ መዋጮ / ገቢ (Income in ETB)',
                    'data' => $incomeData,
                    'backgroundColor' => 'rgba(161, 161, 170, 0.85)', // Indigo
                    'borderColor' => '#a1a1aa',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'የተፈጸመ ካሳ / ወጪ (Disbursements in ETB)',
                    'data' => $expenseData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.85)', // Red
                    'borderColor' => '#dc2626',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
