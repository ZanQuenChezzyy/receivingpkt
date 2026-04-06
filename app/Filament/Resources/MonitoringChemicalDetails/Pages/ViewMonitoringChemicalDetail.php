<?php

namespace App\Filament\Resources\MonitoringChemicalDetails\Pages;

use App\Filament\Resources\MonitoringChemicalDetails\MonitoringChemicalDetailResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMonitoringChemicalDetail extends ViewRecord
{
    protected static string $resource = MonitoringChemicalDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
