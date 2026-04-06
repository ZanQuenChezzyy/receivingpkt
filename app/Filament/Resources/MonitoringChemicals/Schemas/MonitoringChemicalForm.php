<?php

namespace App\Filament\Resources\MonitoringChemicals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MonitoringChemicalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('material_category')
                    ->required(),
                TextInput::make('purchase_order_issued_id')
                    ->required()
                    ->numeric(),
                TextInput::make('qc_by')
                    ->required(),
                TextInput::make('do_number'),
                DatePicker::make('received_date'),
                TextInput::make('location_id')
                    ->numeric(),
                Toggle::make('is_qty_tolerance')
                    ->required(),
                Toggle::make('has_update_progress')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DatePicker::make('tanggal_pengajuan_simala'),
                DatePicker::make('tanggal_pengambilan_sample'),
                DatePicker::make('tanggal_terbit_coa'),
                TextInput::make('leadtime_coa')
                    ->numeric(),
                TextInput::make('doc_status')
                    ->required()
                    ->default('Outstanding'),
                TextInput::make('created_by')
                    ->required()
                    ->numeric(),
            ]);
    }
}
