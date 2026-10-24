<?php

namespace App\Filament\Widgets;

use App\Enums\MemberStatus;
use App\Models\Member;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OverdueMembersWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function getLabel(): string
    {
        return 'እዳ ያለባቸው አባላት';
    }

    public function table(Table $table): Table
    {
        $idir = Filament::getTenant();

        return $table
            ->query(
                Member::query()
                    ->where('idir_id', $idir?->id)
                    ->where('status', MemberStatus::Active)
                    ->where('arrears_amount', '>', 0)
                    ->orderByDesc('arrears_amount')
            )
            ->columns([
                TextColumn::make('full_name')
                    ->label('ስም')
                    ->searchable(),
                TextColumn::make('arrears_amount')
                    ->label('እዳ (ብር)')
                    ->formatStateUsing(fn ($state) => 'ብር '.number_format($state, 2)),
                TextColumn::make('phone')
                    ->label('ስልክ'),
                TextColumn::make('join_date')
                    ->label('ተቀላቅሏል')
                    ->date('Y-m-d'),
            ])
            ->emptyStateHeading('ምንም እዳ ያለባቸው አባላት የሉም')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
