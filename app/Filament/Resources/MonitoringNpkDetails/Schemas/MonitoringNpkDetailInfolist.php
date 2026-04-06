<?php

namespace App\Filament\Resources\MonitoringNpkDetails\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MonitoringNpkDetailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('npk_monitoring_id')
                    ->numeric(),
                TextEntry::make('item_no')
                    ->numeric(),
                TextEntry::make('material_code'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('uoi'),
                TextEntry::make('location_id')
                    ->numeric()
                    ->placeholder('-'),
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
