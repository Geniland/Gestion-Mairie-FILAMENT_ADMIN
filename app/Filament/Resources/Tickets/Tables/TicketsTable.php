<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('taxe_id')
                //     ->numeric()
                //     ->sortable(),

                TextColumn::make('contribuable.nom')
                    ->label('Contribuable'),
             

                TextColumn::make('taxe.nom_taxe')
                    ->label('Taxe'),
                TextColumn::make('commune.nom')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('numero_ticket')
                    ->searchable(),
                TextColumn::make('qr_hash')
                    ->searchable(),
                TextColumn::make('date_expiration')
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
                Action::make('print')
                    ->label('Imprimer')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => url('/ticket/'.$record->id.'/print'))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),

            
            ]);
    }
}
