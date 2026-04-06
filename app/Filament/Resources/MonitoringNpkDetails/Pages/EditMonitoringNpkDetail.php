<?php

namespace App\Filament\Resources\MonitoringNpkDetails\Pages;

use App\Filament\Resources\MonitoringNpkDetails\MonitoringNpkDetailResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMonitoringNpkDetail extends EditRecord
{
    protected static string $resource = MonitoringNpkDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
