<?php

namespace App\Filament\Resources\LocationReceivings\Pages;

use App\Filament\Resources\LocationReceivings\LocationReceivingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageLocationReceivings extends ManageRecords
{
    protected static string $resource = LocationReceivingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Lokasi Penyimpanan Receiving')
                ->icon(Heroicon::PlusCircle),
        ];
    }
}
