<?php

namespace App\Filament\Resources\Agents;

use App\Filament\Resources\Agents\Pages\CreateAgents;
use App\Filament\Resources\Agents\Pages\EditAgents;
use App\Filament\Resources\Agents\Pages\ListAgents;
use App\Filament\Resources\Agents\Schemas\AgentsForm;
use App\Filament\Resources\Agents\Tables\AgentsTable;
use App\Models\Agents;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AgentsResource extends Resource
{
    protected static ?string $model = Agents::class;

    protected static string|BackedEnum|null $navigationIcon = "heroicon-o-users";

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return AgentsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgents::route('/'),
            'create' => CreateAgents::route('/create'),
            'edit' => EditAgents::route('/{record}/edit'),
        ];
    }
}
