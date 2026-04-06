<?php

namespace App\Filament\Resources\PurchaseOrderIssueds\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseOrderIssuedForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi PO')
                    ->description('Masukkan informasi umum tentang Purchase Order')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('purchase_order_no')
                                ->label('No. Purchase Order')
                                ->placeholder('Masukkan nomor PO')
                                ->helperText('Contoh: 5000004191')
                                ->required()
                                ->maxLength(12)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $itemNo = $get('item_no');
                                    if ($itemNo !== null) {
                                        $set('purchase_order_and_item', $state.'-'.$itemNo);
                                    }
                                }),

                            Select::make('item_no')
                                ->label('Item No.')
                                ->placeholder('Pilih nomor item PO')
                                ->options([
                                    10 => '10',
                                    20 => '20',
                                    30 => '30',
                                    40 => '40',
                                    50 => '50',
                                    60 => '60',
                                    70 => '70',
                                    80 => '80',
                                    90 => '90',
                                    100 => '100',
                                    110 => '110',
                                    120 => '120',
                                    130 => '130',
                                    140 => '140',
                                    150 => '150',
                                    160 => '160',
                                    170 => '170',
                                    180 => '180',
                                    190 => '190',
                                    200 => '200',
                                    210 => '210',
                                    220 => '220',
                                    230 => '230',
                                    240 => '240',
                                    250 => '250',
                                    260 => '260',
                                    270 => '270',
                                    280 => '280',
                                    290 => '290',
                                    300 => '300',
                                ])
                                ->required()
                                ->reactive()
                                ->native(false)
                                ->searchable()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $poNo = $get('purchase_order_no');
                                    if ($poNo !== null) {
                                        $set('purchase_order_and_item', $poNo.'-'.$state);
                                    }
                                }),

                            TextInput::make('purchase_order_and_item')
                                ->label('Purchase Order & Item')
                                ->disabled()
                                ->dehydrated()
                                ->placeholder('Otomatis Terisi')
                                ->maxLength(20),

                            TextInput::make('material_code')
                                ->label('Kode Material')
                                ->numeric()
                                ->placeholder('Masukkan kode material'),
                        ]),

                        Textarea::make('description')
                            ->label('Deskripsi Material')
                            ->required()
                            ->columnSpanFull()
                            ->rows(3)
                            ->autosize()
                            ->required()
                            ->placeholder('Tulis nama material atau detail spesifikasi'),

                        Grid::make(2)->schema([
                            TextInput::make('qty_po')
                                ->label('Kuantitas')
                                ->placeholder('Masukkan kuantitas')
                                ->helperText('Contoh: 1000')
                                ->required()
                                ->numeric()
                                ->default(0),

                            Select::make('uoi')
                                ->label('Unit of Issue (UOI)')
                                ->placeholder('Pilih UOI')
                                ->options([
                                    'AML' => 'AML',
                                    'ASY' => 'ASY',
                                    'AU' => 'AU',
                                    'BAG' => 'BAG',
                                    'BAL' => 'BAL',
                                    'BDL' => 'BDL',
                                    'BOX' => 'BOX',
                                    'BT' => 'BT',
                                    'BTG' => 'BTG',
                                    'CAN' => 'CAN',
                                    'CAR' => 'CAR',
                                    'CM' => 'CM',
                                    'CRD' => 'CRD',
                                    'CV' => 'CV',
                                    'CYL' => 'CYL',
                                    'DR' => 'DR',
                                    'DZ' => 'DZ',
                                    'EA' => 'EA',
                                    'FT' => 'FT',
                                    'G' => 'G',
                                    'GAL' => 'GAL',
                                    'KG' => 'KG',
                                    'KIT' => 'KIT',
                                    'KL' => 'KL',
                                    'L' => 'L',
                                    'LBR' => 'LBR',
                                    'LGT' => 'LGT',
                                    'LIC' => 'LIC',
                                    'LMI' => 'LMI',
                                    'LNK' => 'LNK',
                                    'LOT' => 'LOT',
                                    'M' => 'M',
                                    'M2' => 'M2',
                                    'M3' => 'M3',
                                    'ML' => 'ML',
                                    'MM' => 'MM',
                                    'MM3' => 'MM3',
                                    'MON' => 'MON',
                                    'NM3' => 'NM3',
                                    'PAA' => 'PAA',
                                    'PAC' => 'PAC',
                                    'PC' => 'PC',
                                    'PKT' => 'PKT',
                                    'PL' => 'PL',
                                    'ROL' => 'ROL',
                                    'SAK' => 'SAK',
                                    'SET' => 'SET',
                                    'SHT' => 'SHT',
                                    'STK' => 'STK',
                                    'TON' => 'TON',
                                    'TUB' => 'TUB',
                                    'UN' => 'UN',
                                    'VIA' => 'VIA',
                                    'YD' => 'YD',
                                    'YD3' => 'YD3',
                                ])
                                ->native(false)
                                ->searchable()
                                ->required(),

                            Select::make('material_type')
                                ->label('Material Type')
                                ->placeholder('Pilih Material Type')
                                ->options([
                                    'ZSP' => 'ZSP',
                                    'ZRM' => 'ZRM',
                                ])
                                ->native(false)
                                ->searchable()
                                ->required(),

                            TextInput::make('requisitioner')
                                ->label('Requisitioner')
                                ->placeholder('Masukkan Requisitioner')
                                ->required(),
                        ]),

                        TextInput::make('total_amount_in_lc')
                            ->label('Total Amount in LC')
                            ->placeholder('Masukkan total nilai dalam rupiah')
                            ->numeric()
                            ->default(0)
                            ->step(1000)
                            ->prefix('Rp')
                            ->required(),
                    ])->columnSpan(1),

                Group::make()
                    ->schema([
                        Section::make('Vendor & Pengiriman')
                            ->description('Informasi vendor dan tanggal pengiriman')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('vendor_id')
                                        ->label('ID Vendor')
                                        ->placeholder('Masukkan ID Vendor')
                                        ->required()
                                        ->numeric(),

                                    TextInput::make('vendor_name')
                                        ->label('ID & Nama Vendor')
                                        ->placeholder('Masukkan ID & Nama vendor')
                                        ->required()
                                        ->maxLength(100),
                                ]),

                                Grid::make(2)->schema([
                                    DatePicker::make('date_create')
                                        ->label('Tanggal PO Dibuat')
                                        ->placeholder('Pilih tanggal dibuat')
                                        ->native(false)
                                        ->required()
                                        ->displayFormat('d M Y'),

                                    DatePicker::make('delivery_date_po')
                                        ->label('Tanggal Pengiriman (PO)')
                                        ->placeholder('Pilih tanggal pengiriman')
                                        ->native(false)
                                        ->required()
                                        ->displayFormat('d M Y'),
                                ]),
                            ])->columnSpan(1),

                        Section::make('Lainnya')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('po_status')
                                        ->label('Status')
                                        ->placeholder('Pillih status Purchase Order')
                                        ->options([
                                            'A' => 'A',
                                            'B' => 'B',
                                        ])
                                        ->default('B')
                                        ->required()
                                        ->native(false),

                                    Select::make('mrp_type')
                                        ->label('MRP TYPE')
                                        ->placeholder('Pilih MRP TYPE')
                                        ->options([
                                            'V1' => 'V1',
                                            'PD' => 'PD',
                                            'INVESTASI' => 'INVESTASI',
                                            'NONSTOCK' => 'NONSTOCK',
                                        ])
                                        ->required()
                                        ->native(false),
                                ]),
                                Grid::make(2)->schema([
                                    Select::make('aac')
                                        ->label('Account Assignment Cat.')
                                        ->placeholder('Pilih AAC')
                                        ->options([
                                            'A' => 'A',
                                            'P' => 'P',
                                            'K' => 'K',
                                        ])
                                        ->native(false),

                                    Select::make('abc_indicator')
                                        ->label('ABC Indicator')
                                        ->placeholder('Pilih ABC')
                                        ->searchable()
                                        ->options([
                                            'A' => 'A',
                                            'B' => 'B',
                                            'C' => 'C',
                                            'D' => 'D',
                                            'E' => 'E',
                                            'F' => 'F',
                                            'G' => 'G',
                                            'H' => 'H',
                                            'I' => 'I',
                                            'J' => 'J',
                                            'K' => 'K',
                                            'L' => 'L',
                                            'M' => 'M',
                                            'N' => 'N',
                                            'O' => 'O',
                                            'P' => 'P',
                                            'Q' => 'Q',
                                            'R' => 'R',
                                            'S' => 'S',
                                            'T' => 'T',
                                            'U' => 'U',
                                            'V' => 'V',
                                            'W' => 'W',
                                            'X' => 'X',
                                            'Y' => 'Y',
                                            'Z' => 'Z',
                                        ])
                                        ->native(false),
                                ]),
                                TextInput::make('incoterm')
                                    ->label('Incoterm')
                                    ->placeholder('Masukkan Incoterm')
                                    ->maxLength(100)
                                    ->helperText('Contoh: CIF JAKARTA'),
                            ]),
                    ]),
            ])->columns(2);
    }
}
