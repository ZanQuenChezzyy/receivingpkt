<?php

namespace App\Filament\Resources\PurchaseOrderIssueds\Pages;

use App\Filament\Resources\PurchaseOrderIssueds\PurchaseOrderIssuedResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrderIssued extends EditRecord
{
    protected static string $resource = PurchaseOrderIssuedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
