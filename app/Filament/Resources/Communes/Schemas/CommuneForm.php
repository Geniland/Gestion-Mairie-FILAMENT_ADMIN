<?php

namespace App\Filament\Resources\Communes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CommuneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->required(),
                TextInput::make('region')
                    ->required(),
                TextInput::make('quartier')
                    ->required(),
            ]);
    }
}
