<?php

namespace App\Filament\Resources\MonitoringChemicals\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MonitoringChemicalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('material_category'),
                TextEntry::make('purchase_order_issued_id')
                    ->numeric(),
                TextEntry::make('qc_by'),
                TextEntry::make('do_number')
                    ->placeholder('-'),
                TextEntry::make('received_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('location_id')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_qty_tolerance')
                    ->boolean(),
                IconEntry::make('has_update_progress')
                    ->boolean(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('tanggal_pengajuan_simala')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('tanggal_pengambilan_sample')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('tanggal_terbit_coa')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('leadtime_coa')
                    ->numeric()
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
