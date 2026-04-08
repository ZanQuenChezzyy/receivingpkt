<?php

namespace App\Filament\Resources\DeliveryOrderReceiptDetails\Tables;

use App\Models\DeliveryOrderReceiptDetail;
use App\Models\PurchaseOrderIssued;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryOrderReceiptDetailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColumnGroup::make('Informasi Material', [
                    TextColumn::make('purchaseOrderIssued.purchase_order_no')
                        ->label('No. PO & Item')
                        ->description(fn ($record) => 'Item No: '.$record->item_no)
                        ->searchable()
                        ->icon(Heroicon::DocumentText)
                        ->iconColor('primary')
                        ->color('primary')
                        ->sortable(),

                    TextColumn::make('description')
                        ->label('Material')
                        ->description(fn ($record) => 'Kode: '.(! empty($record->material_code) ? $record->material_code : 'None'))
                        ->searchable(['description', 'material_code'])
                        ->weight(FontWeight::Bold)
                        ->icon('heroicon-m-cube')
                        ->iconColor('primary')
                        ->limit(30),
                ]),

                // ⚖️ GRUP 2: PENERIMAAN & NILAI
                ColumnGroup::make('Data Penerimaan', [
                    TextColumn::make('quantity')
                        ->label('Qty Diterima')
                        ->numeric()
                        ->suffix(fn ($record) => " {$record->uoi}")
                        ->badge() // Jadikan badge agar menonjol
                        ->color('success')
                        ->sortable(),

                    TextColumn::make('is_qty_tolerance')
                        ->label('Toleransi')
                        ->badge()
                        ->color(fn ($state) => $state ? 'danger' : 'success')
                        ->icon(fn ($state) => $state ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                        ->formatStateUsing(function ($state, $record) {
                            // 1. Jika False (Normal), tampilkan teks biasa
                            if (! $state) {
                                return 'Normal';
                            }

                            // 2. Jika True (Toleransi), hitung jumlah kelebihannya
                            $poId = $record->purchase_order_issued_id;
                            $itemNo = $record->item_no;

                            if ($poId && $itemNo) {
                                // Ambil target Qty dari PO
                                $poItem = PurchaseOrderIssued::find($poId);
                                $qtyPo = $poItem ? (float) $poItem->qty_po : 0;

                                // Hitung total Qty yang sudah masuk ke database untuk item ini
                                $totalReceived = DeliveryOrderReceiptDetail::where('purchase_order_issued_id', $poId)
                                    ->where('item_no', $itemNo)
                                    ->sum('quantity');

                                // Kalkulasi selisih (kelebihan)
                                $lebihan = $totalReceived - $qtyPo;

                                // Jika benar-benar berlebih, tampilkan angkanya
                                if ($lebihan > 0) {
                                    // Format angka agar rapi (misal: +1.500 EA)
                                    $fmtLebihan = number_format($lebihan, 0, ',', '.');

                                    return "Toleransi (+{$fmtLebihan} {$record->uoi})";
                                }
                            }

                            return 'Toleransi Aktif';
                        }),

                    TextColumn::make('total_amount_snapshot')
                        ->label('Total Nilai')
                        ->money('IDR', locale: 'id')
                        ->weight(FontWeight::SemiBold)
                        ->sortable(),
                ]),

                // 🏢 GRUP 3: LOKASI & SPESIFIKASI
                ColumnGroup::make('Penyimpanan & Spesifikasi', [
                    TextColumn::make('locationReceiving.name')
                        ->label('Lokasi Penyimpanan')
                        ->icon('heroicon-m-map-pin')
                        ->description(fn ($record) => $record->is_different_location ? 'Berbeda Lokasi' : 'Lokasi Utama')
                        ->color(fn ($record) => $record->is_different_location ? 'warning' : 'gray')
                        ->searchable()
                        ->sortable(),

                    // Menggabungkan MRP & Material Type agar hemat kolom
                    TextColumn::make('mrp_type')
                        ->label('MRP / Mat. Type')
                        ->description(fn ($record) => $record->material_type)
                        ->badge()
                        ->color('info')
                        ->searchable(),

                    // Menggabungkan ABC & AAC Indicator
                    TextColumn::make('abc_indicator')
                        ->label('ABC / AAC Ind.')
                        ->html() // Wajib agar HTML bisa dirender
                        ->getStateUsing(function ($record) {
                            // Ambil data ABC, jika kosong set jadi 'None'
                            $abc = ! empty($record->abc_indicator) ? $record->abc_indicator : 'None';

                            // Ambil data AAC, jika kosong set jadi '-'
                            $aac = ! empty($record->aac) ? $record->aac : '-';

                            // Rangkai menjadi HTML dengan menambahkan prefix ABC dan AAC
                            return "
                                <div class='font-medium text-gray-300'>ABC: {$abc}</div>
                                <div class='text-sm text-gray-400'>AAC: {$aac}</div>
                            ";
                        })
                        // HAPUS ->badge() dan ->color() karena styling sudah diatur lewat class HTML di atas
                        ->searchable(query: function ($query, string $search) {
                            $query->where('abc_indicator', 'like', "%{$search}%")
                                ->orWhere('aac', 'like', "%{$search}%");
                        }),
                ]),

                // 👤 GRUP 4: SISTEM (Bisa di-hide/toggle oleh user)
                ColumnGroup::make('Log Sistem', [
                    TextColumn::make('requisitioner')
                        ->label('Requisitioner')
                        ->icon('heroicon-m-user-circle')
                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('created_at')
                        ->label('Tgl Dibuat')
                        ->dateTime('d M Y H:i')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('updated_at')
                        ->label('Tgl Diupdate')
                        ->dateTime('d M Y H:i')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
