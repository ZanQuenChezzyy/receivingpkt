<?php

namespace App\Filament\Resources\MonitoringNpkDetails\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MonitoringNpkDetailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('npk_monitoring_id')
                    ->required()
                    ->numeric(),
                TextInput::make('item_no')
                    ->required()
                    ->numeric(),
                TextInput::make('material_code')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('uoi')
                    ->required(),
                TextInput::make('location_id')
                    ->numeric(),
                Toggle::make('is_qty_tolerance')
                    ->required(),
            ]);
    }
}
