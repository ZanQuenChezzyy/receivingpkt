<?php

namespace App\Filament\Resources\MonitoringChemicalDetails\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MonitoringChemicalDetailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('chemical_monitoring_id')
                    ->numeric(),
                TextEntry::make('tahapan_qc_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('quantity_received')
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
