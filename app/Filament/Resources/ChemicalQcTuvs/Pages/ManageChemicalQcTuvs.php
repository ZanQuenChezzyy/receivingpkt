<?php

namespace App\Filament\Resources\ChemicalQcTuvs\Pages;

use App\Filament\Resources\ChemicalQcTuvs\ChemicalQcTuvResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageChemicalQcTuvs extends ManageRecords
{
    protected static string $resource = ChemicalQcTuvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
