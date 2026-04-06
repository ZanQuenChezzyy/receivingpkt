<?php

namespace App\Filament\Resources\PurchaseOrderIssueds\Pages;

use App\Filament\Imports\PurchaseOrderIssuedImporter;
use App\Filament\Resources\PurchaseOrderIssueds\PurchaseOrderIssuedResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPurchaseOrderIssueds extends ListRecords
{
    protected static string $resource = PurchaseOrderIssuedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label('Impor Purchase Order Terbit')
                ->icon(Heroicon::ArrowDownTray)
                ->importer(PurchaseOrderIssuedImporter::class),
            CreateAction::make()
                ->label('Tambah Purchase Order Terbit')
                ->icon(Heroicon::PlusCircle),
        ];
    }
}
