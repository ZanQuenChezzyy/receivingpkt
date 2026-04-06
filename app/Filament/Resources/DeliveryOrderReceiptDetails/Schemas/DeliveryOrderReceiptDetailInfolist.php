<?php

namespace App\Filament\Resources\DeliveryOrderReceiptDetails\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DeliveryOrderReceiptDetailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('delivery_order_receipt_id')
                    ->numeric(),
                TextEntry::make('purchase_order_issued_id')
                    ->numeric(),
                TextEntry::make('item_no')
                    ->numeric(),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('material_code'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('uoi')
                    ->placeholder('-'),
                TextEntry::make('mrp_type')
                    ->placeholder('-'),
                TextEntry::make('material_type')
                    ->placeholder('-'),
                TextEntry::make('aac')
                    ->placeholder('-'),
                TextEntry::make('abc_indicator')
                    ->placeholder('-'),
                TextEntry::make('requisitioner')
                    ->placeholder('-'),
                TextEntry::make('total_amount_snapshot')
                    ->numeric(),
                TextEntry::make('location_id')
                    ->numeric(),
                IconEntry::make('is_different_location')
                    ->boolean(),
                IconEntry::make('is_qty_tolerance')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
