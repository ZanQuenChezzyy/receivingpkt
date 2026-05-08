<?php

namespace App\Filament\Resources\DeliveryOrderReceipts\Schemas;

use App\Models\DeliveryOrderReceipt;
use App\Models\DeliveryOrderReceiptDetail;
use App\Models\LocationReceiving;
use App\Models\PurchaseOrderIssued;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\GridDirection;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class DeliveryOrderReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::getInformasiKedatanganGroup(),
                self::getDaftarMaterial(),
            ]);
    }

    protected static function getInformasiKedatanganGroup(): Group
    {
        return Group::make([
            self::getModePenerimaanField(),
            Section::make('Informasi Kedatangan Barang')
                ->icon(Heroicon::OutlinedInformationCircle)
                ->description('Pilih nomor Purchase Order (PO) untuk menarik data material dan mengaktifkan form pengisian.')
                ->schema([
                    self::getDataUtamaGrid(),
                    self::getTerminGroup(),
                    self::getDataLainnyaFieldset(),
                ]),
        ]);
    }

    protected static function getModePenerimaanField(): Section
    {
        return Section::make()->schema([
            ToggleButtons::make('receipt_mode')
                ->label('Metode Penerimaan Material')
                ->options([
                    'default' => 'PENERIMAAN DEFAULT',
                    'termin' => 'TERMIN',
                    'dof' => 'SURAT DOF',
                ])
                ->colors([
                    'default' => Color::Blue,
                    'termin' => Color::Yellow,
                    'dof' => Color::Orange,
                ])
                ->icons([
                    'default' => Heroicon::DocumentText,
                    'termin' => Heroicon::ClipboardDocumentCheck,
                    'dof' => Heroicon::ClipboardDocumentList,
                ])
                ->gridDirection(GridDirection::Row)
                ->default('default')
                ->inline()
                // ->inlineLabel()
                ->live()
                ->dehydrated(false)
                ->columnSpanFull()
                // 🔒 KUNCI MASTER: Mengunci toggle jika is_mode_locked bernilai true
                ->disabled(fn (Get $get) => empty($get('search_po')) || $get('is_mode_locked') === true)
                ->afterStateHydrated(function (ToggleButtons $component, $record, Set $set) {
                    // Logika ketika masuk halaman Edit (Selalu Terkunci!)
                    if ($record) {
                        $stage = strtoupper($record->stage ?? '');

                        if (str_contains($stage, 'TERMIN')) {
                            $component->state('termin');
                        } elseif (str_contains($stage, 'DOF')) {
                            $component->state('dof');
                        } else {
                            $component->state('default');
                        }

                        $set('is_mode_locked', true);
                    }
                })
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    if ($state === 'termin') {
                        $set('stage', 'TERMIN 1');
                        $set('termin_percentage', null);
                        $set('dof_number', null);
                        self::updateDocumentCode($set, $get);
                    } else {
                        $set('termin_percentage', null);

                        if ($state === 'dof') {
                            $set('stage', 'SURAT-DOF');
                        } else {
                            $set('stage', null);
                            $set('dof_number', null);
                        }

                        self::updateDocumentCode($set, $get);

                        $details = $get('deliveryOrderReceiptDetails') ?? [];
                        foreach ($details as $key => $detail) {
                            $set("deliveryOrderReceiptDetails.{$key}.is_qty_tolerance", false);

                            $poId = $detail['purchase_order_issued_id'] ?? null;
                            $itemNo = $detail['item_no'] ?? null;

                            if ($poId && $itemNo) {
                                [$qtyPo, $netSaved] = static::computeNetForItem((int) $poId, (string) $itemNo);
                                $sisa = $qtyPo - $netSaved;

                                $set("deliveryOrderReceiptDetails.{$key}.quantity", $sisa);
                                $unitPrice = (float) ($detail['unit_price'] ?? 0);
                                $set("deliveryOrderReceiptDetails.{$key}.total_amount_snapshot", $sisa * $unitPrice);
                            }
                        }
                    }
                }),
        ]);
    }

    protected static function getDataUtamaGrid(): Grid
    {
        return Grid::make(2)->schema([
            Select::make('search_po')
                ->label('Purchase Order')
                ->placeholder('Pilih Nomor Purchase Order')
                ->searchable()
                ->preload(false)
                ->afterStateHydrated(function (Select $component, $record) {
                    if ($record) {
                        $firstDetail = $record->deliveryOrderReceiptDetails()->first();
                        if ($firstDetail) {
                            $poItem = PurchaseOrderIssued::find($firstDetail->purchase_order_issued_id);
                            if ($poItem) {
                                $component->state($poItem->purchase_order_no);
                            }
                        }
                    }
                })
                ->noSearchResultsMessage('Purchase Order tidak ditemukan.')
                ->getSearchResultsUsing(fn (string $search): array => PurchaseOrderIssued::where('purchase_order_no', 'like', "%{$search}%")
                    ->limit(10)
                    ->pluck('purchase_order_no', 'purchase_order_no')
                    ->toArray()
                )
                ->getOptionLabelUsing(fn ($value): ?string => $value)
                ->live()
                ->afterStateUpdated(function (Set $set, $state, Get $get) {
                    if (! $state) {
                        $set('deliveryOrderReceiptDetails', []);
                        $set('source_type', null);
                        $set('document_code', null);
                        $set('is_mode_locked', false); // Buka kunci jika PO dihapus

                        return;
                    }

                    // 🔍 CEK RIWAYAT PO DI DATABASE (Mencari Receipt Pertama dari PO ini)
                    $previousReceipt = DeliveryOrderReceipt::whereHas('deliveryOrderReceiptDetails.purchaseOrderIssued', function ($q) use ($state) {
                        $q->where('purchase_order_no', $state);
                    })->first();

                    // 🧠 LOGIKA KUNCI OTOMATIS BERDASARKAN RIWAYAT
                    if ($previousReceipt) {
                        $prevStage = strtoupper($previousReceipt->stage ?? '');

                        if (str_contains($prevStage, 'TERMIN')) {
                            $set('receipt_mode', 'termin');
                            $set('stage', null); // Agar user pilih Termin X yang baru
                        } elseif (str_contains($prevStage, 'DOF')) {
                            $set('receipt_mode', 'dof');
                            $set('stage', 'SURAT-DOF');
                        } else {
                            $set('receipt_mode', 'default');
                            $set('stage', null);
                        }

                        $set('is_mode_locked', true); // Kunci Mati!
                        $set('termin_percentage', null);
                    } else {
                        // Jika PO ini murni baru pertama kali datang
                        $set('receipt_mode', 'default');
                        $set('is_mode_locked', false); // Bebaskan user memilih
                        $set('stage', null);
                        $set('termin_percentage', null);
                    }

                    $allPoItems = PurchaseOrderIssued::where('purchase_order_no', $state)->get();
                    $filteredItems = $allPoItems->map(function ($item) {
                        [$qtyPo, $netSaved] = static::computeNetForItem((int) $item->id, (string) $item->item_no);
                        $sisa = $qtyPo - $netSaved;

                        if ($sisa <= 0) {
                            return null;
                        }

                        $unitPrice = $qtyPo > 0 ? ((float) $item->total_amount_in_lc / $qtyPo) : 0;

                        return [
                            'purchase_order_issued_id' => $item->id,
                            'material_code' => $item->material_code,
                            'description' => $item->description,
                            'uoi' => $item->uoi,
                            'quantity' => $sisa,
                            'item_no' => $item->item_no,
                            'mrp_type' => $item->mrp_type,
                            'material_type' => $item->material_type,
                            'aac' => $item->aac,
                            'abc_indicator' => $item->abc_indicator,
                            'requisitioner' => $item->requisitioner,
                            'unit_price' => $unitPrice,
                            'total_amount_snapshot' => $sisa * $unitPrice,
                            'location_id' => null,
                            'is_different_location' => false,
                        ];
                    })->filter()->values()->toArray();

                    $set('deliveryOrderReceiptDetails', $filteredItems);

                    if ($allPoItems->isNotEmpty()) {
                        $matType = $allPoItems->first()->material_type;
                        $sourceType = match ($matType) {
                            'ZSP' => 'Sparepart',
                            'ZFP', 'ZRM' => 'Bahan Baku NPK',
                            'ZSM', 'ZPM' => 'Chemical/Karung',
                            default => 'Sparepart',
                        };
                        $set('source_type', $sourceType);
                        self::updateDocumentCode($set, $get);
                    }
                }),

            TextInput::make('delivery_oder_no')
                ->label('No. Surat Jalan / Nomor DO')
                ->placeholder('Masukkan No. Surat Jalan')
                ->maxLength(16)
                ->minLength(3)
                ->unique(ignoreRecord: true)
                ->disabled(fn (Get $get) => empty($get('search_po')))
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateDocumentCode($set, $get))
                ->required(),

            DatePicker::make('received_date')
                ->label('Tanggal Terima')
                ->placeholder('Pilih Tanggal Terima')
                ->native(false)
                ->maxDate(now())
                ->minDate(now()->addDays(-30))
                ->disabled(fn (Get $get) => empty($get('search_po')))
                ->live()
                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateDocumentCode($set, $get))
                ->required(),

            Select::make('received_by')
                ->label('Diterima Oleh')
                ->placeholder('Pilih Penerima')
                ->relationship('receivedBy', 'name')
                ->default(Auth::id())
                ->preload()
                ->searchable()
                ->disabled(fn (Get $get) => empty($get('search_po')))
                ->required(),

            Select::make('global_location_id')
                ->label('Lokasi Receiving')
                ->placeholder('Pilih Lokasi')
                ->options(LocationReceiving::pluck('name', 'id'))
                ->searchable()
                ->live()
                ->disabled(fn (Get $get) => empty($get('search_po')))
                ->afterStateHydrated(function (Select $component, $record) {
                    if ($record) {
                        $firstDetail = $record->deliveryOrderReceiptDetails()->first();
                        if ($firstDetail && $firstDetail->location_id) {
                            $component->state($firstDetail->location_id);
                        }
                    }
                })
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $details = $get('deliveryOrderReceiptDetails') ?? [];
                    foreach ($details as $key => $detail) {
                        if (! $state) {
                            $set("deliveryOrderReceiptDetails.{$key}.location_id", null);

                            continue;
                        }
                        if (! ($detail['is_different_location'] ?? false)) {
                            $set("deliveryOrderReceiptDetails.{$key}.location_id", $state);
                        }
                    }
                })
                ->columnSpan(fn (Get $get) => $get('receipt_mode') === 'termin' ? 2 : 1),

            TextInput::make('stage')
                ->label('Tahapan / Keterangan (Opsional)')
                ->placeholder('Contoh: TAHAP 1')
                ->disabled(fn (Get $get) => empty($get('search_po')))
                ->visible(fn (Get $get) => $get('receipt_mode') !== 'termin')
                ->readOnly(fn (Get $get) => $get('receipt_mode') === 'dof')
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateDocumentCode($set, $get)),

            TextInput::make('dof_number')
                ->label('Nomor Surat DOF')
                ->placeholder('Masukkan Nomor Surat DOF')
                ->required(fn (Get $get) => $get('receipt_mode') === 'dof') // Wajib jika mode DOF
                ->visible(fn (Get $get) => $get('receipt_mode') === 'dof')  // Muncul jika mode DOF
                ->maxLength(50)
                ->columnSpan(1),
        ]);
    }

    protected static function getTerminGroup(): Group
    {
        return Group::make()->schema([
            Select::make('stage')
                ->label('Pilih Termin')
                ->placeholder('Pilih Termin')
                ->options(function () {
                    $options = [];
                    for ($i = 1; $i <= 20; $i++) {
                        $options["TERMIN {$i}"] = "TERMIN {$i}";
                    }

                    return $options;
                })
                ->native(false)
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set, Get $get) => self::updateDocumentCode($set, $get)),

            TextInput::make('termin_percentage')
                ->label('Persentase Qty (%)')
                ->numeric()
                ->suffix('%')
                ->minValue(1)
                ->maxValue(100)
                ->placeholder('Contoh: 20')
                ->required()
                ->rules([
                    // 🌟 TAMBAHKAN $record DI SINI
                    fn (Get $get, $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $valString = str_replace(',', '.', (string) $value);

                        if (! is_numeric($valString)) {
                            $fail('Format persentase tidak valid. Masukkan angka (contoh: 15,5).');

                            return;
                        }

                        $percentageInput = (float) $valString;

                        if ($percentageInput <= 0 || $percentageInput > 100) {
                            $fail('Persentase harus antara 0.01 hingga 100%.');

                            return;
                        }

                        $details = $get('deliveryOrderReceiptDetails') ?? [];

                        foreach ($details as $detail) {
                            $poId = $detail['purchase_order_issued_id'] ?? null;
                            $itemNo = $detail['item_no'] ?? null;

                            // 🌟 AMBIL ID DETAIL UNTUK DIKECUALIKAN SAAT EDIT
                            $detailId = $detail['id'] ?? null;

                            if ($poId && $itemNo) {
                                // 🌟 MASUKKAN $detailId SEBAGAI ARGUMEN KETIGA
                                [$qtyPo, $netSaved] = static::computeNetForItem((int) $poId, (string) $itemNo, $detailId);

                                $sisaQty = $qtyPo - $netSaved;
                                $qtyYangDiminta = ($qtyPo * $percentageInput) / 100;

                                if ($qtyYangDiminta > $sisaQty) {
                                    $maxPercent = round(($sisaQty / $qtyPo) * 100, 2);
                                    $matCode = $detail['material_code'] ?? 'Item ini';
                                    $fail("Gagal! Termin {$matCode} melebihi batas. Sisa maksimal hanya {$maxPercent}%.");
                                    break;
                                }
                            }
                        }
                    },
                ])
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $valString = str_replace(',', '.', (string) $state);
                    $percentage = (float) $valString;

                    if ($percentage <= 0) {
                        return;
                    }

                    $details = $get('deliveryOrderReceiptDetails') ?? [];
                    foreach ($details as $key => $detail) {
                        $poId = $detail['purchase_order_issued_id'] ?? null;
                        if ($poId) {
                            $poItem = PurchaseOrderIssued::find($poId);
                            if ($poItem) {
                                $qtyPo = (float) $poItem->qty_po;
                                $calcQty = ($qtyPo * $percentage) / 100;

                                $set("deliveryOrderReceiptDetails.{$key}.quantity", $calcQty);

                                $unitPrice = (float) ($detail['unit_price'] ?? 0);
                                $set("deliveryOrderReceiptDetails.{$key}.total_amount_snapshot", $calcQty * $unitPrice);
                            }
                        }
                    }
                }),
        ])
            ->columns(2)
            ->columnSpanFull()
            ->disabled(fn (Get $get) => empty($get('search_po')))
            ->visible(fn (Get $get) => $get('receipt_mode') === 'termin');
    }

    protected static function getDataLainnyaFieldset(): Section
    {
        return Section::make('Data Lainnya')
            ->schema([
                Hidden::make('is_mode_locked')->dehydrated(false)->default(false),

                Hidden::make('source_type'),
                Hidden::make('document_code'),
                Hidden::make('status')->default('Diterima'),

                Textarea::make('description')
                    ->label('Keterangan / Deskripsi Tambahan')
                    ->placeholder('Masukkan keterangan atau catatan khusus untuk DO ini...')
                    ->autosize()
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(fn (Get $get) => empty($get('search_po'))),

                Select::make('created_by')
                    ->label('Dibuat Oleh')
                    ->relationship('createdBy', 'name')
                    ->default(Auth::id())
                    ->dehydrated()
                    ->disabled(fn () => Auth::user()->hasRole('Administrator') !== true),

                DatePicker::make('post_103')
                    ->label('Tanggal Post 103 (SAP)')
                    ->placeholder('Belum di-Post')
                    ->native(false)
                    ->disabled(fn () => Auth::user()->hasRole('Administrator') !== true),

                Grid::make(3)->schema([
                    TextEntry::make('document_code_view')
                        ->label('Kode Dokumen')
                        ->state(fn (Get $get) => $get('document_code'))
                        ->weight(FontWeight::Bold)
                        ->color('primary')
                        ->copyable()
                        ->icon(Heroicon::QrCode)
                        ->iconColor('primary')
                        ->limit(10)
                        ->copyMessage('Kode disalin!')
                        ->placeholder('Otomatis Terisi'),
                    TextEntry::make('source_type_view')
                        ->label('Tipe Source')
                        ->state(fn (Get $get) => $get('source_type'))
                        ->weight(FontWeight::Bold)
                        ->placeholder('Otomatis Terisi'),

                    TextEntry::make('status_view')
                        ->label('Status')
                        ->state(fn ($record) => $record ? ($record->status ?: 'Diterima') : 'Draft')
                        ->badge()
                        ->color(fn ($state) => $state === 'Draft' ? 'warning' : 'success')
                        ->icon(fn ($state) => $state === 'Draft' ? Heroicon::PencilSquare : Heroicon::CheckCircle),
                ])->columnSpanFull(),
            ])
            ->columns(2)
            ->columnSpanFull()
            ->collapsible()
            ->description('Informasi tambahan yang diisi otomatis oleh sistem.')
            ->disabled(fn (Get $get) => empty($get('search_po')));
    }

    public static function getDaftarMaterial(): Section
    {
        return Section::make('Daftar Material dalam DO')
            ->description(function (Get $get, $record): string {
                $searchPo = $get('search_po');

                if ($record) {
                    return 'Daftar Material untuk Penerimaan Barang';
                }

                return empty($searchPo)
                    ? 'Silakan pilih Nomor PO terlebih dahulu untuk mengisi daftar material.'
                    : "Daftar Material untuk PO - {$searchPo}";
            })
            ->schema([
                Repeater::make('deliveryOrderReceiptDetails')
                    ->label('Detail Penerimaan Material')
                    ->relationship('deliveryOrderReceiptDetails')
                    ->itemLabel(fn ($state) => $state['description'] ?? 'Item')
                    ->minItems(1)
                    ->hidden(fn (Get $get): bool => empty($get('deliveryOrderReceiptDetails')))
                    ->addable(false)
                    ->reorderable(false)
                    ->deletable(true)
                    ->schema([
                        Grid::make(3)->schema([
                            Hidden::make('purchase_order_issued_id'),
                            Hidden::make('item_no'),
                            Hidden::make('mrp_type'),
                            Hidden::make('material_type'),
                            Hidden::make('aac'),
                            Hidden::make('abc_indicator'),
                            Hidden::make('requisitioner'),
                            Hidden::make('unit_price'),
                            Hidden::make('total_amount_snapshot'),
                            Hidden::make('uoi'),

                            TextInput::make('material_code')
                                ->label('Kode Material')
                                ->placeholder('Kode Material')
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
                                ->readOnly(fn (Get $get): bool => $get('../../receipt_mode') === 'termin')
                                ->hint(fn (Get $get) => $get('../../receipt_mode') === 'termin' ? 'Otomatis' : null)
                                ->rules([
                                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $isToleranceActive = (bool) ($get('is_qty_tolerance') ?? false);

                                        if ($isToleranceActive) {
                                            return;
                                        }

                                        $poId = $get('purchase_order_issued_id');
                                        $itemNo = $get('item_no');

                                        if (! $poId) {
                                            return;
                                        }

                                        $detailId = $get('id');

                                        [$qtyPo, $netSaved] = static::computeNetForItem((int) $poId, (string) $itemNo, $detailId);

                                        $currentInput = (float) $value;
                                        $totalAkanDiterima = $netSaved + $currentInput;
                                        $uoi = $get('uoi') ?? '';

                                        if ($totalAkanDiterima > $qtyPo) {
                                            $selisih = $totalAkanDiterima - $qtyPo;

                                            $fmtSelisih = number_format($selisih, 0, '.', ',');

                                            $fail("Input tidak valid! Kelebihan {$fmtSelisih} {$uoi}. Aktifkan 'Toleransi Qty' atau kurangi angka.");
                                        }
                                    },
                                ])
                                ->validationAttribute('Quantity')
                                ->live(onBlur: true)
                                ->columnSpan(2)
                                ->suffix(fn (Get $get): string => $get('uoi') ?? '')
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $quantity = (float) $state;
                                    $unitPrice = (float) ($get('unit_price') ?? 0);

                                    $newTotalAmount = $quantity * $unitPrice;

                                    $set('total_amount_snapshot', $newTotalAmount);
                                })
                                ->helperText(function (Get $get, $record) {
                                    $itemNo = $get('item_no');
                                    $poId = $get('purchase_order_issued_id');
                                    $uoi = $get('uoi') ?? 'EA';

                                    if (! $poId || ! $itemNo) {
                                        return null;
                                    }

                                    // 1. Ambil riwayat murni dari database (Kecuali baris yang sedang diedit ini)
                                    [$qtyPo, $netSaved] = static::computeNetForItem((int) $poId, (string) $itemNo, $record?->id);

                                    // 2. Ambil input yang sedang diketik user di kotak "quantity" saat ini
                                    $currentInput = (float) str_replace(',', '', (string) ($get('quantity') ?? 0));

                                    // 3. Kalkulasi
                                    // Diterima sebelumnya SAJA
                                    $fmtNetSaved = number_format($netSaved);

                                    // Sisa kuota SEBELUM input saat ini dimasukkan
                                    $sisaAwal = $qtyPo - $netSaved;
                                    $fmtSisaAwal = number_format($sisaAwal);

                                    // Sisa kuota SETELAH input saat ini dimasukkan (untuk validasi visual)
                                    $totalAkanDiterima = $netSaved + $currentInput;
                                    $sisaSetelahInput = $qtyPo - $totalAkanDiterima;

                                    $fmtQtyPo = number_format($qtyPo);
                                    $fmtTotalAkanDiterima = number_format($totalAkanDiterima);
                                    $fmtSisaAbsolut = number_format(abs($sisaSetelahInput));

                                    // 4. Logika Pewarnaan & Peringatan
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

                            Select::make('location_id')
                                ->label('Lokasi')
                                ->placeholder('Pilih Lokasi')
                                ->relationship('locationReceiving', 'name')
                                ->native(false)
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->disabled(fn (Get $get): bool => ! ($get('is_different_location') ?? false))
                                ->dehydrated()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $globalLoc = $get('../../global_location_id');

                                    if ($state != $globalLoc) {
                                        $set('is_different_location', true);
                                    } else {
                                        $set('is_different_location', false);
                                    }
                                }),

                            Toggle::make('is_different_location')
                                ->label('Beda Lokasi?')
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    if (! $state) {
                                        $globalLoc = $get('../../global_location_id');
                                        $set('location_id', $globalLoc);
                                    }
                                }),
                            Toggle::make('is_qty_tolerance')
                                ->label('Toleransi Qty?')
                                ->visible(fn (Get $get): bool => $get('../../receipt_mode') === 'default')
                                ->live()
                                ->dehydrated(),
                        ]),
                    ])
                    ->addable(false)
                    ->reorderable(false)
                    ->deletable()
                    ->defaultItems(0)
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        // 🌟 Ubah koma (,) menjadi titik (.) agar jadi pecahan, lalu cast ke float
                        $quantity = (float) str_replace(',', '.', (string) ($data['quantity'] ?? 0));

                        $poId = $data['purchase_order_issued_id'] ?? null;
                        $itemNo = $data['item_no'] ?? null;

                        if ($poId && $itemNo) {
                            $poItem = PurchaseOrderIssued::find($poId);

                            $unitPrice = ($poItem && $poItem->qty_po > 0)
                                ? ((float) $poItem->total_amount_in_lc / (float) $poItem->qty_po)
                                : 0;

                            [$qtyPo, $netSaved] = static::computeNetForItem((int) $poId, (string) $itemNo);
                            $sisaKuota = $qtyPo - $netSaved;

                            if ($quantity > $sisaKuota) {
                                $qtyBisaDibayar = max(0, $sisaKuota);
                                $data['total_amount_snapshot'] = $qtyBisaDibayar * $unitPrice;
                            } else {
                                $data['total_amount_snapshot'] = $quantity * $unitPrice;
                            }

                            $data['unit_price'] = $unitPrice;
                        } else {
                            $unitPrice = (float) ($data['unit_price'] ?? 0);
                            $data['total_amount_snapshot'] = $quantity * $unitPrice;
                        }

                        $data['quantity'] = $quantity;

                        return $data;
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $record): array {
                        // 🌟 Ubah koma (,) menjadi titik (.) agar jadi pecahan, lalu cast ke float
                        $quantity = (float) str_replace(',', '.', (string) ($data['quantity'] ?? 0));

                        $poId = $data['purchase_order_issued_id'] ?? null;
                        $itemNo = $data['item_no'] ?? null;
                        $excludeId = $record ? $record->id : null;

                        if ($poId && $itemNo) {
                            $poItem = PurchaseOrderIssued::find($poId);

                            $unitPrice = ($poItem && $poItem->qty_po > 0)
                                ? ((float) $poItem->total_amount_in_lc / (float) $poItem->qty_po)
                                : 0;

                            [$qtyPo, $netSaved] = static::computeNetForItem((int) $poId, (string) $itemNo, $excludeId);
                            $sisaKuota = $qtyPo - $netSaved;

                            if ($quantity > $sisaKuota) {
                                $qtyBisaDibayar = max(0, $sisaKuota);
                                $data['total_amount_snapshot'] = $qtyBisaDibayar * $unitPrice;
                            } else {
                                $data['total_amount_snapshot'] = $quantity * $unitPrice;
                            }

                            $data['unit_price'] = $unitPrice;
                        } else {
                            $unitPrice = (float) ($data['unit_price'] ?? 0);
                            $data['total_amount_snapshot'] = $quantity * $unitPrice;
                        }

                        $data['quantity'] = $quantity;

                        return $data;
                    }),

                EmptyState::make('Belum ada Nomor PO yang dipilih')
                    ->description('Silakan cari dan pilih Nomor PO pada bagian Informasi Kedatangan untuk menampilkan daftar material.')
                    ->icon(Heroicon::OutlinedCursorArrowRays)
                    ->contained(true)
                    ->visible(fn (Get $get, $record): bool => filled($get('search_po')) === false && $record === null),

                EmptyState::make('Semua item dalam PO ini sudah diterima sepenuhnya.')
                    ->description('Tidak ada sisa kuota material yang tersedia untuk diproses pada nomor PO ini.')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->contained(true)
                    ->visible(fn (Get $get): bool => ! empty($get('search_po')) && empty($get('deliveryOrderReceiptDetails'))),
            ]);
    }

    public static function computeNetForItem(int $poIssuedId, string $itemNo, $excludeId = null): array
    {
        $poItem = PurchaseOrderIssued::find($poIssuedId);
        if (! $poItem) {
            return [0, 0, 0, 0];
        }

        $qtyPo = (float) $poItem->qty_po;

        $netSaved = (float) DeliveryOrderReceiptDetail::where('purchase_order_issued_id', $poIssuedId)
            ->where('item_no', $itemNo)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->sum('quantity');

        return [$qtyPo, $netSaved];
    }

    public static function updateDocumentCode(Set $set, Get $get): void
    {
        $poNo = $get('search_po');
        $doNo = $get('delivery_oder_no');

        $date = $get('received_date')
    ? Carbon::parse($get('received_date'))->format('dmY') : '';

        $details = $get('deliveryOrderReceiptDetails') ?? [];
        $itemNo = '';
        if (is_array($details) && count($details) > 0) {
            $firstItem = reset($details);
            $itemNo = $firstItem['item_no'] ?? '';
        }

        $stage = $get('stage');

        $parts = array_filter([$poNo, $itemNo, $doNo, $date, $stage]);

        if (! empty($parts)) {
            $joinedString = implode('-', $parts);
            $upperString = strtoupper($joinedString);
            $finalDocumentCode = str_replace(' ', '', $upperString);
            $set('document_code', $finalDocumentCode);
        } else {
            $set('document_code', null);
        }
    }
}
