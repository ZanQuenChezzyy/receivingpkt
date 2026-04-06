<?php

namespace App\Filament\Resources\MonitoringChemicalDetails\Pages;

use App\Filament\Resources\MonitoringChemicalDetails\MonitoringChemicalDetailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringChemicalDetails extends ListRecords
{
    protected static string $resource = MonitoringChemicalDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
