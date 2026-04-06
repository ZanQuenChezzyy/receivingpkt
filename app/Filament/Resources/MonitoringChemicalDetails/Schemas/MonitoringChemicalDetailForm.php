<?php

namespace App\Filament\Resources\MonitoringChemicalDetails\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MonitoringChemicalDetailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('chemical_monitoring_id')
                    ->required()
                    ->numeric(),
                TextInput::make('tahapan_qc_id')
                    ->numeric(),
                TextInput::make('quantity_received')
                    ->required()
                    ->numeric(),
            ]);
    }
}
