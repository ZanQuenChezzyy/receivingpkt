<?php

namespace App\Filament\Resources\DeliveryOrderReceipts\Pages;

use App\Filament\Resources\DeliveryOrderReceipts\DeliveryOrderReceiptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryOrderReceipts extends ListRecords
{
    protected static string $resource = DeliveryOrderReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
