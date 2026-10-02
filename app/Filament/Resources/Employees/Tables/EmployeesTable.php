<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->addSelect([
                'current_department_name' => self::lookupQuery('departments', 'DepartmentNo', 'DepartmentName', 'CurrentDepartmentNo'),
                'current_division_name' => self::lookupQuery('divisions', 'DivisionNo', 'DivisionName', 'CurrentDivisionNo'),
                'current_position_name' => self::lookupQuery('positions', 'PositionNo', 'PositionName', 'CurrentPositionNo'),
            ]))
            ->columns([
                TextColumn::make('EmployeeID')->label('ID')->searchable(),
                TextColumn::make('IDCardNo')->label('NIK KTP')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('EmployeeName')->label('Nama')->searchable(),
                TextColumn::make('current_department_name')->label('Departemen')->searchable()->sortable(),
                TextColumn::make('current_division_name')->label('Divisi')->searchable()->sortable(),
                TextColumn::make('current_position_name')->label('Jabatan')->searchable()->sortable(),
                TextColumn::make('EmailAddress')->label('Email')->searchable(),
                TextColumn::make('DateIn')->label('Mulai Kerja')->date(),
                TextColumn::make('DateOut')->label('Selesai Kerja')->date(),
            ])
            ->filters([
                SelectFilter::make('employment_state')
                    ->label('Status Aktif')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'active' => $query->where(fn (Builder $query) => $query
                            ->whereNull('DateOut')
                            ->orWhereDate('DateOut', '>=', today())),
                        'inactive' => $query->whereNotNull('DateOut')->whereDate('DateOut', '<', today()),
                        default => $query,
                    }),
                Filter::make('date_in')
                    ->label('Tanggal Masuk')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('DateIn', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('DateIn', '<=', $date))),
                Filter::make('date_out')
                    ->label('Tanggal Keluar')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('DateOut', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('DateOut', '<=', $date))),
                self::masterFilter('CurrentDepartmentNo', 'Departemen', 'departments', 'DepartmentNo', 'DepartmentName'),
                self::masterFilter('CurrentBranchNo', 'Cabang', 'branches', 'BranchNo', 'BranchName'),
                self::masterFilter('CurrentDivisionNo', 'Divisi', 'divisions', 'DivisionNo', 'DivisionName'),
                self::masterFilter('CurrentPositionNo', 'Jabatan', 'positions', 'PositionNo', 'PositionName'),
                self::masterFilter('CurrentEmployeeStatusNo', 'Status Pegawai', 'employeestatus', 'EmployeeStatusNo', 'EmployeeStatusName'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('EmployeeName');
    }

    private static function lookupQuery(string $table, string $key, string $name, string $foreignKey): QueryBuilder
    {
        return DB::table($table)
            ->select($name)
            ->whereColumn($key, 'employees.'.$foreignKey)
            ->limit(1);
    }

    private static function masterFilter(string $column, string $label, string $table, string $key, string $name): SelectFilter
    {
        return SelectFilter::make($column)
            ->label($label)
            ->options(fn (): array => DB::table($table)->orderBy($name)->pluck($name, $key)->all())
            ->searchable()
            ->preload();
    }
}
