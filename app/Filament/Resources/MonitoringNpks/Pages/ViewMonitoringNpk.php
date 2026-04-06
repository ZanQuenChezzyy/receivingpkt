<?php

namespace App\Filament\Resources\MonitoringNpks\Pages;

use App\Filament\Resources\MonitoringNpks\MonitoringNpkResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMonitoringNpk extends ViewRecord
{
    protected static string $resource = MonitoringNpkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
