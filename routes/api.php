<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentsController;
use App\Http\Controllers\Api\CommunesController;
use App\Http\Controllers\Api\ContribuablesController;
use App\Http\Controllers\Api\PayementsController;
use App\Http\Controllers\Api\TaxesController;
use App\Http\Controllers\Api\TaxeDetailsController;
use App\Http\Controllers\Api\TypeTaxeController;
use App\Http\Controllers\Api\TicketsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\QuartierController;
use App\Http\Controllers\Api\MobileDashboardController;
use App\Http\Controllers\Api\SettingsController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'audit.log'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
});



Route::prefix('agents')->middleware(['audit.log'])->group(function () {
    Route::get('/', [AgentsController::class, 'index']);
    Route::get('/{id}', [AgentsController::class, 'show']);
    Route::post('/', [AgentsController::class, 'store']);
    Route::put('/{id}', [AgentsController::class, 'update']);
    Route::delete('/{id}', [AgentsController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'audit.log'])->group(function () {

    Route::post('/agents/{agent}/block', [AgentsController::class, 'blockAgent']);
    Route::post('/agents/{agent}/unblock', [AgentsController::class, 'unblockAgent']);

});



Route::prefix('quartiers')->middleware(['audit.log'])->group(function () {

    Route::get('/', [QuartierController::class, 'index']);
    Route::post('/', [QuartierController::class, 'store']);
    Route::get('/{id}', [QuartierController::class, 'show']);
    Route::put('/{id}', [QuartierController::class, 'update']);
    Route::delete('/{id}', [QuartierController::class, 'destroy']);

    // 🔥 IMPORTANT
    Route::get('/commune/{communeId}', [QuartierController::class, 'getByCommune']);

});

Route::middleware(['auth:sanctum', 'audit.log'])->group(function () {
    Route::get('dashboard/stats', [MobileDashboardController::class, 'stats']);
});


Route::middleware(['auth:sanctum', 'audit.log'])->group(function () {
    Route::apiResource('payements', PayementsController::class);
    Route::apiResource('taxes', TaxesController::class);
    Route::apiResource('taxe-details', TaxeDetailsController::class);
    Route::apiResource('types-taxes', TypeTaxeController::class);
    Route::apiResource('tickets', TicketsController::class);
    Route::apiResource('communes', CommunesController::class);
    Route::apiResource('contribuables', ContribuablesController::class);
    
    // Route::get('admin/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/agents', [DashboardController::class, 'agentPerformance']);
    Route::get('dashboard/revenue', [DashboardController::class, 'revenueStats']);
    Route::get('dashboard/top-taxes', [DashboardController::class, 'topTaxes']);
    Route::get('dashboard/risk-fraud', [DashboardController::class, 'riskFraud']);

    Route::get('/dashboard/best-zone', [DashboardController::class, 'bestZone']);
    Route::delete('/dashboard/notifications/{id}', [DashboardController::class, 'dismissNotification']);

    // Settings routes
    Route::get('settings', [SettingsController::class, 'index']);
    Route::post('settings', [SettingsController::class, 'store']);
    Route::get('audit-logs', [SettingsController::class, 'auditLogs']);
    Route::get('settings/backup', [SettingsController::class, 'backup']);
});


