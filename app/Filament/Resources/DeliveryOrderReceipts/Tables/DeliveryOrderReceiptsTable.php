<?php

namespace App\Filament\Resources\DeliveryOrderReceipts\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class DeliveryOrderReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 📄 GRUP 1: INFORMASI DOKUMEN
                ColumnGroup::make('Informasi Dokumen', [
                    TextColumn::make('po_and_do')
                        ->label('Nomor PO & DO')
                        ->icon('heroicon-m-document-duplicate')
                        ->iconColor('primary')
                        ->color('primary')
                        ->weight(FontWeight::Bold)
                        ->getStateUsing(fn ($record) => $record->deliveryOrderReceiptDetails->first()?->purchaseOrderIssued?->purchase_order_no ?? 'Tanpa PO')
                        ->description(fn ($record) => new HtmlString("<span class='text-gray-500 font-medium'>DO: {$record->delivery_oder_no}</span>"))
                        ->searchable(query: function (Builder $query, string $search) {
                            $query->where('delivery_oder_no', 'like', "%{$search}%")
                                ->orWhereHas('deliveryOrderReceiptDetails.purchaseOrderIssued', function ($q) use ($search) {
                                    $q->where('purchase_order_no', 'like', "%{$search}%");
                                });
                        })
                        ->copyable()
                        ->copyMessage('Nomor PO disalin!')
                        ->sortable(),

                    TextColumn::make('document_code')
                        ->label('Kode Dokumen')
                        ->icon(Heroicon::QrCode)
                        ->iconColor('primary')
                        ->color('primary')
                        ->searchable()
                        ->copyable()
                        ->weight(FontWeight::SemiBold)
                        ->copyMessage('Kode dokumen disalin!')
                        ->limit(15)
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('received_date')
                        ->label('Tanggal Terima')
                        ->icon(Heroicon::CalendarDays)
                        ->iconColor('gray')
                        ->date('d F Y')
                        ->description(fn ($record) => Carbon::parse($record->received_date)->translatedFormat('l')) // Nama hari di bawahnya
                        ->sortable(),
                ]),

                // 📦 GRUP 2: DETAIL PENERIMAAN
                ColumnGroup::make('Detail Penerimaan', [
                    TextColumn::make('deskripsi_item')
                        ->label('Material')
                        ->icon(Heroicon::Cube)
                        ->iconColor('gray')
                        ->getStateUsing(function ($record) {
                            $details = $record->deliveryOrderReceiptDetails;
                            if ($details->isEmpty()) {
                                return ['Tidak ada item'];
                            }

                            return $details->map(function ($detail) {
                                return $detail->description;
                            })->toArray();
                        })
                        ->listWithLineBreaks() // Menampilkan data array berbaris ke bawah
                        ->bulleted() // Menambahkan titik (bullet)
                        ->limitList(2) // Batasi tampilan awal misal 2 baris agar tabel tidak terlalu panjang
                        ->expandableLimitedList() // Bisa diklik "View more"
                        ->searchable(query: function (Builder $query, string $search) {
                            $query->whereHas('deliveryOrderReceiptDetails', function ($q) use ($search) {
                                $q->where('description', 'like', "%{$search}%");
                            });
                        }),

                    TextColumn::make('quantity_item')
                        ->label('Quantity')
                        ->iconColor('success')
                        ->getStateUsing(function ($record) {
                            $details = $record->deliveryOrderReceiptDetails;
                            if ($details->isEmpty()) {
                                return ['-'];
                            }

                            return $details->map(function ($detail) {
                                $qty = number_format($detail->quantity, 0, ',', '.');

                                return "{$qty} {$detail->uoi}";
                            })->toArray();
                        })
                        ->listWithLineBreaks() // Harus sama persis dengan kolom sebelahnya
                        ->limitList(2)
                        ->expandableLimitedList()
                        ->toggleable(isToggledHiddenByDefault: true)
                        ->color('success') // Memberikan warna hijau pada teks quantity agar kontras
                        ->weight(FontWeight::SemiBold),

                    TextColumn::make('lokasi')
                        ->label('Lokasi')
                        ->icon('heroicon-m-map-pin')
                        ->getStateUsing(fn ($record) => $record->deliveryOrderReceiptDetails->first()?->locationReceiving?->name ?? 'Belum Diatur')
                        ->badge()
                        ->color('info'),

                    // Dipindahkan ke sini dari Log Sistem
                    TextColumn::make('receivedBy.name')
                        ->label('Penerima')
                        ->icon(Heroicon::User)
                        ->iconColor('warning')
                        ->color('warning')
                        ->badge()
                        ->searchable()
                        ->sortable(),
                ]),

                // ⚙️ GRUP 3: STATUS & TAHAPAN
                ColumnGroup::make('Status & Tahapan', [
                    TextColumn::make('stage')
                        ->label('Termin / Tahapan')
                        ->placeholder('Tidak Ada Tahapan')
                        ->badge()
                        ->default('') // Wajib ditambah agar null tetap diproses
                        ->formatStateUsing(function ($state) {
                            if (empty($state)) {
                                return 'Default';
                            }
                            if (str_contains(strtoupper($state), 'DOF')) {
                                return 'Surat DOF';
                            }

                            return $state; // Untuk Termin 1, Termin 2, dsb.
                        })
                        ->color(function ($state) {
                            if (empty($state)) {
                                return 'success';
                            }
                            if (str_contains(strtoupper($state), 'DOF')) {
                                return 'info';
                            }
                            if (str_contains(strtoupper($state), 'TERMIN')) {
                                return 'warning';
                            }

                            return 'gray';
                        })
                        ->icon(function ($state) {
                            if (empty($state)) {
                                return 'heroicon-m-check-circle';
                            }
                            if (str_contains(strtoupper($state), 'DOF')) {
                                return 'heroicon-m-document-duplicate';
                            }
                            if (str_contains(strtoupper($state), 'TERMIN')) {
                                return 'heroicon-m-chart-pie';
                            }

                            return 'heroicon-m-tag';
                        })
                        ->searchable(),

                    TextColumn::make('post_103')
                        ->label('Status POST 103')
                        ->badge()
                        ->default(false) // Wajib ditambah agar null tetap dirender sebagai 'Belum Post'
                        ->formatStateUsing(fn ($state) => $state ? 'Posted 103' : 'Belum Post')
                        ->description(fn ($record) => $record->post_103 ? Carbon::parse($record->post_103)->format('d M Y - H:i') : 'Menunggu aksi')
                        ->color(fn ($state) => $state ? 'success' : 'gray')
                        ->icon(fn ($state) => $state ? 'heroicon-m-check-badge' : 'heroicon-m-clock')
                        ->toggleable(isToggledHiddenByDefault: true)
                        ->sortable(),
                ]),

                // 👤 GRUP 4: LOG SISTEM (Bisa disembunyikan user)
                ColumnGroup::make('Log Sistem', [
                    TextColumn::make('createdBy.name')
                        ->label('Dibuat Oleh')
                        ->icon('heroicon-m-computer-desktop')
                        ->color('gray')
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('created_at')
                        ->label('Tgl Dibuat')
                        ->dateTime('d M Y H:i')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('updated_at')
                        ->label('Tgl Diperbarui')
                        ->dateTime('d M Y H:i')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('post_103_action')
                    ->label('Post 103')
                    ->icon(Heroicon::DocumentCheck)
                    ->color('success')
                    ->button()
                    ->outlined()
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Post 103')
                    ->modalDescription('Apakah Anda yakin ingin melakukan Post 103 pada dokumen ini? Tanggal hari ini akan tercatat sebagai tanggal post.')
                    ->modalSubmitActionLabel('Ya, Post Sekarang')
                    ->hidden(fn ($record): bool => $record->post_103 !== null)
                    ->action(function ($record) {
                        $record->update([
                            'post_103' => Carbon::now(),
                        ]);

                        Notification::make()
                            ->title('Berhasil!')
                            ->body('Tanggal Post 103 berhasil dicatat.')
                            ->success()
                            ->send();
                    }),
                Action::make('undo_post_103_action')
                    ->label('Batal Post')
                    ->icon(Heroicon::ArrowUturnLeft)
                    ->color('danger')
                    ->button()
                    ->outlined()
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Post 103')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan Post 103? Data tanggal post sebelumnya akan dihapus dari dokumen ini.')
                    ->modalSubmitActionLabel('Ya, Batalkan')
                    ->hidden(fn ($record): bool => $record->post_103 === null)
                    ->action(function ($record) {
                        $record->update([
                            'post_103' => null,
                        ]);

                        Notification::make()
                            ->title('Dibatalkan!')
                            ->body('Status Post 103 berhasil ditarik kembali.')
                            ->warning()
                            ->send();
                    }),
                ActionGroup::make([
                    ViewAction::make()
                        ->color('gray')
                        ->slideOver(),
                    EditAction::make()
                        ->color('info')
                        ->slideOver(),
                    DeleteAction::make()
                        ->requiresConfirmation(),
                ])
                    ->label('')
                    ->icon(Heroicon::EllipsisHorizontal)
                    ->size(Size::Small)
                    ->color('info')
                    ->outlined()
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada Dokumen Penerimaan')
            ->emptyStateDescription('Buat dokumen penerimaan barang baru dari PO yang tersedia.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
