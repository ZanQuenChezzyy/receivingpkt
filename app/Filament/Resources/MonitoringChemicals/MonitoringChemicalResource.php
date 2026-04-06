<?php

namespace App\Filament\Resources\MonitoringChemicals;

use App\Filament\Resources\MonitoringChemicals\Pages\CreateMonitoringChemical;
use App\Filament\Resources\MonitoringChemicals\Pages\EditMonitoringChemical;
use App\Filament\Resources\MonitoringChemicals\Pages\ListMonitoringChemicals;
use App\Filament\Resources\MonitoringChemicals\Pages\ViewMonitoringChemical;
use App\Filament\Resources\MonitoringChemicals\Schemas\MonitoringChemicalForm;
use App\Filament\Resources\MonitoringChemicals\Schemas\MonitoringChemicalInfolist;
use App\Filament\Resources\MonitoringChemicals\Tables\MonitoringChemicalsTable;
use App\Models\MonitoringChemical;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MonitoringChemicalResource extends Resource
{
    protected static ?string $model = MonitoringChemical::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MonitoringChemicalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MonitoringChemicalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonitoringChemicalsTable::configure($table);
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
            'index' => ListMonitoringChemicals::route('/'),
            'create' => CreateMonitoringChemical::route('/create'),
            'view' => ViewMonitoringChemical::route('/{record}'),
            'edit' => EditMonitoringChemical::route('/{record}/edit'),
        ];
    }
}
