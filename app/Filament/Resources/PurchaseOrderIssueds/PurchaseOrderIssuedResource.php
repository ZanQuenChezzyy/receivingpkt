<?php

namespace App\Filament\Resources\PurchaseOrderIssueds;

use App\Filament\Resources\PurchaseOrderIssueds\Pages\CreatePurchaseOrderIssued;
use App\Filament\Resources\PurchaseOrderIssueds\Pages\EditPurchaseOrderIssued;
use App\Filament\Resources\PurchaseOrderIssueds\Pages\ListPurchaseOrderIssueds;
use App\Filament\Resources\PurchaseOrderIssueds\Pages\ViewPurchaseOrderIssued;
use App\Filament\Resources\PurchaseOrderIssueds\Schemas\PurchaseOrderIssuedForm;
use App\Filament\Resources\PurchaseOrderIssueds\Schemas\PurchaseOrderIssuedInfolist;
use App\Filament\Resources\PurchaseOrderIssueds\Tables\PurchaseOrderIssuedsTable;
use App\Models\PurchaseOrderIssued;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class PurchaseOrderIssuedResource extends Resource
{
    protected static ?string $model = PurchaseOrderIssued::class;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'purchase_order_and_item';

    protected static ?int $navigationSort = 25;

    protected static ?string $slug = 'purchase-order-terbit';

    public static function getNavigationLabel(): string
    {
        return 'Purchase Order Terbit';
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

    protected static string|Htmlable|null $navigationBadgeTooltip = 'Total PO Terbit';

    public static function form(Schema $schema): Schema
    {
        return PurchaseOrderIssuedForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PurchaseOrderIssuedInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseOrderIssuedsTable::configure($table);
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
            'index' => ListPurchaseOrderIssueds::route('/'),
            'create' => CreatePurchaseOrderIssued::route('/create'),
            'view' => ViewPurchaseOrderIssued::route('/{record}'),
            'edit' => EditPurchaseOrderIssued::route('/{record}/edit'),
        ];
    }
}
