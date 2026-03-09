<?php

namespace App\Filament\Resources\Contribuables\Pages;

use App\Filament\Resources\Contribuables\ContribuableResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContribuable extends EditRecord
{
    protected static string $resource = ContribuableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
