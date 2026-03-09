<?php

namespace App\Filament\Resources\Taxes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaxesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('commune.nom'),

                TextColumn::make('contribuable.nom')
                    ->label('Contribuable'),

                TextColumn::make('typeTaxe.nom')
                    ->label('Type taxe'),

                TextColumn::make('montant')
                    ->money('XOF'),

                TextColumn::make('agent.nom')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('montant')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('periode_debut')
                    ->date()
                    ->sortable(),
                TextColumn::make('periode_fin')
                    ->date()
                    ->sortable(),
                TextColumn::make('statut')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
