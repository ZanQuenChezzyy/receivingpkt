<?php

namespace App\Filament\Resources\MonitoringChemicalDetails\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitoringChemicalDetailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('monitoringChemical.purchaseOrder.purchase_order_no')
                    ->label('Purchase Order No')
                    ->sortable(),
                TextColumn::make('monitoringChemical.purchaseOrder.description')
                    ->label('Keterangan')
                    ->sortable(),
                TextColumn::make('chemicalQcTuv.tahapan_name')
                    ->label('Tahapan QC')
                    ->sortable(),
                TextColumn::make('quantity_received')
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
