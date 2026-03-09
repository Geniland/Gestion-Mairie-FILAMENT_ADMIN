<?php

namespace App\Filament\Widgets;

use App\Models\Agents;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class AgentRevenue extends TableWidget
{
    protected static ?string $heading = 'Recettes par agent';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Agents::query()
                    ->leftJoin('taxes', 'agents.id', '=', 'taxes.agent_id')
                    ->leftJoin('tickets', 'taxes.id', '=', 'tickets.taxe_id')
                    ->select(
                        'agents.id',
                        'agents.nom',
                        DB::raw('COUNT(tickets.id) as total_tickets'),
                        DB::raw('SUM(taxes.montant) as total_collecte')
                    )
                    ->groupBy('agents.id', 'agents.nom')
            )

            ->columns([

                Tables\Columns\TextColumn::make('nom')
                    ->label('Agent')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_tickets')
                    ->label('Tickets émis')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_collecte')
                    ->label('Montant collecté')
                    ->money('XOF')
                    ->sortable(),
            ]);
    }
}