<?php

namespace App\Filament\Worker\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $workerId = auth()->id();

        $pendingOrders = Order::where('worker_id', $workerId)
            ->whereIn('status', [
                OrderStatus::PaymentSubmitted,
                OrderStatus::PaymentReceived,
                OrderStatus::InProgress,
            ])
            ->count();

        $completedOrders = Order::where('worker_id', $workerId)
            ->where('status', OrderStatus::Completed)
            ->count();

        $totalEarnings = Order::where('worker_id', $workerId)
            ->where('status', OrderStatus::Completed)
            ->sum('total_price');

        $thisMonthEarnings = Order::where('worker_id', $workerId)
            ->where('status', OrderStatus::Completed)
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('total_price');

        return [
            Stat::make('Pending Orders', $pendingOrders)
                ->description('Orders awaiting action')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Completed Orders', $completedOrders)
                ->description('All time')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Earnings', 'PHP ' . number_format($totalEarnings, 2))
                ->description('All time')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('This Month', 'PHP ' . number_format($thisMonthEarnings, 2))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
}
