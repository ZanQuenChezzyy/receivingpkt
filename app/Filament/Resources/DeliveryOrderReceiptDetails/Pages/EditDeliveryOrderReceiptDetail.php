<?php

namespace App\Filament\Resources\DeliveryOrderReceiptDetails\Pages;

use App\Filament\Resources\DeliveryOrderReceiptDetails\DeliveryOrderReceiptDetailResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryOrderReceiptDetail extends EditRecord
{
    protected static string $resource = DeliveryOrderReceiptDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
