<?php

namespace App\Filament\Resources\LocationReceivings;

use App\Filament\Resources\LocationReceivings\Pages\ManageLocationReceivings;
use App\Models\LocationReceiving;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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

class LocationReceivingResource extends Resource
{
    protected static ?string $model = LocationReceiving::class;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::MapPin;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 25;

    protected static ?string $slug = 'lokasi-receiving';

    public static function getNavigationLabel(): string
    {
        return 'Lokasi Receiving';
    }

    public static function getModelLabel(): string
    {
        return 'Lokasi Receiving';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Lokasi Receiving';
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

    protected static string|Htmlable|null $navigationBadgeTooltip = 'Total lokasi di Receiving';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Lokasi')
                    ->description('Masukkan informasi nama lokasi penyimpanan barang.')
                    ->icon('heroicon-m-map-pin')
                    ->aside() // Membuat label deskripsi di samping (opsional)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lokasi')
                            ->placeholder('Contoh: FLOOR-A, B-01')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
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
                TextColumn::make('name')
                    ->label('Nama Lokasi')
                    ->searchable()
                    ->sortable()
                    ->copyable() // Memudahkan user copy nama lokasi
                    ->icon(Heroicon::MapPin),

                TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->dateTime('d M Y H:i')
                    ->since() // Menampilkan "2 days ago" agar lebih informatif
                    ->sortable()
                    ->color('gray')
                    ->size('sm'),

                TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime('d M Y H:i')
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
            'index' => ManageLocationReceivings::route('/'),
        ];
    }
}
