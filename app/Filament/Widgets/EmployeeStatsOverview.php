<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

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

        $activeLoans = DB::table('loans')
            ->where('LoanLeft', '>', 0)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(LoanLeft), 0) as total')
            ->first();

        return [
            Stat::make('Total Pegawai', Employee::query()->count())
                ->icon(Heroicon::OutlinedUsers)
                ->url(EmployeeResource::getUrl('index')),
            Stat::make('Pegawai Aktif', $activeEmployees)
                ->description('Belum keluar atau tanggal keluar belum lewat')
                ->icon(Heroicon::OutlinedUserGroup),
            Stat::make('Masuk Bulan Ini', Employee::query()->whereBetween('DateIn', [$monthStart, $today])->count())
                ->icon(Heroicon::OutlinedUserPlus),
            Stat::make('Cuti Aktif', DB::table('employeeleaves')
                ->where('StartDate', '<=', $today)
                ->where(fn ($query) => $query->whereNull('ExpiredDate')->orWhereDate('ExpiredDate', '>=', $today))
                ->count())
                ->icon(Heroicon::OutlinedCalendarDays),
            Stat::make('Pinjaman Aktif', $activeLoans->count)
                ->icon(Heroicon::OutlinedBanknotes),
            Stat::make('Sisa Pinjaman', 'Rp'.number_format($activeLoans->total, 0, ',', '.'))
                ->icon(Heroicon::OutlinedCurrencyDollar),
            Stat::make('Absensi Hari Ini', DB::table('absences')->whereDate('AbsenceDate', $today)->count())
                ->icon(Heroicon::OutlinedClock),
            Stat::make('Surat Peringatan Aktif', DB::table('warningletters')->whereDate('ExpiredDate', '>=', $today)->count())
                ->icon(Heroicon::OutlinedExclamationTriangle),
        ];
    }
}
