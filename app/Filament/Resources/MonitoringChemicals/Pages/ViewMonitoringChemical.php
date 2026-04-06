<?php

namespace App\Filament\Resources\MonitoringChemicals\Pages;

use App\Filament\Resources\MonitoringChemicals\MonitoringChemicalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMonitoringChemical extends ViewRecord
{
    protected static string $resource = MonitoringChemicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
