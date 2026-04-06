<?php

namespace App\Filament\Resources\MonitoringNpks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitoringNpksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_order_terbit_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_oder_number')
                    ->searchable(),
                TextColumn::make('location_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sample_receivied_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('stage')
                    ->searchable(),
                TextColumn::make('delivery_oder_delivery_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_order_103_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('received_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_order_status')
                    ->searchable(),
                TextColumn::make('purchase_order_status_a_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_order_status_b_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('laprima_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('coa_date')
                    ->date()
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
