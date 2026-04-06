<?php

namespace App\Filament\Resources\MonitoringNpks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MonitoringNpkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('purchase_order_terbit_id')
                    ->numeric(),
                TextEntry::make('delivery_oder_number')
                    ->placeholder('-'),
                TextEntry::make('location_id')
                    ->numeric(),
                TextEntry::make('sample_receivied_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('stage')
                    ->placeholder('-'),
                TextEntry::make('delivery_oder_delivery_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('purchase_order_103_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('received_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('purchase_order_status')
                    ->placeholder('-'),
                TextEntry::make('purchase_order_status_a_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('purchase_order_status_b_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('laprima_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('coa_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('doc_status'),
                TextEntry::make('created_by')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
