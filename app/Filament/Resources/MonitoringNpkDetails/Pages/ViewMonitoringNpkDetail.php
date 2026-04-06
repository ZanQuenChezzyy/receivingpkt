<?php

namespace App\Filament\Resources\MonitoringNpkDetails\Pages;

use App\Filament\Resources\MonitoringNpkDetails\MonitoringNpkDetailResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMonitoringNpkDetail extends ViewRecord
{
    protected static string $resource = MonitoringNpkDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
