<?php

namespace App\Filament\Resources\Payements\Pages;

use App\Filament\Resources\Payements\PayementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPayement extends EditRecord
{
    protected static string $resource = PayementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
