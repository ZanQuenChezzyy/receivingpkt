<?php

namespace App\Filament\Resources\MonitoringNpkDetails;

use App\Filament\Resources\MonitoringNpkDetails\Pages\CreateMonitoringNpkDetail;
use App\Filament\Resources\MonitoringNpkDetails\Pages\EditMonitoringNpkDetail;
use App\Filament\Resources\MonitoringNpkDetails\Pages\ListMonitoringNpkDetails;
use App\Filament\Resources\MonitoringNpkDetails\Pages\ViewMonitoringNpkDetail;
use App\Filament\Resources\MonitoringNpkDetails\Schemas\MonitoringNpkDetailForm;
use App\Filament\Resources\MonitoringNpkDetails\Schemas\MonitoringNpkDetailInfolist;
use App\Filament\Resources\MonitoringNpkDetails\Tables\MonitoringNpkDetailsTable;
use App\Models\MonitoringNpkDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MonitoringNpkDetailResource extends Resource
{
    protected static ?string $model = MonitoringNpkDetail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MonitoringNpkDetailForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MonitoringNpkDetailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonitoringNpkDetailsTable::configure($table);
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
            'index' => ListMonitoringNpkDetails::route('/'),
            'create' => CreateMonitoringNpkDetail::route('/create'),
            'view' => ViewMonitoringNpkDetail::route('/{record}'),
            'edit' => EditMonitoringNpkDetail::route('/{record}/edit'),
        ];
    }
}
