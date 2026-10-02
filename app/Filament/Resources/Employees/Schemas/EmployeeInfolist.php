<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil Pegawai')
                    ->schema([
                        TextEntry::make('EmployeeID')->label('ID Pegawai'),
                        TextEntry::make('EmployeeName')->label('Nama'),
                        TextEntry::make('Address')->label('Alamat'),
                        TextEntry::make('Phone')->label('Telepon'),
                        TextEntry::make('EmailAddress')->label('Email'),
                        TextEntry::make('BirthPlace')->label('Tempat Lahir'),
                        TextEntry::make('BirthDate')->label('Tanggal Lahir')->date(),
                        TextEntry::make('DateIn')->label('Mulai Kerja')->date(),
                        TextEntry::make('DateOut')->label('Selesai Kerja')->date(),
                        TextEntry::make('Remarks')->label('Catatan')->columnSpanFull(),
                    ])->columns(2),
                Section::make('Riwayat Kepegawaian')
                    ->schema([
                        TextEntry::make('history_summary')
                            ->label('Riwayat status dan mutasi')
                            ->state(fn ($record): string => self::history($record->EmployeeNo))
                            ->columnSpanFull()
                            ->html(),
                    ]),
                Section::make('Absensi dan Cuti')
                    ->schema([
                        TextEntry::make('absence_summary')
                            ->label('Ringkasan')
                            ->state(fn ($record): string => self::absence($record->EmployeeNo))
                            ->columnSpanFull()
                            ->html(),
                    ]),
                Section::make('Payroll, Klaim, dan Pinjaman')
                    ->schema([
                        TextEntry::make('financial_summary')
                            ->label('Ringkasan')
                            ->state(fn ($record): string => self::financial($record->EmployeeNo))
                            ->columnSpanFull()
                            ->html(),
                    ]),
                Section::make('Pelatihan dan Peringatan')
                    ->schema([
                        TextEntry::make('development_summary')
                            ->label('Ringkasan')
                            ->state(fn ($record): string => self::development($record->EmployeeNo))
                            ->columnSpanFull()
                            ->html(),
                    ]),
            ]);
    }

    private static function history(int $employeeNo): string
    {
        $rows = DB::table('employeehistories')->where('EmployeeNo', $employeeNo)->orderByDesc('RefDate')->limit(100)->get();
        $status = DB::table('employeestatushistories')->where('EmployeeNo', $employeeNo)->orderByDesc('HistoryDate')->limit(100)->get();
        $transfers = DB::table('transfers')->where('EmployeeNo', $employeeNo)->orderByDesc('TransferDate')->limit(100)->get();
        $positions = DB::table('positiontransfers')->where('EmployeeNo', $employeeNo)->orderByDesc('PositionTransferDate')->limit(100)->get();

        return self::list($rows, 'RefDate', fn ($row) => $row->Description ?: 'Riwayat umum')
            .self::list($status, 'HistoryDate', fn ($row) => "Perubahan status: {$row->FromEmployeeStatusNo} ke {$row->ToEmployeeStatusNo}. {$row->Description}")
            .self::list($transfers, 'TransferDate', fn ($row) => "Mutasi organisasi. {$row->Description}")
            .self::list($positions, 'PositionTransferDate', fn ($row) => "Perubahan jabatan: {$row->FromPositionNo} ke {$row->ToPositionNo}. {$row->Description}");
    }

    private static function absence(int $employeeNo): string
    {
        $absence = DB::table('absences')->where('EmployeeNo', $employeeNo)->count();
        $leave = DB::table('employeeleaves')->where('EmployeeNo', $employeeNo)->count();

        return "Total catatan absensi: {$absence}<br>Total catatan cuti: {$leave}";
    }

    private static function financial(int $employeeNo): string
    {
        $payroll = DB::table('payroll')->where('EmployeeNo', $employeeNo)->orderByDesc('PayrollDate')->limit(20)->get();
        $claims = DB::table('employeeclaims')->where('EmployeeNo', $employeeNo)->orderByDesc('ClaimDate')->limit(20)->get();
        $loans = DB::table('loans')->where('EmployeeNo', $employeeNo)->orderByDesc('LoanDate')->limit(20)->get();

        return self::list($payroll, 'PayrollDate', fn ($row) => "Payroll {$row->PayrollID}: {$row->GrandTotal}")
            .self::list($claims, 'ClaimDate', fn ($row) => "Klaim: {$row->Amount}. {$row->Remarks}")
            .self::list($loans, 'LoanDate', fn ($row) => "Pinjaman: {$row->Amount}, sisa {$row->LoanLeft}. {$row->Remarks}");
    }

    private static function development(int $employeeNo): string
    {
        $training = DB::table('trainingdetails')->join('trainings', 'trainings.TrainingNo', '=', 'trainingdetails.TrainingNo')->where('trainingdetails.EmployeeNo', $employeeNo)->orderByDesc('TrainingDate')->limit(50)->get();
        $warnings = DB::table('warningletters')->where('EmployeeNo', $employeeNo)->orderByDesc('WarningDate')->limit(50)->get();

        return self::list($training, 'TrainingDate', fn ($row) => "Pelatihan: {$row->TrainingTitle}. Nilai: {$row->TrainingResultGrade}")
            .self::list($warnings, 'WarningDate', fn ($row) => "Peringatan: {$row->Title}. {$row->Description}");
    }

    private static function list($rows, string $date, callable $text): string
    {
        return collect($rows)->map(fn ($row) => '<p><strong>'.e($row->{$date}).'</strong> - '.e($text($row)).'</p>')->implode('');
    }
}
