<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Pegawai - {{ $employee->EmployeeName }}</title>
    <style>
        @page { margin: 82px 36px 55px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1f2937; }
        header { position: fixed; top: -62px; left: 0; right: 0; border-bottom: 2px solid #111827; padding-bottom: 8px; }
        footer { position: fixed; bottom: -38px; left: 0; right: 0; border-top: 1px solid #9ca3af; padding-top: 6px; text-align: center; color: #6b7280; }
        .page-number:after { content: "Halaman " counter(page) " dari " counter(pages); }
        h1 { margin: 0; font-size: 17px; }
        h2 { font-size: 12px; background: #e5e7eb; padding: 6px; margin: 15px 0 6px; border-left: 4px solid #d97706; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 5px; vertical-align: top; }
        th { width: 24%; text-align: left; background: #f3f4f6; }
        .grid td { width: 50%; }
        .muted { color: #6b7280; }
        .break { page-break-before: always; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
<header>
    <h1>Data Pegawai</h1>
    <div>{{ $employee->EmployeeID }} - {{ $employee->EmployeeName }}</div>
</header>
<footer><span class="page-number"></span></footer>

<h2>Ringkasan Kepegawaian</h2>
<table class="grid">
    <tr><th>ID Pegawai</th><td>{{ $employee->EmployeeID }}</td><th>Nomor Internal</th><td>{{ $employee->EmployeeNo }}</td></tr>
    <tr><th>Status</th><td>{{ ! $employee->DateOut || $employee->DateOut >= today()->toDateString() ? 'Aktif' : 'Tidak Aktif' }}</td><th>Status Pegawai</th><td>{{ $lookups['status'] }}</td></tr>
    <tr><th>Departemen</th><td>{{ $lookups['department'] }}</td><th>Jabatan</th><td>{{ $lookups['position'] }}</td></tr>
    <tr><th>Divisi</th><td>{{ $lookups['division'] }}</td><th>Cabang</th><td>{{ $lookups['branch'] }}</td></tr>
    <tr><th>Mulai Kerja</th><td>{{ $employee->DateIn ?: '-' }}</td><th>Selesai Kerja</th><td>{{ $employee->DateOut ?: '-' }}</td></tr>
</table>

<h2>Data Pribadi dan Kontak</h2>
<table class="grid">
    <tr><th>Nama</th><td>{{ $employee->EmployeeName }}</td><th>NIK/KTP</th><td>{{ $employee->IDCardNo ?: '-' }}</td></tr>
    <tr><th>Tempat/Tanggal Lahir</th><td>{{ $employee->BirthPlace ?: '-' }}, {{ $employee->BirthDate ?: '-' }}</td><th>Jenis Kelamin</th><td>{{ $employee->IsMale ? 'Laki-laki' : 'Perempuan' }}</td></tr>
    <tr><th>Agama</th><td>{{ $lookups['religion'] }}</td><th>Pendidikan</th><td>{{ $lookups['education'] }}</td></tr>
    <tr><th>Telepon</th><td>{{ $employee->Phone ?: '-' }}</td><th>HP</th><td>{{ $employee->HP ?: '-' }}</td></tr>
    <tr><th>Email</th><td>{{ $employee->EmailAddress ?: '-' }}</td><th>Negara</th><td>{{ $lookups['country'] }}</td></tr>
    <tr><th>Alamat</th><td colspan="3">{{ $employee->Address ?: '-' }}</td></tr>
</table>

<h2>Keluarga dan Jadwal</h2>
<table class="grid">
    <tr><th>Status Hubungan</th><td>{{ $employee->RelationNo ?: '-' }}</td><th>Orang Tua</th><td>{{ $employee->ParentName ?: '-' }}</td></tr>
    <tr><th>Pasangan</th><td>{{ $employee->SpouseName ?: '-' }}</td><th>Jumlah Anak</th><td>{{ $employee->TotalChildren }}</td></tr>
    <tr><th>Mulai Jadwal</th><td>{{ $employee->StartSchedule ?: '-' }}</td><th>Barcode</th><td>{{ $employee->BarcodeNo ?: '-' }}</td></tr>
</table>

<h2>Gaji, Bank, Pajak, dan Pinjaman</h2>
<table class="grid">
    <tr><th>Gaji Terakhir</th><td>Rp{{ number_format((float) $employee->LatestSalary, 0, ',', '.') }}</td><th>Perubahan Gaji</th><td>{{ $employee->LastSalaryChange ?: '-' }}</td></tr>
    <tr><th>Bank</th><td>{{ $lookups['bank'] }}</td><th>Cabang Bank</th><td>{{ $employee->BankBranch ?: '-' }}</td></tr>
    <tr><th>Nomor Rekening</th><td>{{ $employee->BankAccountNo ?: '-' }}</td><th>Nama Rekening</th><td>{{ $employee->BankAccountName ?: '-' }}</td></tr>
    <tr><th>NPWP</th><td>{{ $employee->NPWP ?: '-' }}</td><th>Nama NPWP</th><td>{{ $employee->NPWPName ?: '-' }}</td></tr>
    <tr><th>Total Pinjaman</th><td>Rp{{ number_format((float) $employee->TotalLoans, 0, ',', '.') }}</td><th>Sisa Catatan</th><td>{{ $loans->count() }}</td></tr>
    <tr><th>Alamat NPWP</th><td colspan="3">{{ $employee->NPWPAddress ?: '-' }}</td></tr>
</table>

<div class="break"></div>
<h2>Riwayat Umum</h2>
<table><tr><th>Tanggal</th><th>Deskripsi</th></tr>
@forelse ($history as $row)<tr><td>{{ $row->RefDate }}</td><td>{{ $row->Description ?: '-' }}</td></tr>@empty<tr><td colspan="2" class="muted">Tidak ada data.</td></tr>@endforelse
</table>

<h2>Absensi dan Cuti</h2>
<table><tr><th>Tanggal</th><th>Status</th><th>Keterangan</th></tr>
@forelse ($absences as $row)<tr><td>{{ $row->AbsenceDate }}</td><td>{{ $row->IsAbsence ? 'Tidak hadir' : ($row->IsPermission ? 'Izin' : 'Hadir') }}</td><td>{{ $row->Remarks ?? '-' }}</td></tr>@empty<tr><td colspan="3" class="muted">Tidak ada data.</td></tr>@endforelse
</table>
@foreach ($leaves as $row)<p>Cuti: {{ $row->StartDate }} sampai {{ $row->ExpiredDate ?: '-' }} | {{ $row->TotalDays ?? '-' }} hari</p>@endforeach

<h2>Payroll, Klaim, dan Pinjaman</h2>
<table><tr><th>Tanggal</th><th>Jenis</th><th>Nominal</th><th>Keterangan</th></tr>
@foreach ($payroll as $row)<tr><td>{{ $row->PayrollDate }}</td><td>Payroll</td><td>Rp{{ number_format((float) $row->GrandTotal, 0, ',', '.') }}</td><td>{{ $row->PayrollID }}</td></tr>@endforeach
@foreach ($claims as $row)<tr><td>{{ $row->ClaimDate }}</td><td>Klaim</td><td>Rp{{ number_format((float) $row->Amount, 0, ',', '.') }}</td><td>{{ $row->Remarks ?: '-' }}</td></tr>@endforeach
@foreach ($loans as $row)<tr><td>{{ $row->LoanDate }}</td><td>Pinjaman</td><td>Rp{{ number_format((float) $row->Amount, 0, ',', '.') }}</td><td>Sisa Rp{{ number_format((float) $row->LoanLeft, 0, ',', '.') }}</td></tr>@endforeach
</table>
</body>
</html>
