<?php

namespace App\Filament\Resources\MonitoringNpks\Schemas;

use App\Models\LocationReceiving;
use App\Models\MonitoringNpkDetail;
use App\Models\PurchaseOrderIssued;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class MonitoringNpkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Group::make()->schema([
                        self::getInformasiKedatanganGroup(),
                        // 🌟 GROUPING BARU: Daftar Material & Status SAP digabung di kolom kiri
                        Group::make([
                            self::getTrackingDokumenSection(), // Sisa dokumen NPK (COA, Laprima)
                        ]),
                    ]),

                    Group::make()->schema([
                        self::getDaftarMaterial(),
                        self::getStatusPoSapSection(),
                        self::getDataLainnyaFieldset(),
                        Hidden::make('created_by')
                            ->default(Auth::id()),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    protected static function getInformasiKedatanganGroup(): Group
    {
        return Group::make([
            Section::make('Informasi Kedatangan NPK')
                ->icon(Heroicon::OutlinedInformationCircle)
                ->description('Pilih nomor Purchase Order (PO) untuk menarik data material NPK dan mengaktifkan form pengisian.')
                ->schema([
                    self::getDataUtamaGrid(),
                ]),
        ]);
    }

    protected static function getDataUtamaGrid(): Grid
    {
        return Grid::make(2)->schema([
            Select::make('purchase_order_terbit_id')
                ->label('Purchase Order & Material')
                ->placeholder('Cari Nomor PO atau Deskripsi...')
                ->getSearchResultsUsing(fn (string $search): array => PurchaseOrderIssued::where('purchase_order_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->whereIn('material_type', ['ZFP', 'ZRM'])
                    ->limit(20)
                    ->get()
                    ->mapWithKeys(fn ($item) => [$item->id => "{$item->purchase_order_no}"])
                    ->toArray()
                )
                ->searchable()
                ->preload(false)
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    if (! $state) {
                        $set('monitoringNpkDetails', []);

                        return;
                    }

                    $poItem = PurchaseOrderIssued::find($state);
                    if ($poItem) {
                        // Cek riwayat terpakai di sistem Monitoring NPK
                        [$qtyPo, $netSaved] = static::computeNetForItem((int) $poItem->id, (string) $poItem->item_no);
                        $sisa = $qtyPo - $netSaved;

                        // Jika sudah habis, jangan masukkan ke form repeater
                        if ($sisa <= 0) {
                            $set('monitoringNpkDetails', []);

                            return;
                        }

                        $set('monitoringNpkDetails', [
                            [
                                'item_no' => $poItem->item_no,
                                'material_code' => $poItem->material_code,
                                'description' => $poItem->description,
                                'quantity' => null, // Biarkan user isi manual
                                'string' => $poItem->uoi,
                                'is_qty_tolerance' => false,
                                'location_id' => null,
                            ],
                        ]);
                    }
                }),

            EmptyState::make('Belum Ada PO yang Dipilih')
                ->description('Silakan cari dan pilih Nomor Purchase Order di atas untuk mulai mengisi form kedatangan.')
                ->icon(Heroicon::OutlinedDocumentMagnifyingGlass)
                ->columnSpanFull()
                ->visible(fn (Get $get, $record) => blank($get('purchase_order_terbit_id')) && $record === null),

            EmptyState::make('Semua item NPK dalam PO ini sudah diterima sepenuhnya.')
                ->description('Tidak ada sisa kuota material yang tersedia untuk diproses pada nomor PO ini.')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->contained(true)
                ->columnSpanFull()
                ->visible(fn (Get $get): bool => ! empty($get('purchase_order_terbit_id')) && empty($get('monitoringNpkDetails'))),

            TextInput::make('delivery_oder_number') // Sesuai typo db
                ->label('Nomor Surat Jalan / DO')
                ->placeholder('Masukkan No. Surat Jalan / DO')
                ->required()
                ->visible(fn (Get $get, $record) => filled($get('purchase_order_terbit_id')) || $record !== null),

            DatePicker::make('delivery_oder_delivery_date')
                ->label('Tanggal Berangkat (DO)')
                ->native(false)
                ->visible(fn (Get $get, $record) => filled($get('purchase_order_terbit_id')) || $record !== null)
                ->maxDate(now()),

            DatePicker::make('received_date')
                ->label('Tanggal Tiba di Gudang')
                ->native(false)
                ->default(now())
                ->maxDate(now())
                ->visible(fn (Get $get, $record) => filled($get('purchase_order_terbit_id')) || $record !== null)
                ->required(),

            Select::make('location_id')
                ->label('Lokasi Kedatangan Utama')
                ->options(LocationReceiving::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get, $record) => filled($get('purchase_order_terbit_id')) || $record !== null)
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $details = $get('monitoringNpkDetails') ?? [];
                    foreach ($details as $key => $detail) {
                        if (! $state) {
                            $set("monitoringNpkDetails.{$key}.location_id", null);

                            continue;
                        }
                        $set("monitoringNpkDetails.{$key}.location_id", $state);
                    }
                }),
        ]);
    }

    protected static function getDataLainnyaFieldset(): Section
    {
        return Section::make('Status Dokumen')
            ->visible(fn (Get $get, $record) => filled($get('purchase_order_terbit_id')) || $record !== null)
            ->schema([
                Hidden::make('doc_status')
                    ->default('Outstanding')
                    ->dehydrateStateUsing(function (Get $get) {
                        $laprima = $get('laprima_date');
                        $coa = $get('coa_date');
                        $po103 = $get('purchase_order_103_date');

                        return ($laprima && $coa && $po103) ? 'Completed' : 'Outstanding';
                    }),

                Grid::make(1)->schema([
                    TextEntry::make('doc_status_display')
                        ->label('Status Final NPK')
                        ->inlineLabel()
                        ->getStateUsing(fn (Get $get) => $get('doc_status') ?? 'Outstanding')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'Completed' => 'success',
                            'Outstanding' => 'warning',
                            default => 'gray',
                        })
                        ->icon(fn (string $state): string => match ($state) {
                            'Completed' => 'heroicon-m-check-circle',
                            'Outstanding' => 'heroicon-m-clock',
                            default => 'heroicon-m-document',
                        }),
                ]),
            ]);
    }

    public static function getDaftarMaterial(): Section
    {
        return Section::make('Daftar Material NPK')
            ->description('Rincian penerimaan material NPK berdasarkan PO yang dipilih.')
            ->visible(fn (Get $get, $record) => filled($get('purchase_order_terbit_id')) || $record !== null)
            ->schema([
                Repeater::make('monitoringNpkDetails')
                    ->hiddenLabel()
                    ->relationship('monitoringNpkDetails')
                    ->addable(false)
                    ->deletable(false) // Baris ditarik otomatis dari PO
                    ->reorderable(false)
                    ->hidden(fn (Get $get): bool => empty($get('monitoringNpkDetails')))
                    ->schema([
                        Grid::make(3)->schema([
                            Hidden::make('item_no'),
                            Hidden::make('string'),
                            Hidden::make('location_id'),

                            TextInput::make('material_code')
                                ->label('Kode Material')
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('description')
                                ->label('Deskripsi')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            TextInput::make('quantity')
                                ->label('Quantity Diterima')
                                ->numeric()
                                ->required()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->suffix(fn (Get $get) => $get('string'))
                                ->live(onBlur: true)
                                ->rules([
                                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $isToleranceActive = (bool) ($get('is_qty_tolerance') ?? false);
                                        if ($isToleranceActive) {
                                            return;
                                        }

                                        $poId = $get('../../purchase_order_terbit_id');
                                        $itemNo = $get('item_no');
                                        if (! $poId) {
                                            return;
                                        }

                                        $detailId = $get('id');
                                        [$qtyPo, $netSaved] = static::computeNetForItem((int) $poId, (string) $itemNo, $detailId);

                                        $currentInput = (float) str_replace(',', '', (string) $value);
                                        $totalAkanDiterima = $netSaved + $currentInput;
                                        $uoi = $get('string') ?? '';

                                        if ($totalAkanDiterima > $qtyPo) {
                                            $selisih = $totalAkanDiterima - $qtyPo;
                                            $fmtSelisih = number_format($selisih, 0, '.', ',');
                                            $fail("Input tidak valid! Kelebihan {$fmtSelisih} {$uoi}. Aktifkan 'Toleransi Qty' atau kurangi angka.");
                                        }
                                    },
                                ])
                                ->columnSpan(2)
                                ->helperText(function (Get $get, $record) {
                                    $itemNo = $get('item_no');
                                    $poId = $get('../../purchase_order_terbit_id');
                                    $uoi = $get('string') ?? '';

                                    if (! $poId || ! $itemNo) {
                                        return null;
                                    }

                                    [$qtyPo, $netSaved] = static::computeNetForItem((int) $poId, (string) $itemNo, $record?->id);

                                    $currentInput = (float) str_replace(',', '', (string) ($get('quantity') ?? 0));

                                    $fmtNetSaved = number_format($netSaved, 0, ',', '.');
                                    $sisaAwal = $qtyPo - $netSaved;

                                    $totalAkanDiterima = $netSaved + $currentInput;
                                    $sisaSetelahInput = $qtyPo - $totalAkanDiterima;

                                    $fmtQtyPo = number_format($qtyPo, 0, ',', '.');
                                    $fmtTotalAkanDiterima = number_format($totalAkanDiterima, 0, ',', '.');
                                    $fmtSisaAbsolut = number_format(abs($sisaSetelahInput), 0, ',', '.');

                                    if ($get('is_qty_tolerance') && $sisaSetelahInput < 0) {
                                        $statusInfo = "<span style='color: #d97706; font-weight: bold;'>Toleransi Aktif: {$fmtSisaAbsolut} {$uoi}</span>";
                                    } else {
                                        $colorSisa = $sisaSetelahInput < 0 ? '#dc2626' : ($sisaSetelahInput == 0 ? '#6b7280' : '#f59e0b');
                                        $statusLabel = $sisaSetelahInput < 0 ? 'OVER LIMIT' : 'Quantity Tersisa';
                                        $statusInfo = "<span style='color: {$colorSisa}; font-weight: bold;'>{$statusLabel}: {$fmtSisaAbsolut} {$uoi}</span>";
                                    }

                                    $colorAkanDiterima = ($totalAkanDiterima >= $qtyPo) ? '#16a34a' : ($totalAkanDiterima > 0 ? '#16a34a' : '#6b7280');
                                    $colorRiwayat = ($netSaved > 0) ? '#4090ff' : '#4b5563';

                                    return new HtmlString("
                                        <div style='margin-top: 4px; font-size: 0.8rem; line-height: 1.6;'>
                                            <span style='color: #4b5563;'>PO Terbit: <b>{$fmtQtyPo} {$uoi}</b></span> |
                                            <span style='color: {$colorRiwayat};'>Riwayat Terima: <b>{$fmtNetSaved} {$uoi}</b></span><br>
                                            <span style='color: {$colorAkanDiterima}; font-weight: 600;'>Riwayat + Input Saat Ini: <b>{$fmtTotalAkanDiterima} {$uoi}</b></span><br>
                                            {$statusInfo}
                                        </div>
                                    ");
                                }),

                            Toggle::make('is_qty_tolerance')
                                ->label('Toleransi Qty?')
                                ->live()
                                ->dehydrated(),
                        ]),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['quantity'] = (float) str_replace(',', '', (string) ($data['quantity'] ?? 0));

                        return $data;
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                        $data['quantity'] = (float) str_replace(',', '', (string) ($data['quantity'] ?? 0));

                        return $data;
                    }),
            ]);
    }

    // 🌟 SECTION BARU: Status SAP digeser ke bawah Daftar Material
    protected static function getStatusPoSapSection(): Section
    {
        return Section::make('Status Purchase Order (SAP)')
            ->icon(Heroicon::OutlinedServerStack)
            ->visible(fn (Get $get, $record) => filled($get('purchase_order_terbit_id')) || $record !== null)
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('stage')
                        ->label('Tahapan PO')
                        ->placeholder('Contoh: Tahap 1'),

                    TextInput::make('purchase_order_status')
                        ->label('Status PO')
                        ->placeholder('Contoh: Diterima / Proses'),

                    DatePicker::make('purchase_order_103_date')
                        ->label('Tanggal PO 103')
                        ->native(false),

                    DatePicker::make('purchase_order_status_a_date')
                        ->label('Tanggal Status A')
                        ->native(false),

                    DatePicker::make('purchase_order_status_b_date')
                        ->label('Tanggal Status B')
                        ->native(false),
                ]),

                FileUpload::make('purchase_order_status_a_files')
                    ->label('File Bukti Status A')
                    ->multiple()
                    ->directory('npk_po_documents')
                    ->columnSpanFull(),
            ]);
    }

    protected static function getTrackingDokumenSection(): Section
    {
        return Section::make('Tracking QC / Dokumen Lapangan')
            ->icon(Heroicon::OutlinedDocumentCheck)
            ->visible(fn (Get $get, $record) => filled($get('purchase_order_terbit_id')) || $record !== null)
            ->schema([
                DatePicker::make('sample_receivied_date')
                    ->label('Tanggal Terima Sample')
                    ->native(false),

                DatePicker::make('laprima_date')
                    ->label('Tanggal Laprima')
                    ->native(false),

                DatePicker::make('coa_date')
                    ->label('Tanggal Terbit COA')
                    ->native(false),

                FileUpload::make('coa_files')
                    ->label('Upload File COA')
                    ->multiple()
                    ->directory('npk_coa_documents')
                    ->columnSpanFull(),
            ]);
    }

    public static function computeNetForItem(int $poIssuedId, string $itemNo, $excludeId = null): array
    {
        $poItem = PurchaseOrderIssued::find($poIssuedId);
        if (! $poItem) {
            return [0, 0];
        }

        $qtyPo = (float) $poItem->qty_po;

        // Cari riwayat dari MonitoringNpkDetail yang PO-nya sama (via MonitoringNpk induk)
        $netSaved = (float) MonitoringNpkDetail::whereHas('MonitoringNpk', function ($q) use ($poIssuedId) {
            $q->where('purchase_order_terbit_id', $poIssuedId);
        })
            ->where('item_no', $itemNo)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->sum('quantity');

        return [$qtyPo, $netSaved];
    }
}
