<?php

namespace App\Filament\Resources\PurchaseOrderIssueds\Pages;

use App\Filament\Resources\PurchaseOrderIssueds\PurchaseOrderIssuedResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrderIssued extends ViewRecord
{
    protected static string $resource = PurchaseOrderIssuedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
