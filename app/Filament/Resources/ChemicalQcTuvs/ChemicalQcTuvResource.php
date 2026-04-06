<?php

namespace App\Filament\Resources\ChemicalQcTuvs;

use App\Filament\Resources\ChemicalQcTuvs\Pages\ManageChemicalQcTuvs;
use App\Models\ChemicalQcTuv;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ChemicalQcTuvResource extends Resource
{
    protected static ?string $model = ChemicalQcTuv::class;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEyeDropper;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::EyeDropper;

    protected static ?string $recordTitleAttribute = 'purchaseOrderIssued.purchase_order_no';

    protected static ?int $navigationSort = 24;

    protected static ?string $slug = 'qc-tuv-chemical';

    public static function getNavigationLabel(): string
    {
        return 'QC TUV Chemical';
    }

    public static function getModelLabel(): string
    {
        return 'QC TUV Chemical';
    }

    public static function getPluralModelLabel(): string
    {
        return 'QC TUV Chemical';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();

        return $count < 1 ? 'danger' : 'success';
    }

    protected static string|Htmlable|null $navigationBadgeTooltip = 'Total QC TUV Chemical';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi QC TUV')
                    ->description('Detail tahapan pemeriksaan kualitas chemical.')
                    ->aside() // Membuat label section di samping (opsional, keren untuk layar lebar)
                    ->schema([
                        Select::make('purchase_order_issued_id')
                            ->label('Nomor PO dan Item')
                            ->placeholder('PIlih Nomor PO dan Item')
                            ->relationship('purchaseOrderIssued', 'purchase_order_and_item')
                            ->searchable()
                            ->noOptionsMessage('Tidak ada data ditemukan')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Pilih nomor PO yang terkait dengan pengecekan ini.'),

                        TextInput::make('tahapan_name')
                            ->label('Nama Tahapan')
                            ->placeholder('Contoh: Tahap 1 TUV')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('qty_qc_tuv')
                            ->label('Quantity QC')
                            ->numeric()
                            ->required()
                            ->prefix('QTY')
                            ->default(0),
                    ])->columns(2),
            ])->columns(1);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('purchase_order_issued_id')
                    ->numeric(),
                TextEntry::make('tahapan_name'),
                TextEntry::make('qty_qc_tuv')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchaseOrderIssued.purchase_order_no')
                    ->label('Purchase Order')
                    ->sortable(),
                TextColumn::make('purchaseOrderIssued.item_no')
                    ->label('Item')
                    ->sortable(),
                TextColumn::make('tahapan_name')
                    ->label('Tahapan QC TUV')
                    ->searchable(),
                TextColumn::make('qty_qc_tuv')
                    ->label('QTY QC TUV')
                    ->numeric()
                    ->suffix(fn ($record): string => ' '.($record->purchaseOrderIssued?->uoi ?? ''))
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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageChemicalQcTuvs::route('/'),
        ];
    }
}
