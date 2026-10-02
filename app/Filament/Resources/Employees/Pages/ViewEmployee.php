<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->url(fn (): string => route('employees.pdf', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}
