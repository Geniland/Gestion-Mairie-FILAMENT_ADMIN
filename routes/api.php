<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentsController;
<<<<<<< HEAD
=======
use App\Http\Controllers\Api\CommunesController;
use App\Http\Controllers\Api\ContribuablesController;
use App\Http\Controllers\Api\PayementsController;
use App\Http\Controllers\Api\TaxeController;
use App\Http\Controllers\Api\TaxeDetailsController;
use App\Http\Controllers\Api\TypeTaxeController;
use App\Http\Controllers\Api\TicketsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;

>>>>>>> efa4ca4 (gen committ)


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


<<<<<<< HEAD
=======
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
});

>>>>>>> efa4ca4 (gen committ)


Route::prefix('agents')->group(function () {
    Route::get('/', [AgentsController::class, 'index']);
    Route::get('/{id}', [AgentsController::class, 'show']);
    Route::post('/', [AgentsController::class, 'store']);
    Route::put('/{id}', [AgentsController::class, 'update']);
    Route::delete('/{id}', [AgentsController::class, 'destroy']);
<<<<<<< HEAD
});
=======
});


Route::apiResource('communes', CommunesController::class);


Route::apiResource('contribuables', ContribuablesController::class);


Route::apiResource('payements', PayementsController::class);


Route::apiResource('taxes', TaxeController::class);



Route::apiResource('taxe-details', TaxeDetailsController::class);


Route::apiResource('types-taxes', TypeTaxeController::class);


Route::apiResource('tickets', TicketsController::class);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard/agents', [DashboardController::class, 'agentPerformance']);
    Route::get('dashboard/revenue', [DashboardController::class, 'revenueStats']);
    Route::get('dashboard/top-taxes', [DashboardController::class, 'topTaxes']);
});


>>>>>>> efa4ca4 (gen committ)
