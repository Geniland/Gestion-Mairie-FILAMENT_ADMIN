<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ticket/{id}/print', function ($id) {
    $ticket = \App\Models\Tickets::with(
        ['taxe.typeTaxe',
                    'commune',
                    'contribuable'
        ])->findOrFail($id);

    return view('tickets.print', compact('ticket'));
});

// Routes publiques de vérification de ticket (API)
Route::get('/api/v/{hash}', [\App\Http\Controllers\PublicTicketController::class, 'verify']);

// Servir l'application VueJS pour toutes les autres routes non-API
Route::get('/{any}', function () {
    $path = public_path('app/index.html');
    if (!file_exists($path)) {
        return "L'application VueJS n'est pas encore buildée dans public/app/. Exécutez 'npm run build' dans le dossier 'gestion' et copiez le contenu de 'dist' dans 'public/app/'.";
    }
    return file_get_contents($path);
})->where('any', '^(?!api|v1|storage).*$');

// Route spécifique pour /v/{hash} pour s'assurer qu'elle charge aussi l'app Vue
Route::get('/v/{hash}', function () {
    return file_get_contents(public_path('app/index.html'));
});



