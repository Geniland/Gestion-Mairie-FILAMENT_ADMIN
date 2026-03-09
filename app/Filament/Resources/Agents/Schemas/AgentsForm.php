<?php

namespace App\Filament\Resources\Agents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AgentsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('commune_id')
                    ->relationship('commune', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                TextInput::make('nom')
                    ->required(),
                TextInput::make('telephone')
                    ->tel()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                Select::make('role')
                    ->options(['super_admin' => 'Super admin', 'admin_commune' => 'Admin commune', 'agent' => 'Agent'])
                    ->required(),
            ]);
    }
}
