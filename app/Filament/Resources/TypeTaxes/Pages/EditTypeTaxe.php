<?php

namespace App\Filament\Resources\TypeTaxes\Pages;

use App\Filament\Resources\TypeTaxes\TypeTaxeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTypeTaxe extends EditRecord
{
    protected static string $resource = TypeTaxeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
