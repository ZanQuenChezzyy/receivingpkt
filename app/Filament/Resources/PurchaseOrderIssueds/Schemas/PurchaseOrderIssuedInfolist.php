<?php

namespace App\Filament\Resources\PurchaseOrderIssueds\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseOrderIssuedInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('purchase_order_and_item')
                    ->placeholder('-'),
                TextEntry::make('material_type'),
                TextEntry::make('mrp_type'),
                TextEntry::make('purchase_order_no'),
                TextEntry::make('item_no')
                    ->numeric(),
                TextEntry::make('material_code')
                    ->placeholder('-'),
                TextEntry::make('aac')
                    ->placeholder('-'),
                TextEntry::make('abc_indicator')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('qty_po')
                    ->numeric(),
                TextEntry::make('uoi'),
                TextEntry::make('vendor_id')
                    ->placeholder('-'),
                TextEntry::make('vendor_name'),
                TextEntry::make('date_create')
                    ->date(),
                TextEntry::make('delivery_date_po')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('po_status')
                    ->placeholder('-'),
                TextEntry::make('incoterm')
                    ->placeholder('-'),
                TextEntry::make('total_amount_in_lc')
                    ->numeric(),
                TextEntry::make('requisitioner'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
