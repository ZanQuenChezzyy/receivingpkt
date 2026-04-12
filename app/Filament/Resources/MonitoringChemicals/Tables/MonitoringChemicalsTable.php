<?php

namespace App\Filament\Resources\MonitoringChemicals\Tables;

use App\Models\MonitoringChemical;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MonitoringChemicalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // 🌟 GRUP 1: INFORMASI MATERIAL
                ColumnGroup::make('Informasi Material', [
                    TextColumn::make('purchaseOrder.purchase_order_no')
                        ->label('PO & Material')
                        ->searchable()
                        ->sortable()
                        ->weight(FontWeight::Bold)
                        ->color('primary')
                        ->description(fn (MonitoringChemical $record): string => Str::limit($record->purchaseOrder?->description ?? '-', 45))
                        ->copyable()
                        ->copyMessage('Nomor PO disalin!'),

                    TextColumn::make('material_category')
                        ->label('Kategori')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'Karung' => 'warning',
                            'Chemical' => 'info',
                            'Lainnya' => 'success',
                            default => 'gray',
                        })
                        ->searchable(),

                    TextColumn::make('quantity')
                        ->label('Qty Datang')
                        ->sortable()
                        ->weight(FontWeight::Bold)
                        ->color(fn ($record) => $record->is_qty_tolerance ? 'danger' : 'gray')
                        ->formatStateUsing(function ($state, $record) {
                            $fmt = rtrim(rtrim(number_format((float) $state, 2, ',', '.'), '0'), ',');
                            $uoi = $record->purchaseOrder?->uoi ?? '';

                            return "{$fmt} {$uoi}";
                        })
                        ->description(fn (MonitoringChemical $record): string => $record->tahapan ?? 'Tanpa Tahapan'),
                ]),

                // 🌟 GRUP 2: PENGIRIMAN & QC
                ColumnGroup::make('Pengiriman & QC', [
                    TextColumn::make('qc_by')
                        ->label('Tujuan QC')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'ISTEK' => 'info',
                            'PPE' => 'indigo',
                            default => 'gray',
                        })
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make('do_number')
                        ->label('No. DO & Tgl Tiba')
                        ->searchable()
                        ->description(fn (MonitoringChemical $record) => $record->received_date?->format('d M Y') ?? '-'),
                ]),

                ColumnGroup::make('Dokumen & Simala', [
                    TextColumn::make('doc_status')
                        ->label('Status Dokumen')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'Completed' => 'success',
                            'Outstanding' => 'warning',
                            'Rejected' => 'danger',
                            default => 'gray',
                        })
                        ->icon(fn (string $state): string => match ($state) {
                            'Completed' => 'heroicon-m-check-circle',
                            'Outstanding' => 'heroicon-m-clock',
                            'Rejected' => 'heroicon-m-x-circle',
                            default => 'heroicon-m-document',
                        })
                        ->searchable(),

                    TextColumn::make('leadtime_coa')
                        ->label('Leadtime')
                        ->placeholder('Tidak Ada')
                        ->numeric()
                        ->suffix(' Hari')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: false),

                    TextColumn::make('tanggal_pengajuan_simala')
                        ->date('d M Y')
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('tanggal_pengambilan_sample')
                        ->date('d M Y')
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('tanggal_terbit_coa')
                        ->date('d M Y')
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),

                // 🌟 KOLOM TAMBAHAN (Tidak Digrupkan, ditaruh di akhir)
                TextColumn::make('location_id')
                    ->label('Lokasi ID')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_qty_tolerance')
                    ->label('Pakai Toleransi?')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('has_update_progress')
                    ->label('Update Progress?')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('receivedBy.name')
                    ->label('Penerima')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime('d M Y H:i')
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
