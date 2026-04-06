<?php

namespace App\Filament\Resources\DeliveryOrderReceiptDetails\Pages;

use App\Filament\Resources\DeliveryOrderReceiptDetails\DeliveryOrderReceiptDetailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryOrderReceiptDetails extends ListRecords
{
    protected static string $resource = DeliveryOrderReceiptDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
