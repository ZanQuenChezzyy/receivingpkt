<?php

namespace App\Filament\Resources\MonitoringNpks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MonitoringNpkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('purchase_order_terbit_id')
                    ->required()
                    ->numeric(),
                TextInput::make('delivery_oder_number'),
                TextInput::make('location_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('sample_receivied_date'),
                TextInput::make('stage'),
                DatePicker::make('delivery_oder_delivery_date'),
                DatePicker::make('purchase_order_103_date'),
                DatePicker::make('received_date'),
                TextInput::make('purchase_order_status'),
                DatePicker::make('purchase_order_status_a_date'),
                DatePicker::make('purchase_order_status_b_date'),
                TextInput::make('purchase_order_status_a_files'),
                DatePicker::make('laprima_date'),
                DatePicker::make('coa_date'),
                TextInput::make('coa_files'),
                TextInput::make('doc_status')
                    ->required()
                    ->default('Outstanding'),
                TextInput::make('created_by')
                    ->required()
                    ->numeric(),
            ]);
    }
}
