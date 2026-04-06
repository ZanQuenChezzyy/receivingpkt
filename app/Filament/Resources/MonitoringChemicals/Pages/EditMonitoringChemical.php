<?php

namespace App\Filament\Resources\MonitoringChemicals\Pages;

use App\Filament\Resources\MonitoringChemicals\MonitoringChemicalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMonitoringChemical extends EditRecord
{
    protected static string $resource = MonitoringChemicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
