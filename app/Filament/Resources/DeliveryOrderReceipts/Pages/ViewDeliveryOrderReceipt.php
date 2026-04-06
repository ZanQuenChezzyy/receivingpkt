<?php

namespace App\Filament\Resources\DeliveryOrderReceipts\Pages;

use App\Filament\Resources\DeliveryOrderReceipts\DeliveryOrderReceiptResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryOrderReceipt extends ViewRecord
{
    protected static string $resource = DeliveryOrderReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
