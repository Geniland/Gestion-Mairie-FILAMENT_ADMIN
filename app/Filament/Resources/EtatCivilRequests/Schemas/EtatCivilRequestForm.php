<?php

namespace App\Filament\Resources\EtatCivilRequests\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EtatCivilRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('nom')
                    ->required(),
                TextInput::make('telephone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('type')
                    ->required(),
                Textarea::make('details')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'en_attente' => 'En attente',
                        'en_cours' => 'En cours',
                        'valider' => 'Validée',
                        'rejeter' => 'Rejetée',
                    ])
                    ->required(),
                FileUpload::make('files')
                    ->label('Documents fournis')
                    ->multiple()
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable()
                    ->columnSpanFull(),
                Textarea::make('commentaire_admin')
                    ->columnSpanFull(),
            ]);
    }
}
