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

// Routes publiques de vérification de ticket
Route::get('/v/{hash}', [\App\Http\Controllers\PublicTicketController::class, 'show'])->name('ticket.verify');
Route::get('/api/v/{hash}', [\App\Http\Controllers\PublicTicketController::class, 'verify']);



