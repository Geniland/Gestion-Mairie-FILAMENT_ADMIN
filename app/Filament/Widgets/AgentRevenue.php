<?php

namespace App\Filament\Widgets;

use App\Models\Agents;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class AgentRevenue extends TableWidget
{
    protected static ?string $heading = 'Performance des agents collecteurs';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

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
                        DB::raw('COALESCE(SUM(taxes.montant),0) as total_collecte')
                    )
                    ->groupBy('agents.id', 'agents.nom')
                    ->orderByDesc('total_collecte')
                    ->limit(5)
            )

            ->columns([

                Tables\Columns\TextColumn::make('nom')
                    ->label('Agent')
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('total_tickets')
                    ->label('Tickets émis')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_collecte')
                    ->label('Montant collecté')
                    ->money('XOF')
                    ->alignEnd()
                    ->sortable(),

            ])

            ->striped()
            ->paginated(false)
            ->defaultSort('total_collecte', 'desc');
    }
}