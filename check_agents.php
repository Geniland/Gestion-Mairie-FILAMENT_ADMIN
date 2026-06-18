<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Agents;

echo "=== Liste des agents ===\n\n";
$agents = Agents::all(['id', 'nom', 'email', 'role', 'commune_id', 'is_blocked']);

foreach ($agents as $agent) {
    echo "ID: {$agent->id}\n";
    echo "Nom: {$agent->nom}\n";
    echo "Email: {$agent->email}\n";
    echo "Rôle: {$agent->role}\n";
    echo "Commune ID: {$agent->commune_id}\n";
    echo "Bloqué: " . ($agent->is_blocked ? 'Oui' : 'Non') . "\n";
    echo "-------------------------\n";
}
