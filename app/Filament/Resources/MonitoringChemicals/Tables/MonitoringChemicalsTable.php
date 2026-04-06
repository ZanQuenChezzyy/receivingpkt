<?php

namespace App\Filament\Resources\MonitoringChemicals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitoringChemicalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('material_category')
                    ->searchable(),
                TextColumn::make('purchase_order_issued_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('qc_by')
                    ->searchable(),
                TextColumn::make('do_number')
                    ->searchable(),
                TextColumn::make('received_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('location_id')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_qty_tolerance')
                    ->boolean(),
                IconColumn::make('has_update_progress')
                    ->boolean(),
                TextColumn::make('tanggal_pengajuan_simala')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_pengambilan_sample')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_terbit_coa')
                    ->date()
                    ->sortable(),
                TextColumn::make('leadtime_coa')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('doc_status')
                    ->searchable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
