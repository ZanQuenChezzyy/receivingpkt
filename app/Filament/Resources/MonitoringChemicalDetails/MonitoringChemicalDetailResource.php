<?php

namespace App\Filament\Resources\MonitoringChemicalDetails;

use App\Filament\Resources\MonitoringChemicalDetails\Pages\CreateMonitoringChemicalDetail;
use App\Filament\Resources\MonitoringChemicalDetails\Pages\EditMonitoringChemicalDetail;
use App\Filament\Resources\MonitoringChemicalDetails\Pages\ListMonitoringChemicalDetails;
use App\Filament\Resources\MonitoringChemicalDetails\Pages\ViewMonitoringChemicalDetail;
use App\Filament\Resources\MonitoringChemicalDetails\Schemas\MonitoringChemicalDetailForm;
use App\Filament\Resources\MonitoringChemicalDetails\Schemas\MonitoringChemicalDetailInfolist;
use App\Filament\Resources\MonitoringChemicalDetails\Tables\MonitoringChemicalDetailsTable;
use App\Models\MonitoringChemicalDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MonitoringChemicalDetailResource extends Resource
{
    protected static ?string $model = MonitoringChemicalDetail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MonitoringChemicalDetailForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MonitoringChemicalDetailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonitoringChemicalDetailsTable::configure($table);
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
            'index' => ListMonitoringChemicalDetails::route('/'),
            'create' => CreateMonitoringChemicalDetail::route('/create'),
            'view' => ViewMonitoringChemicalDetail::route('/{record}'),
            'edit' => EditMonitoringChemicalDetail::route('/{record}/edit'),
        ];
    }
}
