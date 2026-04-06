<?php

namespace App\Filament\Resources\DeliveryOrderReceiptDetails;

use App\Filament\Resources\DeliveryOrderReceiptDetails\Pages\CreateDeliveryOrderReceiptDetail;
use App\Filament\Resources\DeliveryOrderReceiptDetails\Pages\EditDeliveryOrderReceiptDetail;
use App\Filament\Resources\DeliveryOrderReceiptDetails\Pages\ListDeliveryOrderReceiptDetails;
use App\Filament\Resources\DeliveryOrderReceiptDetails\Pages\ViewDeliveryOrderReceiptDetail;
use App\Filament\Resources\DeliveryOrderReceiptDetails\Schemas\DeliveryOrderReceiptDetailForm;
use App\Filament\Resources\DeliveryOrderReceiptDetails\Schemas\DeliveryOrderReceiptDetailInfolist;
use App\Filament\Resources\DeliveryOrderReceiptDetails\Tables\DeliveryOrderReceiptDetailsTable;
use App\Models\DeliveryOrderReceiptDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliveryOrderReceiptDetailResource extends Resource
{
    protected static ?string $model = DeliveryOrderReceiptDetail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DeliveryOrderReceiptDetailForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DeliveryOrderReceiptDetailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryOrderReceiptDetailsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryOrderReceiptDetails::route('/'),
            'create' => CreateDeliveryOrderReceiptDetail::route('/create'),
            'view' => ViewDeliveryOrderReceiptDetail::route('/{record}'),
            'edit' => EditDeliveryOrderReceiptDetail::route('/{record}/edit'),
        ];
    }
}
