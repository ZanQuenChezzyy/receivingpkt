<?php

namespace App\Filament\Resources\DeliveryOrderReceipts\Pages;

use App\Filament\Resources\DeliveryOrderReceipts\DeliveryOrderReceiptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryOrderReceipt extends CreateRecord
{
    protected static string $resource = DeliveryOrderReceiptResource::class;
}
