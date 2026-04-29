<?php

namespace App\Filament\Resources\EtatCivilRequests\Pages;

use App\Filament\Resources\EtatCivilRequests\EtatCivilRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEtatCivilRequest extends EditRecord
{
    protected static string $resource = EtatCivilRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
