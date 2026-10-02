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
        return $schema->components([
            Section::make('Ringkasan Pegawai')->schema([
                TextEntry::make('EmployeeNo')->label('Nomor Internal'),
                TextEntry::make('EmployeeID')->label('ID Pegawai'),
                TextEntry::make('EmployeeName')->label('Nama'),
                TextEntry::make('active_status')->label('Status Aktif')->state(fn ($record): string => ! $record->DateOut || $record->DateOut >= today()->toDateString() ? 'Aktif' : 'Tidak Aktif')->badge()->color(fn (string $state): string => $state === 'Aktif' ? 'success' : 'danger'),
                self::lookupEntry('CurrentEmployeeStatusNo', 'Status Pegawai', 'employeestatus', 'EmployeeStatusNo', 'EmployeeStatusName'),
                self::lookupEntry('CurrentPositionNo', 'Jabatan', 'positions', 'PositionNo', 'PositionName'),
                self::lookupEntry('CurrentDepartmentNo', 'Departemen', 'departments', 'DepartmentNo', 'DepartmentName'),
                self::lookupEntry('CurrentDivisionNo', 'Divisi', 'divisions', 'DivisionNo', 'DivisionName'),
                self::lookupEntry('CurrentBranchNo', 'Cabang', 'branches', 'BranchNo', 'BranchName'),
                TextEntry::make('DateIn')->label('Mulai Kerja')->date()->placeholder('-'),
                TextEntry::make('DateOut')->label('Selesai Kerja')->date()->placeholder('-'),
                TextEntry::make('ResignReason')->label('Alasan Berhenti')->placeholder('-')->columnSpanFull(),
            ])->columns(3),

            Section::make('Data Pribadi dan Kontak')->schema([
                TextEntry::make('Address')->label('Alamat')->placeholder('-')->columnSpanFull(),
                TextEntry::make('Phone')->label('Telepon')->placeholder('-'),
                TextEntry::make('HP')->label('HP')->placeholder('-'),
                TextEntry::make('EmailAddress')->label('Email')->placeholder('-'),
                TextEntry::make('BirthPlace')->label('Tempat Lahir')->placeholder('-'),
                TextEntry::make('BirthDate')->label('Tanggal Lahir')->date()->placeholder('-'),
                TextEntry::make('IsMale')->label('Jenis Kelamin')->formatStateUsing(fn ($state): string => self::yes($state) ? 'Laki-laki' : 'Perempuan'),
                TextEntry::make('IDCardNo')->label('NIK/KTP')->placeholder('-'),
                self::lookupEntry('ReligionNo', 'Agama', 'religions', 'ReligionNo', 'ReligionName'),
                self::lookupEntry('EducationNo', 'Pendidikan', 'educations', 'EducationNo', 'EducationName'),
                self::lookupEntry('CountryNo', 'Negara', 'countries', 'CountryNo', 'CountryName'),
                TextEntry::make('IsForeign')->label('Warga Negara Asing')->formatStateUsing(fn ($state): string => self::yes($state) ? 'Ya' : 'Tidak'),
                TextEntry::make('BarcodeNo')->label('Barcode')->placeholder('-'),
                TextEntry::make('ExpiredDate')->label('Masa Berlaku')->date()->placeholder('-'),
            ])->columns(3),

            Section::make('Keluarga')->schema([
                self::lookupEntry('RelationNo', 'Status Hubungan', 'relations', 'RelationNo', 'RelationName'),
                TextEntry::make('ParentName')->label('Nama Orang Tua')->placeholder('-'),
                TextEntry::make('SpouseName')->label('Nama Pasangan')->placeholder('-'),
                TextEntry::make('TotalChildren')->label('Jumlah Anak')->numeric(),
            ])->columns(2),

            Section::make('Pekerjaan dan Jadwal')->schema([
                self::lookupEntry('GradeLevelNo', 'Grade', 'gradelevels', 'GradeLevelNo', 'GradeLevelName'),
                self::lookupEntry('GroupScheduleNo', 'Grup Jadwal', 'groupschedules', 'GroupScheduleNo', 'GroupScheduleName'),
                self::lookupEntry('ScheduleTypeNo', 'Tipe Jadwal', 'scheduletypes', 'ScheduleTypeNo', 'ScheduleTypeName'),
                TextEntry::make('StartSchedule')->label('Mulai Jadwal')->date()->placeholder('-'),
                self::booleanEntry('MustClockIn', 'Wajib Clock In'),
                self::booleanEntry('MustClockOut', 'Wajib Clock Out'),
                self::booleanEntry('MustBreakOut', 'Wajib Break Out'),
                self::booleanEntry('MustBreakIn', 'Wajib Break In'),
                TextEntry::make('ComeOvertimeTypeNo')->label('Tipe Lembur Datang')->placeholder('-'),
                TextEntry::make('LeaveOvertimeTypeNo')->label('Tipe Lembur Pulang')->placeholder('-'),
                self::booleanEntry('IsHolidayOvertime', 'Lembur Hari Libur'),
                TextEntry::make('PeriodicNo')->label('Periodik')->placeholder('-'),
                TextEntry::make('ClaimApprovalGroupNo')->label('Grup Persetujuan Klaim')->placeholder('-'),
                TextEntry::make('AbsenceApprovalGroupNo')->label('Grup Persetujuan Absensi')->placeholder('-'),
            ])->columns(3),

            Section::make('Gaji, Bank, Pajak, dan Pinjaman')->schema([
                self::moneyEntry('LatestSalary', 'Gaji Terakhir'),
                TextEntry::make('LastSalaryChange')->label('Perubahan Gaji Terakhir')->date()->placeholder('-'),
                self::lookupEntry('BankNo', 'Bank', 'banks', 'BankNo', 'BankName'),
                TextEntry::make('BankBranch')->label('Cabang Bank')->placeholder('-'),
                TextEntry::make('BankAccountNo')->label('Nomor Rekening')->placeholder('-'),
                TextEntry::make('BankAccountName')->label('Nama Rekening')->placeholder('-'),
                self::lookupEntry('CurrencyNo', 'Mata Uang', 'currency', 'CurrencyNo', 'CurrencyName'),
                self::booleanEntry('IsTaxable', 'Kena Pajak'),
                self::booleanEntry('UsingSalaryTypeTax', 'Pajak Berdasarkan Tipe Gaji'),
                TextEntry::make('NPWP')->label('NPWP')->placeholder('-'),
                TextEntry::make('NPWPName')->label('Nama NPWP')->placeholder('-'),
                TextEntry::make('NPWPAddress')->label('Alamat NPWP')->placeholder('-')->columnSpanFull(),
                self::moneyEntry('BudgetLimit', 'Batas Anggaran'),
                self::moneyEntry('TotalLoans', 'Total Pinjaman'),
                self::moneyEntry('TotalLoanFlexible', 'Total Pinjaman Fleksibel'),
                self::moneyEntry('LoanFlexiblePayment', 'Pembayaran Pinjaman Fleksibel'),
                TextEntry::make('PayrollInfo')->label('Informasi Payroll')->placeholder('-')->columnSpanFull(),
            ])->columns(3),

            Section::make('Referensi dan Audit')->schema([
                TextEntry::make('CandidateRefNo')->label('Referensi Kandidat')->placeholder('-'),
                TextEntry::make('TemplateSalaryTypeNo')->label('Template Tipe Gaji')->placeholder('-'),
                TextEntry::make('CreatedBy')->label('Dibuat Oleh')->placeholder('-'),
                TextEntry::make('CreatedOn')->label('Dibuat Pada')->dateTime()->placeholder('-'),
                TextEntry::make('UpdateBy')->label('Diubah Oleh')->placeholder('-'),
                TextEntry::make('UpdateOn')->label('Diubah Pada')->dateTime()->placeholder('-'),
                TextEntry::make('Remarks')->label('Catatan')->placeholder('-')->columnSpanFull(),
            ])->columns(3)->collapsible()->collapsed(),

            Section::make('Riwayat Kepegawaian')->schema([
                TextEntry::make('history_summary')->label('Riwayat status dan mutasi')->state(fn ($record): string => self::history($record->EmployeeNo))->columnSpanFull()->html(),
            ]),
            Section::make('Absensi dan Cuti')->schema([
                TextEntry::make('absence_summary')->label('Ringkasan')->state(fn ($record): string => self::absence($record->EmployeeNo))->columnSpanFull()->html(),
            ]),
            Section::make('Payroll, Klaim, dan Pinjaman')->schema([
                TextEntry::make('financial_summary')->label('Ringkasan')->state(fn ($record): string => self::financial($record->EmployeeNo))->columnSpanFull()->html(),
            ]),
            Section::make('Pelatihan dan Peringatan')->schema([
                TextEntry::make('development_summary')->label('Ringkasan')->state(fn ($record): string => self::development($record->EmployeeNo))->columnSpanFull()->html(),
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
            .self::list($status, 'HistoryDate', fn ($row) => 'Perubahan status: '.self::lookup('employeestatus', 'EmployeeStatusNo', 'EmployeeStatusName', $row->FromEmployeeStatusNo).' ke '.self::lookup('employeestatus', 'EmployeeStatusNo', 'EmployeeStatusName', $row->ToEmployeeStatusNo).'. '.$row->Description)
            .self::list($transfers, 'TransferDate', fn ($row) => self::transferDescription($row))
            .self::list($positions, 'PositionTransferDate', fn ($row) => 'Perubahan jabatan: '.self::lookup('positions', 'PositionNo', 'PositionName', $row->FromPositionNo).' ke '.self::lookup('positions', 'PositionNo', 'PositionName', $row->ToPositionNo).'. '.$row->Description);
    }

    private static function absence(int $employeeNo): string
    {
        $month = DB::table('absences')->where('EmployeeNo', $employeeNo)->whereBetween('AbsenceDate', [today()->startOfMonth(), today()]);
        $leave = DB::table('employeeleaves')->where('EmployeeNo', $employeeNo)->where('StartDate', '<=', today())->where(fn ($query) => $query->whereNull('ExpiredDate')->orWhereDate('ExpiredDate', '>=', today()))->count();

        return 'Catatan bulan ini: '.$month->count().'<br>Tidak hadir: '.(clone $month)->where('IsAbsence', 1)->count().'<br>Izin: '.(clone $month)->where('IsPermission', 1)->count().'<br>Cuti aktif: '.$leave;
    }

    private static function financial(int $employeeNo): string
    {
        $payroll = DB::table('payroll')->where('EmployeeNo', $employeeNo)->orderByDesc('PayrollDate')->limit(20)->get();
        $claims = DB::table('employeeclaims')->where('EmployeeNo', $employeeNo)->orderByDesc('ClaimDate')->limit(20)->get();
        $loans = DB::table('loans')->where('EmployeeNo', $employeeNo)->orderByDesc('LoanDate')->limit(20)->get();

        return self::list($payroll, 'PayrollDate', fn ($row) => "Payroll {$row->PayrollID}: ".self::rupiah($row->GrandTotal))
            .self::list($claims, 'ClaimDate', fn ($row) => 'Klaim: '.self::rupiah($row->Amount).'. '.$row->Remarks)
            .self::list($loans, 'LoanDate', fn ($row) => 'Pinjaman: '.self::rupiah($row->Amount).', sisa '.self::rupiah($row->LoanLeft).'. '.$row->Remarks);
    }

    private static function development(int $employeeNo): string
    {
        $training = DB::table('trainingdetails')->join('trainings', 'trainings.TrainingNo', '=', 'trainingdetails.TrainingNo')->where('trainingdetails.EmployeeNo', $employeeNo)->orderByDesc('TrainingDate')->limit(50)->get();
        $warnings = DB::table('warningletters')->where('EmployeeNo', $employeeNo)->orderByDesc('WarningDate')->limit(50)->get();

        return self::list($training, 'TrainingDate', fn ($row) => "Pelatihan: {$row->TrainingTitle}. Nilai: {$row->TrainingResultGrade}. {$row->TrainingResutlRemarks}")
            .self::list($warnings, 'WarningDate', fn ($row) => "Peringatan: {$row->WarningLetterID} - {$row->Title}. Berlaku sampai {$row->ExpiredDate}. {$row->Description}");
    }

    private static function transferDescription(object $row): string
    {
        $parts = [];
        foreach ([['Branch', 'branches', 'BranchNo', 'BranchName', 'Cabang'], ['Department', 'departments', 'DepartmentNo', 'DepartmentName', 'Departemen'], ['Division', 'divisions', 'DivisionNo', 'DivisionName', 'Divisi'], ['Position', 'positions', 'PositionNo', 'PositionName', 'Jabatan'], ['EmployeeStatus', 'employeestatus', 'EmployeeStatusNo', 'EmployeeStatusName', 'Status']] as [$field, $table, $key, $name, $label]) {
            $from = $row->{'From'.$field.'No'} ?? null;
            $to = $row->{'To'.$field.'No'} ?? null;
            if ($from != $to && ($from || $to)) {
                $parts[] = $label.': '.self::lookup($table, $key, $name, $from).' ke '.self::lookup($table, $key, $name, $to);
            }
        }

        return implode('; ', $parts).($row->Description ? '. '.$row->Description : '');
    }

    private static function lookupEntry(string $field, string $label, string $table, string $key, string $name): TextEntry
    {
        return TextEntry::make($field)->label($label)->formatStateUsing(fn ($state): string => self::lookup($table, $key, $name, $state))->placeholder('-');
    }

    private static function booleanEntry(string $field, string $label): TextEntry
    {
        return TextEntry::make($field)->label($label)->formatStateUsing(fn ($state): string => self::yes($state) ? 'Ya' : 'Tidak')->badge()->color(fn ($state): string => self::yes($state) ? 'success' : 'gray');
    }

    private static function moneyEntry(string $field, string $label): TextEntry
    {
        return TextEntry::make($field)->label($label)->formatStateUsing(fn ($state): string => self::rupiah($state));
    }

    private static function lookup(string $table, string $key, string $name, $value): string
    {
        return $value ? (DB::table($table)->where($key, $value)->value($name) ?? (string) $value) : '-';
    }

    private static function yes($value): bool
    {
        return in_array($value, [1, '1', true, "\x01"], true);
    }

    private static function rupiah($value): string
    {
        return 'Rp'.number_format((float) $value, 0, ',', '.');
    }

    private static function list($rows, string $date, callable $text): string
    {
        if ($rows->isEmpty()) {
            return '<p>Tidak ada data.</p>';
        }

        return collect($rows)->map(fn ($row) => '<p><strong>'.e($row->{$date}).'</strong> - '.e($text($row)).'</p>')->implode('');
    }
}
