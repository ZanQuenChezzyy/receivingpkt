<?php

namespace App\Filament\Resources\MonitoringChemicals\Schemas;

use App\Models\ChemicalQcTuv;
use App\Models\LocationReceiving;
use App\Models\MonitoringChemical;
use App\Models\MonitoringChemicalDetail;
use App\Models\PurchaseOrderIssued;
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
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class MonitoringChemicalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Group::make()->schema([
                        self::getInformasiKedatanganSection(),
                        self::getSummaryPoSection(),
                    ])->columnSpan(['lg' => 2]),

                    Group::make()->schema([
                        EmptyState::make('Belum ada Nomor PO yang dipilih')
                            ->description('Silakan cari dan pilih Nomor PO pada bagian Informasi Kedatangan untuk menampilkan daftar material.')
                            ->icon(Heroicon::OutlinedCursorArrowRays)
                            ->contained(true)
                            // Catatan: Saya sesuaikan nama field 'search_po' menjadi 'purchase_order_issued_id' agar sesuai dengan form kita
                            ->visible(fn (Get $get, $record): bool => blank($get('purchase_order_issued_id')) && $record === null),
                        self::getTuvDetailsSection(),
                        self::getStatusSection(),
                        self::getTrackingDokumenSection(),

                        Hidden::make('doc_status')
                            ->afterStateHydrated(function (Set $set, Get $get, $state) {
                                if ($get('material_category') === 'Karung') {
                                    $set('doc_status', 'Completed');
                                }
                            })
                            ->dehydrateStateUsing(function (Get $get, $state) {
                                if ($get('material_category') === 'Karung') {
                                    return 'Completed';
                                }

                                $simala = $get('tanggal_pengajuan_simala');
                                $sample = $get('tanggal_pengambilan_sample');
                                $coa = $get('tanggal_terbit_coa');

                                if ($simala && $sample && $coa) {
                                    return 'Completed';
                                }

                                return 'Outstanding';
                            }),

                        Hidden::make('created_by')
                            ->default(Auth::id()),
                    ])->columnSpan(['lg' => 1]),
                ])->columnSpanFull(),
            ]);
    }

    protected static function getInformasiKedatanganSection(): Section
    {
        return Section::make('Informasi Kedatangan Barang')
            ->icon(Heroicon::OutlinedTruck)
            ->description('Pilih kategori dan PO untuk memulai proses input kedatangan.')
            ->schema([
                // 1. Pilih Kategori
                ToggleButtons::make('material_category')
                    ->label('Kategori Material')
                    ->options([
                        'Karung' => 'Karung',
                        'Chemical' => 'Chemical',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->colors([
                        'Karung' => Color::Yellow,
                        'Chemical' => Color::Blue,
                        'Lainnya' => Color::Emerald,
                    ])
                    ->inline()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state === 'Karung') {
                            $set('qc_by', 'ISTEK');
                        } elseif ($state === 'Chemical') {
                            $set('qc_by', 'PPE');
                        } else {
                            $set('qc_by', null);
                        }

                        $set('purchase_order_issued_id', null);
                        $set('do_number', null);
                        $set('quantity', null);
                        $set('tahapan', null);
                        $set('notes', null);
                        $set('monitoringChemicalDetails', []);
                        $set('tanggal_pengajuan_simala', null);
                        $set('tanggal_pengambilan_sample', null);
                        $set('tanggal_terbit_coa', null);
                        $set('leadtime_coa', null);
                        $set('has_update_progress', false);
                        $set('is_qty_tolerance', false);
                    }),

                // 2. Pelaksana QC
                ToggleButtons::make('qc_by')
                    ->label('Tujuan QC')
                    ->key(fn (Get $get) => 'qc_by_'.($get('material_category') ?? 'none'))
                    ->options([
                        'ISTEK' => 'ISTEK',
                        'PPE' => 'PPE',
                    ])
                    ->colors([
                        'ISTEK' => Color::Blue,
                        'PPE' => Color::Indigo,
                    ])
                    ->inline()
                    ->required()
                    ->disabled(fn (Get $get) => in_array($get('material_category'), ['Karung', 'Chemical']) || blank($get('material_category')))
                    ->dehydrated(),

                self::getPurchaseOrderSelect(),
                ...self::getDetailKedatanganGroup(),

            ])->columns(2);
    }

    // 🌟 HELPER 1: Ekstraksi Select Purchase Order
    protected static function getPurchaseOrderSelect(): Select
    {
        return Select::make('purchase_order_issued_id')
            ->label('Purchase Order & Material')
            ->placeholder('Cari Nomor PO atau Deskripsi...')
            ->disabled(fn (Get $get) => blank($get('material_category')))
            ->getSearchResultsUsing(fn (string $search): array => PurchaseOrderIssued::where('purchase_order_no', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->limit(20)
                ->get()
                ->mapWithKeys(fn ($item) => [$item->id => "{$item->purchase_order_no} - Item {$item->item_no} ({$item->description})"])
                ->toArray()
            )
            ->getOptionLabelUsing(function ($value): ?string {
                $item = PurchaseOrderIssued::find($value);

                return $item ? "{$item->purchase_order_no} - Item {$item->item_no} ({$item->description})" : null;
            })
            ->searchable()
            ->preload(false)
            ->required()
            ->live()
            ->afterStateUpdated(function (Set $set, Get $get, $state, $record) {
                $set('quantity', null);
                $set('tahapan', null);

                if (! $state) {
                    $set('monitoringChemicalDetails', []);

                    return;
                }

                // Tarik semua tahapan TUV milik PO ini
                $tuvs = ChemicalQcTuv::where('purchase_order_issued_id', $state)->orderBy('id')->get();
                $repeaterData = [];
                $recordId = $get('id'); // Ambil ID form utama jika sedang di halaman Edit

                // Looping tahapan dan hitung SISA QTY-nya
                foreach ($tuvs as $tuv) {
                    // Hitung riwayat TUV ini yang sudah terpakai di record kedatangan sebelumnya
                    $riwayatTerpakai = MonitoringChemicalDetail::where('chemical_qc_tuv_id', $tuv->id)
                        ->when($recordId, function ($query) use ($recordId) {
                            // Abaikan jumlah dari record yang sedang diedit ini agar tidak double count
                            $query->whereHas('monitoringChemical', function ($q) use ($recordId) {
                                $q->where('id', '!=', $recordId);
                            });
                        })
                        ->sum('quantity_received');

                    $targetTuv = (float) $tuv->qty_qc_tuv;
                    $sisa = $targetTuv - (float) $riwayatTerpakai;

                    // 🌟 HANYA MASUKKAN KE FORM REPEATER JIKA MASIH ADA SISA KUOTA (> 0)
                    if ($sisa > 0) {
                        $repeaterData[(string) Str::uuid()] = [
                            'chemical_qc_tuv_id' => (string) $tuv->id,
                            'quantity_received' => $sisa, // Masukkan SISA-nya, bukan Target aslinya!
                        ];
                    }
                }

                // Tembakkan data sisa ke dalam form Repeater
                $set('monitoringChemicalDetails', $repeaterData);

                // 🌟 PICU PERHITUNGAN ULANG MASTER QTY
                self::sumRepeaterToMainQty($set, $set, $repeaterData);
            })
            ->columnSpanFull();
    }

    // Ubah tipe return menjadi array agar bisa langsung disisipkan ke schema pemanggil
    protected static function getDetailKedatanganGroup(): array
    {
        return [
            // 🌟 1. EMPTY STATE (Muncul saat PO BELUM dipilih)
            EmptyState::make('Belum Ada PO yang Dipilih')
                ->description('Silakan pilih Kategori dan cari Nomor Purchase Order di atas untuk mulai mengisi form kedatangan.')
                ->icon(Heroicon::OutlinedDocumentMagnifyingGlass)
                ->visible(fn (Get $get, $record) => blank($get('purchase_order_issued_id')) && $record === null)
                ->columnSpanFull(),

            // 🌟 2. GROUP INPUT (Muncul saat PO SUDAH dipilih)
            Group::make()
                ->visible(fn (Get $get, $record) => filled($get('purchase_order_issued_id')) || $record !== null)
                ->schema([
                    TextInput::make('do_number')
                        ->label('Nomor DO Sementara')
                        ->placeholder('Masukkan No. DO')
                        ->maxLength(15)
                        ->required(),

                    TextInput::make('quantity')
                        ->label('Quantity Kedatangan')
                        ->required()
                        ->numeric()
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->dehydrateStateUsing(fn ($state) => (float) str_replace(',', '', (string) $state))
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, $state, $record) {
                            $poId = $get('purchase_order_issued_id');
                            $inputQty = (float) str_replace(',', '', (string) $state);

                            if (! $poId || $inputQty <= 0) {
                                $set('tahapan', null);

                                return;
                            }

                            $historyQty = MonitoringChemical::where('purchase_order_issued_id', $poId)
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->sum('quantity');

                            $totalAkumulasiSistem = $historyQty + $inputQty;
                            $tuvs = ChemicalQcTuv::where('purchase_order_issued_id', $poId)->orderBy('id')->get();
                            $tahapanTerlibat = [];
                            $akumulasiTuvTarget = 0;

                            foreach ($tuvs as $index => $tuv) {
                                $tahapStart = $akumulasiTuvTarget;
                                $akumulasiTuvTarget += $tuv->qty_qc_tuv;

                                if ($historyQty < $akumulasiTuvTarget && $totalAkumulasiSistem > $tahapStart) {
                                    preg_match('/\d+/', $tuv->tahapan_name, $matches);
                                    $angkaTahap = $matches[0] ?? ($index + 1);
                                    $tahapanTerlibat[] = $angkaTahap;
                                }
                            }

                            if (! empty($tahapanTerlibat)) {
                                $tahapanUnik = array_unique($tahapanTerlibat);
                                $set('tahapan', 'TAHAP '.implode(',', $tahapanUnik).' TUV');
                            } else {
                                $set('tahapan', null);
                            }
                        })
                        ->rules([
                            fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $poId = $get('purchase_order_issued_id');
                                if (! $poId) {
                                    return;
                                }

                                $inputQty = (float) str_replace(',', '', (string) $value);
                                $poItem = PurchaseOrderIssued::find($poId);
                                $totalPo = $poItem ? (float) $poItem->qty_po : 0;

                                $totalTuvTarget = ChemicalQcTuv::where('purchase_order_issued_id', $poId)->sum('qty_qc_tuv');

                                if ($totalTuvTarget > 0) {
                                    $baseMaxAllowed = (float) $totalTuvTarget;
                                } else {
                                    $baseMaxAllowed = $totalPo;
                                }

                                $isToleranceActive = (bool) ($get('is_qty_tolerance') ?? false);

                                if ($isToleranceActive) {
                                    $toleranceAmount = $baseMaxAllowed * 0.10;
                                    $finalMaxAllowed = $baseMaxAllowed + $toleranceAmount;
                                } else {
                                    $finalMaxAllowed = $baseMaxAllowed;
                                }

                                $recordId = $get('id');
                                $historyQty = MonitoringChemical::where('purchase_order_issued_id', $poId)
                                    ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                                    ->sum('quantity');

                                $sisaBisaDiterima = $finalMaxAllowed - $historyQty;

                                if ($inputQty > $sisaBisaDiterima) {
                                    $fmtSisa = rtrim(rtrim(number_format($sisaBisaDiterima, 2, '.', ','), '0'), '.');
                                    $sumberBatas = $totalTuvTarget > 0 ? 'Target TUV' : 'PO';

                                    if ($isToleranceActive) {
                                        $fail("Overlimit! Dengan toleransi 10% dari {$sumberBatas}, sisa maksimal yang bisa diterima hanya {$fmtSisa}.");
                                    } else {
                                        $fail("Overlimit! Sisa kuota (Berdasarkan {$sumberBatas}) hanya {$fmtSisa}. Aktifkan 'Toleransi Qty Aktif' jika barang datang melebihi target.");
                                    }
                                }
                            },
                        ]),

                    TextInput::make('tahapan')
                        ->label(fn (Get $get) => $get('material_category') === 'Karung' ? 'Tahapan (Auto)' : 'Tahapan')
                        ->required()
                        ->readOnly(fn (Get $get) => $get('material_category') === 'Karung')
                        ->hint(fn (Get $get) => $get('material_category') === 'Karung' ? 'Otomatis dari QTY' : null),

                    DatePicker::make('received_date')
                        ->label('Tanggal Tiba')
                        ->native(false)
                        ->maxDate(now())
                        ->default(now())
                        ->required(),

                    Select::make('location_id')
                        ->label('Lokasi Kedatangan')
                        ->options(LocationReceiving::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('received_by')
                        ->label('Diterima Oleh')
                        ->relationship('receivedBy', 'name')
                        ->default(Auth::id())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Textarea::make('notes')
                        ->label('Keterangan (Opsional)')
                        ->placeholder('Masukkan catatan tambahan jika diperlukan...')
                        ->rows(3)
                        ->autosize()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    protected static function getSummaryPoSection(): Section
    {
        return Section::make('Summary PO (Keseluruhan Item)')
            ->icon(Heroicon::OutlinedTableCells)
            ->visible(function (Get $get, $record) {
                $hasPo = filled($get('purchase_order_issued_id')) || $record !== null;

                return $hasPo;
            })
            ->schema([
                TextEntry::make('summary_table')
                    ->hiddenLabel()
                    ->extraAttributes(fn (Get $get) => [
                        'data-qty' => $get('quantity'),
                    ])
                    ->getStateUsing(function (Get $get, $record) {
                        // 1. Amankan ID PO
                        $poId = $get('purchase_order_issued_id') ?? ($record ? $record->purchase_order_issued_id : null);

                        if (! $poId) {
                            return new HtmlString('<p class="text-sm text-gray-500 italic">Data PO tidak ditemukan.</p>');
                        }

                        $selectedPo = PurchaseOrderIssued::find($poId);
                        if (! $selectedPo) {
                            return '';
                        }

                        $rawInputQty = $get('quantity');

                        if ($rawInputQty !== null && $rawInputQty !== '') {
                            // Ambil dari inputan user yang sedang aktif
                            $inputQtySementara = (float) str_replace(',', '.', (string) $rawInputQty);
                        } else {
                            // Ambil dari database jika form baru dibuka dan user belum mengetik apa-apa
                            $inputQtySementara = $record ? (float) $record->quantity : 0;
                        }

                        $recordId = $record ? $record->id : null;

                        $allItems = PurchaseOrderIssued::where('purchase_order_no', $selectedPo->purchase_order_no)
                            ->orderBy('item_no')
                            ->get();

                        // ... [Mulai dari sini, kode HTML tabelnya TETAP SAMA persis seperti sebelumnya] ...
                        $html = '<div class="fi-ta-content divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-gray-700 dark:bg-gray-900 dark:ring-white/10">';
                        $html .= '<div class="overflow-x-auto">';
                        $html .= '<table class="w-full table-auto divide-y divide-gray-200 dark:divide-gray-700">';

                        // ... Header ...
                        $html .= '<thead class="bg-gray-50 dark:bg-white/5">
                                    <tr>
                                        <th class="px-3 py-2.5 text-left text-sm font-semibold text-gray-950 dark:text-white whitespace-nowrap">Item No</th>
                                        <th class="px-3 py-2.5 text-left text-sm font-semibold text-gray-950 dark:text-white">Deskripsi</th>
                                        <th class="px-3 py-2.5 text-right text-sm font-semibold text-gray-950 dark:text-white whitespace-nowrap">Qty PO</th>
                                        <th class="px-3 py-2.5 text-right text-sm font-semibold text-gray-950 dark:text-white whitespace-nowrap">Qty Riwayat</th>
                                        <th class="px-3 py-2.5 text-right text-sm font-semibold text-gray-950 dark:text-white whitespace-nowrap">Qty Masuk Saat Ini</th>
                                        <th class="px-3 py-2.5 text-right text-sm font-semibold text-gray-950 dark:text-white whitespace-nowrap">Sisa Qty</th>
                                    </tr>
                                </thead>';

                        $html .= '<tbody class="divide-y divide-gray-200 dark:divide-gray-700">';

                        // ... Looping ...
                        foreach ($allItems as $item) {
                            $totalDiterimaRiwayat = MonitoringChemical::where('purchase_order_issued_id', $item->id)
                                ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                                ->sum('quantity');

                            $qtySaatIni = ($item->id == $poId) ? $inputQtySementara : 0;

                            $totalKeseluruhan = $totalDiterimaRiwayat + $qtySaatIni;

                            $qtyPoFloat = (float) $item->qty_po;
                            $sisa = $qtyPoFloat - $totalKeseluruhan;

                            // ... Logika warna dan render baris (seperti sebelumnya) ...
                            $colorClass = '';
                            $badgeClass = '';
                            if ($sisa <= 0) {
                                $colorClass = 'text-success-600 dark:text-success-400 whitespace-nowrap';
                                $badgeClass = 'inline-flex items-center justify-center min-h-6 px-2 py-0.5 text-xs font-medium tracking-tight rounded-md text-success-700 bg-success-50 ring-1 ring-inset ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30 whitespace-nowrap';
                            } elseif ($sisa < 0) {
                                $colorClass = 'text-danger-600 dark:text-danger-400 font-bold whitespace-nowrap';
                                $badgeClass = 'inline-flex items-center justify-center min-h-6 px-2 py-0.5 text-xs font-medium tracking-tight rounded-md text-danger-700 bg-danger-50 ring-1 ring-inset ring-danger-600/10 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20 whitespace-nowrap';
                            } else {
                                $colorClass = 'text-gray-500 dark:text-gray-400 whitespace-nowrap';
                            }

                            $fmtPo = rtrim(rtrim(number_format($qtyPoFloat, 2, ',', '.'), '0'), ',');
                            $fmtTerimaRiwayat = rtrim(rtrim(number_format($totalDiterimaRiwayat, 2, ',', '.'), '0'), ',');
                            $fmtSaatIni = rtrim(rtrim(number_format($qtySaatIni, 2, ',', '.'), '0'), ',');
                            $fmtSisa = rtrim(rtrim(number_format($sisa, 2, ',', '.'), '0'), ',');

                            $saatIniColor = $qtySaatIni > 0 ? 'text-primary-600 dark:text-primary-400 font-bold' : 'text-gray-400';

                            $shortDescription = Str::limit($item->description, 20, '...');
                            $escapedDescription = htmlspecialchars($item->description, ENT_QUOTES, 'UTF-8');

                            $html .= "<tr class='hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75'>";
                            $html .= "<td class='px-3 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap'>{$item->item_no}</td>";

                            $html .= "<td class='px-3 py-3 text-sm text-gray-950 dark:text-white font-medium'>";
                            if (strlen($item->description) > 20) {
                                $html .= "<span x-data x-tooltip=\"'{$escapedDescription}'\" class='cursor-help border-b border-dashed border-gray-300 dark:border-gray-600'>{$shortDescription}</span>";
                            } else {
                                $html .= "<span>{$item->description}</span>";
                            }
                            $html .= '</td>';

                            $html .= "<td class='px-3 py-3 text-sm text-right text-gray-500 dark:text-gray-400 whitespace-nowrap'>{$fmtPo} {$item->uoi}</td>";
                            $html .= "<td class='px-3 py-3 text-sm text-right font-semibold text-success-600 dark:text-success-400 whitespace-nowrap'>{$fmtTerimaRiwayat} {$item->uoi}</td>";
                            $html .= "<td class='px-3 py-3 text-sm text-right {$saatIniColor} whitespace-nowrap'>+ {$fmtSaatIni} {$item->uoi}</td>";

                            $html .= "<td class='px-3 py-3 text-sm text-right whitespace-nowrap'>";
                            if ($badgeClass !== '') {
                                $html .= "<span class='{$badgeClass}'>{$fmtSisa} {$item->uoi}</span>";
                            } else {
                                $html .= "<span class='{$colorClass}'>{$fmtSisa} {$item->uoi}</span>";
                            }
                            $html .= '</td></tr>';
                        }

                        $html .= '</tbody></table></div></div>';

                        return new HtmlString($html);
                    }),
            ]);
    }

    protected static function getTrackingDokumenSection(): Section
    {
        return Section::make('Tracking Dokumen QC')
            ->icon(Heroicon::OutlinedDocumentMagnifyingGlass)
            ->visible(function (Get $get) {
                $isChemicalOrLainnya = in_array($get('material_category'), ['Chemical', 'Lainnya']);
                $isUpdateProgressActive = (bool) $get('has_update_progress');

                return $isChemicalOrLainnya && $isUpdateProgressActive;
            })
            ->schema([
                DatePicker::make('tanggal_pengajuan_simala')
                    ->label('Pengajuan Simala')
                    ->native(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateLeadtimeAndStatus($set, $get)),

                DatePicker::make('tanggal_pengambilan_sample')
                    ->label('Pengambilan Sample')
                    ->native(false)
                    ->live(onBlur: true) // 🌟 TAMBAHAN: Jadikan live agar memicu pengecekan status
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateLeadtimeAndStatus($set, $get)),

                DatePicker::make('tanggal_terbit_coa')
                    ->label('Terbit COA')
                    ->native(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateLeadtimeAndStatus($set, $get)),

                TextInput::make('leadtime_coa')
                    ->label('Leadtime COA')
                    ->numeric()
                    ->readOnly()
                    ->suffix('Hari'),
            ])->columns(2);
    }

    protected static function getTuvDetailsSection(): Section
    {
        return Section::make('Detail QC TUV')
            ->icon(Heroicon::OutlinedClipboardDocumentList)
            ->visible(fn (Get $get, $record) => $get('material_category') === 'Karung' && (filled($get('purchase_order_issued_id')) || $record !== null))
            ->schema([
                self::getTuvEmptyState(),
                self::getTuvRepeater(),
                self::getTuvSummaryTextEntry(),
            ]);
    }

    // 🌟 HELPER 1: EMPTY STATE
    protected static function getTuvEmptyState(): EmptyState
    {
        return EmptyState::make('Data TUV Tidak Tersedia atau Sudah Lunas')
            ->description(function (Get $get, $record) {
                $poId = $get('purchase_order_issued_id');
                if (! $poId) {
                    return '';
                }

                $countTuv = ChemicalQcTuv::where('purchase_order_issued_id', $poId)->count();
                if ($countTuv === 0) {
                    return 'PO ini belum memiliki target tahapan QC TUV di database. Anda bisa menambahkannya secara manual menggunakan tombol "Tambah QC TUV" di bawah.';
                }

                return 'Semua kuota TUV untuk PO ini telah terpenuhi/habis. Jika ada kedatangan tambahan, silakan klik tombol "Tambah QC TUV" di bawah untuk membuat tahapan TUV baru.';
            })
            ->icon(Heroicon::OutlinedShieldCheck)
            ->visible(function (Get $get, $record) {
                $poId = $get('purchase_order_issued_id');
                if (! $poId) {
                    return false;
                }

                $tuvs = ChemicalQcTuv::where('purchase_order_issued_id', $poId)->get();

                if ($tuvs->isEmpty()) {
                    return true;
                }

                $recordId = $record ? $record->id : null;
                $totalSisa = 0;

                foreach ($tuvs as $tuv) {
                    $riwayatTerpakai = MonitoringChemicalDetail::where('chemical_qc_tuv_id', $tuv->id)
                        ->when($recordId, function ($query) use ($recordId) {
                            $query->where('monitoring_chemical_id', '!=', $recordId);
                        })
                        ->sum('quantity_received');

                    $sisa = (float) $tuv->qty_qc_tuv - (float) $riwayatTerpakai;
                    if ($sisa > 0) {
                        $totalSisa += $sisa;
                    }
                }

                return $totalSisa <= 0;
            });
    }

    // 🌟 HELPER 2: REPEATER
    protected static function getTuvRepeater(): Repeater
    {
        return Repeater::make('monitoringChemicalDetails')
            ->label('')
            ->relationship('monitoringChemicalDetails')
            ->addActionLabel('Tambah QC TUV')
            ->defaultItems(0)
            ->live()
            ->schema([
                Select::make('chemical_qc_tuv_id')
                    ->label('QC TUV')
                    ->relationship(
                        name: 'chemicalQcTuv',
                        titleAttribute: 'tahapan_name',
                        modifyQueryUsing: fn (Builder $query, Get $get) => $query->where('purchase_order_issued_id', $get('../../purchase_order_issued_id'))
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->live()
                    ->createOptionForm([
                        Section::make('Informasi QC TUV')
                            ->description('Detail tahapan pemeriksaan kualitas chemical.')
                            ->aside() // Membuat label section di samping (opsional, keren untuk layar lebar)
                            ->schema([
                                Select::make('tahapan_name')
                                    ->label('Nama Tahapan')
                                    ->options(
                                        collect(range(1, 1000))->mapWithKeys(fn ($angka) => [
                                            "TAHAP {$angka} TUV" => "TAHAP {$angka} TUV",
                                        ])->toArray()
                                    )
                                    ->searchable() // Agar user bisa ketik "10" langsung muncul Tahap 10
                                    ->required(),

                                TextInput::make('qty_qc_tuv')
                                    ->label('Quantity QC')
                                    ->numeric()
                                    ->required()
                                    ->prefix('QTY')
                                    ->default(0),
                            ])->columns(2),
                    ])
                    ->createOptionUsing(function (array $data, Get $get): int {
                        $poId = $get('../../purchase_order_issued_id');

                        $newTuv = ChemicalQcTuv::create([
                            'purchase_order_issued_id' => $poId,
                            'tahapan_name' => $data['tahapan_name'],
                            'qty_qc_tuv' => (float) str_replace(',', '', (string) $data['qty_qc_tuv']),
                        ]);

                        return $newTuv->id;
                    })
                    ->editOptionForm([
                        Section::make('Informasi QC TUV')
                            ->description('Detail tahapan pemeriksaan kualitas chemical.')
                            ->aside() // Membuat label section di samping (opsional, keren untuk layar lebar)
                            ->schema([
                                Select::make('tahapan_name')
                                    ->label('Nama Tahapan')
                                    ->options(
                                        collect(range(1, 1000))->mapWithKeys(fn ($angka) => [
                                            "TAHAP {$angka} TUV" => "TAHAP {$angka} TUV",
                                        ])->toArray()
                                    )
                                    ->searchable() // Agar user bisa ketik "10" langsung muncul Tahap 10
                                    ->required(),

                                TextInput::make('qty_qc_tuv')
                                    ->label('Quantity QC')
                                    ->numeric()
                                    ->required()
                                    ->prefix('QTY')
                                    ->default(0),
                            ])->columns(2),
                    ])
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($state) {
                            $tuv = ChemicalQcTuv::find($state);
                            if ($tuv) {
                                $poId = $get('../../purchase_order_issued_id');
                                $recordId = $get('../../id');

                                $targetTuvQty = (float) $tuv->qty_qc_tuv;

                                $riwayatTerpakai = MonitoringChemicalDetail::where('chemical_qc_tuv_id', $state)
                                    ->when($recordId, function ($query) use ($recordId) {
                                        $query->where('monitoring_chemical_id', '!=', $recordId);
                                    })
                                    ->sum('quantity_received');

                                $sisaUntukTahapIni = $targetTuvQty - (float) $riwayatTerpakai;

                                if ($sisaUntukTahapIni < 0) {
                                    $sisaUntukTahapIni = 0;
                                }

                                $set('quantity_received', $sisaUntukTahapIni);
                            }
                        } else {
                            $set('quantity_received', null);
                        }

                        self::sumRepeaterToMainQty($set, clone $get);
                    }),

                TextInput::make('quantity_received')
                    ->label('TUV QTY')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->required()
                    ->readOnly()
                    ->extraInputAttributes(['class' => 'bg-gray-100 dark:bg-gray-800'])
                    ->suffix(function (Get $get) {
                        $poId = $get('../../purchase_order_issued_id');
                        if ($poId) {
                            $po = PurchaseOrderIssued::find($poId);

                            return $po ? $po->uoi : '';
                        }

                        return '';
                    }),
            ]);
    }

    // 🌟 HELPER 3: SUMMARY RIWAYAT TEXT ENTRY
    protected static function getTuvSummaryTextEntry(): TextEntry
    {
        return TextEntry::make('summary_riwayat_tuv')
            ->hiddenLabel()
            ->visible(function (Get $get) {
                $poId = $get('purchase_order_issued_id');
                if (! $poId) {
                    return false;
                }

                return ChemicalQcTuv::where('purchase_order_issued_id', $poId)->exists();
            })
            ->getStateUsing(function (Get $get, $record) {
                $poId = $get('purchase_order_issued_id');
                if (! $poId) {
                    return '';
                }

                $po = PurchaseOrderIssued::find($poId);
                $uoi = $po ? $po->uoi : '';

                $tuvs = ChemicalQcTuv::where('purchase_order_issued_id', $poId)->orderBy('id')->get();
                $recordId = $record ? $record->id : null;

                $totalRiwayatLama = 0;
                $htmlRiwayat = '';

                foreach ($tuvs as $tuv) {
                    $riwayatTerpakai = MonitoringChemicalDetail::where('chemical_qc_tuv_id', $tuv->id)
                        ->when($recordId, function ($query) use ($recordId) {
                            $query->where('monitoring_chemical_id', '!=', $recordId);
                        })
                        ->sum('quantity_received');

                    if ($riwayatTerpakai > 0) {
                        $totalRiwayatLama += $riwayatTerpakai;
                        $fmtRiwayatItem = rtrim(rtrim(number_format($riwayatTerpakai, 2, '.', ','), '0'), '.');
                        $htmlRiwayat .= "
                            <div class='flex justify-between items-center py-1 border-b border-gray-100 dark:border-gray-800 last:border-0'>
                                <span class='text-sm text-gray-600 dark:text-gray-400'>{$tuv->tahapan_name}</span>
                                <span class='text-sm font-medium text-gray-900 dark:text-white'>{$fmtRiwayatItem} {$uoi}</span>
                            </div>
                        ";
                    }
                }

                $detailsSekarang = $get('monitoringChemicalDetails') ?? [];
                $totalAkanDitambah = 0;
                $htmlAkanDitambah = '';

                foreach ($detailsSekarang as $item) {
                    $qtyStr = str_replace(',', '', (string) ($item['quantity_received'] ?? '0'));
                    $qtyInput = (float) $qtyStr;

                    if ($qtyInput > 0 && ! empty($item['chemical_qc_tuv_id'])) {
                        $totalAkanDitambah += $qtyInput;
                        $tuvItem = ChemicalQcTuv::find($item['chemical_qc_tuv_id']);
                        $namaTahap = $tuvItem ? $tuvItem->tahapan_name : 'Tahap Tidak Diketahui';
                        $fmtInputItem = rtrim(rtrim(number_format($qtyInput, 2, '.', ','), '0'), '.');

                        $htmlAkanDitambah .= "
                            <div class='flex justify-between items-center py-1 border-b border-success-100 dark:border-success-900/30 last:border-0'>
                                <span class='text-sm text-success-600 dark:text-success-400 font-medium'>{$namaTahap}</span>
                                <span class='text-sm font-bold text-success-600 dark:text-success-400'>+ {$fmtInputItem} {$uoi}</span>
                            </div>
                        ";
                    }
                }

                $grandTotal = $totalRiwayatLama + $totalAkanDitambah;
                $fmtGrandTotal = rtrim(rtrim(number_format($grandTotal, 2, '.', ','), '0'), '.');

                $ui = '<div class="mt-4 rounded-xl ring-1 ring-gray-950/10 dark:ring-white/20 bg-white dark:bg-gray-900 overflow-hidden">';

                if ($htmlRiwayat !== '') {
                    $ui .= '
                        <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 border-b border-gray-950/10 dark:border-white/20">
                            <h4 class="text-sm font-semibold text-gray-950 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Riwayat Penerimaan TUV Sebelumnya
                            </h4>
                        </div>
                        <div class="px-4 py-2">
                            '.$htmlRiwayat.'
                        </div>
                    ';
                }

                if ($htmlAkanDitambah !== '') {
                    $ui .= '
                        <div class="px-4 py-3 bg-success-50 dark:bg-success-400/10 border-y border-success-200 dark:border-success-800">
                            <h4 class="text-sm font-semibold text-success-800 dark:text-success-400 flex items-center gap-2">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Akan Ditambahkan (Sesi Ini)
                            </h4>
                        </div>
                        <div class="px-4 py-2 bg-white dark:bg-gray-900">
                            '.$htmlAkanDitambah.'
                        </div>
                    ';
                }

                $ui .= '
                    <div class="px-4 py-4 bg-primary-50 dark:bg-primary-900/20 border-t border-primary-200 dark:border-primary-800 flex justify-between items-center">
                        <span class="text-sm font-bold text-primary-900 dark:text-primary-100">Total Keseluruhan</span>
                        <span class="text-sm font-black text-primary-600 dark:text-primary-400">'.$fmtGrandTotal.' '.$uoi.'</span>
                    </div>
                ';

                $ui .= '</div>';

                return new HtmlString($ui);
            });
    }

    protected static function getStatusSection(): Section
    {
        return Section::make('Status Dokumen')
            ->visible(fn (Get $get, $record) => $get('material_category') !== 'Karung' && (filled($get('purchase_order_issued_id')) || $record !== null))
            ->schema([
                // 🌟 TAMPILAN UI (Tidak masuk ke database)
                TextEntry::make('doc_status_display')
                    ->label('Status')
                    ->inlineLabel()
                    ->getStateUsing(fn (Get $get) => $get('doc_status') ?? 'Outstanding')
                    ->badge() // Ubah jadi desain Badge oval yang cantik
                    ->color(fn (string $state): string => match ($state) {
                        'Completed' => 'success',  // Hijau
                        'Outstanding' => 'warning',  // Kuning/Orange
                        'Rejected' => 'danger',   // Merah
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Completed' => 'heroicon-m-check-circle',
                        'Outstanding' => 'heroicon-m-clock',
                        'Rejected' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-document',
                    }),

                Fieldset::make('Toggle & Opsi Lainnya')
                    ->schema([
                        Toggle::make('has_update_progress')
                            ->label('Update Progress?')
                            ->inline(false)
                            ->default(false)
                            ->live(),

                        Toggle::make('is_qty_tolerance')
                            ->label('Toleransi Qty Aktif?')
                            ->inline(false)
                            ->default(false),
                    ]),
            ]);
    }

    public static function calculateLeadTime(Set $set, Get $get): void
    {
        $simalaDate = $get('tanggal_pengajuan_simala');
        $coaDate = $get('tanggal_terbit_coa');

        if ($simalaDate && $coaDate) {
            $diff = Carbon::parse($simalaDate)->diffInDays(Carbon::parse($coaDate));
            $set('leadtime_coa', $diff);
        } else {
            $set('leadtime_coa', null);
        }
    }

    // Ganti fungsi ini di bagian bawah class
    public static function sumRepeaterToMainQty(Set $set, $getOrSet, $overrideDetails = null): void
    {
        $details = $overrideDetails ?? (is_callable($getOrSet) ? $getOrSet('../../monitoringChemicalDetails') : []);

        $total = 0;
        $tahapanTerlibat = [];

        foreach ($details as $item) {
            // 🌟 PERBAIKAN BUG KOMA: Gunakan str_replace(',', '') bukan (',', '.')
            $qtyStr = str_replace(',', '', (string) ($item['quantity_received'] ?? '0'));
            $qty = (float) $qtyStr;

            $total += $qty;

            if ($qty > 0 && ! empty($item['chemical_qc_tuv_id'])) {
                $tuv = ChemicalQcTuv::find($item['chemical_qc_tuv_id']);
                if ($tuv) {
                    preg_match('/\d+/', $tuv->tahapan_name, $matches);
                    $tahapanTerlibat[] = $matches[0] ?? '';
                }
            }
        }

        $targetQtyKey = $overrideDetails ? 'quantity' : '../../quantity';
        $targetTahapanKey = $overrideDetails ? 'tahapan' : '../../tahapan';

        $set($targetQtyKey, $total > 0 ? $total : null);

        $tahapanTerlibat = array_filter($tahapanTerlibat);
        if (! empty($tahapanTerlibat)) {
            $tahapanUnik = array_unique($tahapanTerlibat);
            $set($targetTahapanKey, 'TAHAP '.implode(',', $tahapanUnik).' TUV');
        } else {
            $set($targetTahapanKey, null);
        }
    }

    public static function updateLeadtimeAndStatus(Set $set, Get $get): void
    {
        $simalaDate = $get('tanggal_pengajuan_simala');
        $sampleDate = $get('tanggal_pengambilan_sample');
        $coaDate = $get('tanggal_terbit_coa');

        // --- 1. HITUNG LEADTIME (Simala s.d COA) ---
        if ($simalaDate && $coaDate) {
            $diff = \Carbon\Carbon::parse($simalaDate)->diffInDays(\Carbon\Carbon::parse($coaDate));
            $set('leadtime_coa', $diff);
        } else {
            $set('leadtime_coa', null);
        }

        // --- 2. UPDATE DOCUMENT STATUS ---
        // Jika KETIGA tanggal sudah terisi, maka status Completed
        if ($simalaDate && $sampleDate && $coaDate) {
            $set('doc_status', 'Completed');
        } else {
            // Jika ada satu saja yang kosong, status kembali ke Outstanding
            $set('doc_status', 'Outstanding');
        }
    }
}
