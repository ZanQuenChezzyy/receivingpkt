<?php

namespace App\Filament\Resources\MonitoringChemicalDetails\Pages;

use App\Filament\Resources\MonitoringChemicalDetails\MonitoringChemicalDetailResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMonitoringChemicalDetail extends EditRecord
{
    protected static string $resource = MonitoringChemicalDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
