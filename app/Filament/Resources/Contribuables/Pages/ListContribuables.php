<?php

namespace App\Filament\Resources\Contribuables\Pages;

use App\Filament\Resources\Contribuables\ContribuableResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContribuables extends ListRecords
{
    protected static string $resource = ContribuableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
