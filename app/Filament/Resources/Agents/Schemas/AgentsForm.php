<?php

namespace App\Filament\Resources\Agents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AgentsForm
{
    public static function configure(Schema $schema): Schema
    {
        $agent = auth()->guard('web')->user();
        $isSuperAdmin = $agent && $agent->isSuperAdmin();

        // Définir les rôles disponibles selon l'utilisateur connecté
        $roleOptions = $isSuperAdmin 
            ? ['super_admin' => 'Super admin', 'maire' => 'Maire', 'agent' => 'Agent'] 
            : ['agent' => 'Agent']; // Seul super admin peut créer maire et super admin

        return $schema
            ->components([
                Select::make('role')
                    ->options($roleOptions)
                    ->default($isSuperAdmin ? 'agent' : 'agent')
                    ->required()
                    ->live(), // Important pour que le champ commune se mette à jour dynamiquement
                    
                Select::make('commune_id')
                    ->relationship('commune', 'nom')
                    ->searchable()
                    ->preload()
                    ->label('Commune')
                    ->required(), // Toujours requis car la base de données l'exige
                    
                TextInput::make('nom')
                    ->required(),
                TextInput::make('telephone')
                    ->tel()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
            ]);
    }
}
