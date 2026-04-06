<?php

namespace App\Filament\Resources\MonitoringNpks\Pages;

use App\Filament\Resources\MonitoringNpks\MonitoringNpkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringNpks extends ListRecords
{
    protected static string $resource = MonitoringNpkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
