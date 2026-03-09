<?php

namespace App\Filament\Resources\Payements\Pages;

use App\Filament\Resources\Payements\PayementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayements extends ListRecords
{
    protected static string $resource = PayementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
