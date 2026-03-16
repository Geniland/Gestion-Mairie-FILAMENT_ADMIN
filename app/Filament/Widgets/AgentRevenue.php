<?php

namespace App\Filament\Widgets;

use App\Models\Agents;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class AgentRevenue extends TableWidget
{
    protected static ?string $heading = '🏆 Performance des agents';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        $user = auth()->user();

        $ticketsSub = DB::table('tickets')
            ->select('agent_id', DB::raw('COUNT(*) as total_tickets'))
            ->groupBy('agent_id');

        $payementsSub = DB::table('payements')
            ->select('agent_id', DB::raw('SUM(montant) as total_collecte'))
            ->groupBy('agent_id');

        $query = Agents::query()
            ->leftJoinSub($ticketsSub, 'tickets_summary', function ($join) {
                $join->on('agents.id', '=', 'tickets_summary.agent_id');
            })
            ->leftJoinSub($payementsSub, 'payements_summary', function ($join) {
                $join->on('agents.id', '=', 'payements_summary.agent_id');
            })
            ->select(
                'agents.id',
                'agents.nom',
                DB::raw('COALESCE(tickets_summary.total_tickets, 0) as total_tickets'),
                DB::raw('COALESCE(payements_summary.total_collecte, 0) as total_collecte')
            );

        // 👤 Si ce n'est pas un super admin → voir seulement ses données
        if (!$user->isSuperAdmin()) {
            $query->where('agents.id', $user->id);
        }

        return $table
            ->query(
                $query->orderByDesc('total_collecte')
            )

            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Agent')
                    ->icon('heroicon-m-user')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('total_tickets')
                    ->label('Tickets')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_collecte')
                    ->label('Montant Collecté')
                    ->money('XOF')
                    ->color('success')
                    ->alignEnd()
                    ->weight('bold'),
            ])

            ->striped()
            ->paginated(false);
    }
}