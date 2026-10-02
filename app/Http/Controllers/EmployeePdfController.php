<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeePdfController extends Controller
{
    public function __invoke(Employee $employee): Response
    {
        $lookups = [
            'department' => $this->lookup('departments', 'DepartmentNo', 'DepartmentName', $employee->CurrentDepartmentNo),
            'branch' => $this->lookup('branches', 'BranchNo', 'BranchName', $employee->CurrentBranchNo),
            'division' => $this->lookup('divisions', 'DivisionNo', 'DivisionName', $employee->CurrentDivisionNo),
            'position' => $this->lookup('positions', 'PositionNo', 'PositionName', $employee->CurrentPositionNo),
            'status' => $this->lookup('employeestatus', 'EmployeeStatusNo', 'EmployeeStatusName', $employee->CurrentEmployeeStatusNo),
            'religion' => $this->lookup('religions', 'ReligionNo', 'ReligionName', $employee->ReligionNo),
            'education' => $this->lookup('educations', 'EducationNo', 'EducationName', $employee->EducationNo),
            'country' => $this->lookup('countries', 'CountryNo', 'CountryName', $employee->CountryNo),
            'bank' => $this->lookup('banks', 'BankNo', 'BankName', $employee->BankNo),
        ];

        $history = DB::table('employeehistories')->where('EmployeeNo', $employee->EmployeeNo)->orderByDesc('RefDate')->get();
        $absences = DB::table('absences')->where('EmployeeNo', $employee->EmployeeNo)->orderByDesc('AbsenceDate')->limit(50)->get();
        $leaves = DB::table('employeeleaves')->where('EmployeeNo', $employee->EmployeeNo)->orderByDesc('StartDate')->limit(50)->get();
        $payroll = DB::table('payroll')->where('EmployeeNo', $employee->EmployeeNo)->orderByDesc('PayrollDate')->limit(20)->get();
        $claims = DB::table('employeeclaims')->where('EmployeeNo', $employee->EmployeeNo)->orderByDesc('ClaimDate')->limit(20)->get();
        $loans = DB::table('loans')->where('EmployeeNo', $employee->EmployeeNo)->orderByDesc('LoanDate')->limit(20)->get();
        $warnings = DB::table('warningletters')->where('EmployeeNo', $employee->EmployeeNo)->orderByDesc('WarningDate')->get();

        return Pdf::loadView('employees.pdf', compact('employee', 'lookups', 'history', 'absences', 'leaves', 'payroll', 'claims', 'loans', 'warnings'))
            ->setPaper('a4')
            ->setOption('isPhpEnabled', true)
            ->download('pegawai-'.$employee->EmployeeID.'.pdf');
    }

    private function lookup(string $table, string $key, string $name, mixed $value): string
    {
        return $value ? (DB::table($table)->where($key, $value)->value($name) ?? (string) $value) : '-';
    }
}
