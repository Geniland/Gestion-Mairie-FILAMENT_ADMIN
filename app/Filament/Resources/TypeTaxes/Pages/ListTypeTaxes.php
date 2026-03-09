<?php

namespace App\Filament\Resources\TypeTaxes\Pages;

use App\Filament\Resources\TypeTaxes\TypeTaxeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTypeTaxes extends ListRecords
{
    protected static string $resource = TypeTaxeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
