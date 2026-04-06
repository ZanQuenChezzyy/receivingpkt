<?php

namespace App\Filament\Resources\DeliveryOrderReceiptDetails\Pages;

use App\Filament\Resources\DeliveryOrderReceiptDetails\DeliveryOrderReceiptDetailResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryOrderReceiptDetail extends ViewRecord
{
    protected static string $resource = DeliveryOrderReceiptDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
