<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Fine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HRStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return [
                Stat::make('Total Employees', User::where('role', '!=', 'ADMIN')->count())
                    ->description('Active Staff')
                    ->descriptionIcon('heroicon-m-users')
                    ->chart([7, 3, 10, 3, 15, 4, 17])
                    ->color('primary'),

                Stat::make('Present Today', Attendance::whereDate('date', Carbon::today())->count())
                    ->description(Carbon::today()->format('d M Y'))
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('success'),

                Stat::make('Pending Leaves', LeaveRequest::where('status', 'pending')->count())
                    ->description('Requires Approval')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('warning'),
                
                Stat::make('Fines (This Month)', 'Rp ' . number_format(
                    Fine::query()
                        ->whereYear('created_at', Carbon::now()->year)
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->sum('amount')
                ))
                    ->description('Total Deductions')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('danger'),
            ];
        }

        $attendanceToday = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        $statusLabel = $attendanceToday ? 'Present' : 'Not Clocked In';
        $statusColor = $attendanceToday ? 'success' : 'gray';

        return [
            Stat::make('My Days Worked', Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year)
                    ->count()
                )
                ->description('This Month')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('My Status Today', $statusLabel)
                ->description(Carbon::today()->format('d M Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($statusColor),

            Stat::make('My Pending Leaves', LeaveRequest::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->count()
                )
                ->description('Awaiting Approval')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            
            Stat::make('My Fines (This Month)', 'Rp ' . number_format(
                Fine::query()
                    ->where('user_id', $user->id)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('amount')
            ))
                ->description('Your Deductions')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
        ];
    }
}