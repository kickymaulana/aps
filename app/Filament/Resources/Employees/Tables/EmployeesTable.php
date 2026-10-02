<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('EmployeeID')->label('ID')->searchable(),
                TextColumn::make('EmployeeName')->label('Nama')->searchable(),
                TextColumn::make('EmailAddress')->label('Email')->searchable(),
                TextColumn::make('DateIn')->label('Mulai Kerja')->date(),
                TextColumn::make('DateOut')->label('Selesai Kerja')->date(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('EmployeeName');
    }
}
