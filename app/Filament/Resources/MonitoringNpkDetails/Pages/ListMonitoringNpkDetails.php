<?php

namespace App\Filament\Resources\MonitoringNpkDetails\Pages;

use App\Filament\Resources\MonitoringNpkDetails\MonitoringNpkDetailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringNpkDetails extends ListRecords
{
    protected static string $resource = MonitoringNpkDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
