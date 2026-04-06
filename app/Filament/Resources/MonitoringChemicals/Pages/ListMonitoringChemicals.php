<?php

namespace App\Filament\Resources\MonitoringChemicals\Pages;

use App\Filament\Resources\MonitoringChemicals\MonitoringChemicalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringChemicals extends ListRecords
{
    protected static string $resource = MonitoringChemicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
