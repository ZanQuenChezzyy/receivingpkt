<?php

namespace App\Filament\Resources\MonitoringNpks\Pages;

use App\Filament\Resources\MonitoringNpks\MonitoringNpkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMonitoringNpk extends EditRecord
{
    protected static string $resource = MonitoringNpkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
