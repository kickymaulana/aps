<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = today();
        $monthStart = $today->copy()->startOfMonth();

        $activeEmployees = Employee::query()
            ->where(fn ($query) => $query
                ->whereNull('DateOut')
                ->orWhereDate('DateOut', '>=', $today))
            ->count();

        return [
            Stat::make('Total Pegawai', Employee::query()->count())
                ->icon(Heroicon::OutlinedUsers)
                ->url(EmployeeResource::getUrl('index')),
            Stat::make('Pegawai Aktif', $activeEmployees)
                ->description('Belum keluar atau tanggal keluar belum lewat')
                ->icon(Heroicon::OutlinedUserGroup),
            Stat::make('Masuk Bulan Ini', Employee::query()->whereBetween('DateIn', [$monthStart, $today])->count())
                ->icon(Heroicon::OutlinedUserPlus),
        ];
    }
}
