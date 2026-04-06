<?php

namespace App\Filament\Resources\DeliveryOrderReceipts\Pages;

use App\Filament\Resources\DeliveryOrderReceipts\DeliveryOrderReceiptResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryOrderReceipt extends EditRecord
{
    protected static string $resource = DeliveryOrderReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
