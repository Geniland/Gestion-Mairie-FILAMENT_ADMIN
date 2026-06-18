<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tickets;
use App\Models\PublicTaxe;
use App\Models\Contribuable;
use App\Models\Taxe;
use App\Models\Payement;
use App\Models\Commune;

echo "=== TICKETS EN BASE ===" . PHP_EOL;
$tickets = Tickets::all();
foreach ($tickets as $t) {
    echo "Ticket ID: {$t->id} | Numéro: {$t->numero_ticket} | QR hash: {$t->qr_hash}" . PHP_EOL;
    echo "  - Contribuable ID: {$t->contribuable_id} | Taxe ID: {$t->taxe_id} | Commune ID: {$t->commune_id}" . PHP_EOL;
}

echo PHP_EOL . "=== DERNIÈRE PUBLICTAXE ===" . PHP_EOL;
$latestPublicTaxe = PublicTaxe::with('ticket')->latest()->first();
if ($latestPublicTaxe) {
    var_dump($latestPublicTaxe->toArray());
    echo PHP_EOL . "  - Ticket associé: " . ($latestPublicTaxe->ticket ? json_encode($latestPublicTaxe->ticket->toArray(), JSON_PRETTY_PRINT) : "Aucun") . PHP_EOL;
}
