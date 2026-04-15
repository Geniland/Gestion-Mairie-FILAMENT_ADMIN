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
use App\Http\Controllers\Public\ContentController as PublicContentController;
use App\Http\Controllers\Public\TaxesController as PublicTaxesController;
use App\Http\Controllers\Public\PaymentsController as PublicPaymentsController;
use App\Http\Controllers\Public\EtatCivilController as PublicEtatCivilController;



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

    //Public API (site web)
Route::prefix('public')->group(function () {
    Route::get('services', [PublicContentController::class, 'services']);
    Route::get('actualites', [PublicContentController::class, 'actualites']);
    Route::get('actualites/{id}', [PublicContentController::class, 'actualite']);
    
    // Admin CRUD for Public Content
    Route::middleware(['auth:sanctum', 'audit.log'])->group(function () {
        Route::post('admin/services', [PublicContentController::class, 'storeService']);
        Route::put('admin/services/{id}', [PublicContentController::class, 'updateService']);
        Route::delete('admin/services/{id}', [PublicContentController::class, 'destroyService']);
        
        Route::post('admin/actualites', [PublicContentController::class, 'storeActualite']);
        Route::put('admin/actualites/{id}', [PublicContentController::class, 'updateActualite']);
        Route::delete('admin/actualites/{id}', [PublicContentController::class, 'destroyActualite']);
    });
    
    Route::post('login', [PublicEtatCivilController::class, 'login']);
    Route::post('register', [PublicEtatCivilController::class, 'register']);
    Route::post('forgot-password', [PublicEtatCivilController::class, 'forgotPassword']);

    // Routes nécessitant une connexion utilisateur public
    Route::middleware('auth:sanctum')->group(function () {
        // Route::get('etat-civil', [PublicEtatCivilController::class, 'index']);
        Route::get('etat-civil', [PublicEtatCivilController::class, 'index']);
        Route::get('etat-civil/historique', [PublicEtatCivilController::class, 'historique']);
        Route::post('etat-civil', [PublicEtatCivilController::class, 'store']);

        Route::post('taxes', [PublicTaxesController::class, 'store']);
        Route::get('taxes', [PublicTaxesController::class, 'index']);
        
        Route::get('payments', [PublicPaymentsController::class, 'index']);
        Route::post('payments/initiate', [PublicPaymentsController::class, 'initiate']);
        Route::post('logout', [PublicEtatCivilController::class, 'logout']);
    });
    
    Route::post('payments/callback', [PublicPaymentsController::class, 'callback']); // Webhook/callback
});









Route::middleware(['auth:sanctum', 'audit.log'])->group(function () {
    Route::apiResource('payements', PayementsController::class);
    Route::apiResource('taxes', TaxesController::class);
    Route::get('admin/public-taxes', [TaxesController::class, 'listPublicTaxes']);
    Route::post('admin/public-taxes/{id}/approve', [TaxesController::class, 'approvePublicTaxe']);
    Route::post('admin/public-taxes/{id}/reject', [TaxesController::class, 'rejectPublicTaxe']);
    Route::apiResource('taxe-details', TaxeDetailsController::class);
    Route::apiResource('types-taxes', TypeTaxeController::class);
    Route::apiResource('tickets', TicketsController::class);
    Route::apiResource('communes', CommunesController::class);
    Route::apiResource('contribuables', ContribuablesController::class);
    Route::get('public-users', [ContribuablesController::class, 'listPublicUsers']);
    
    // Nouvelles routes pour la gestion du portail public par l'admin
    Route::get('admin/online-payments', [PublicPaymentsController::class, 'adminIndex']);
    Route::post('admin/online-payments/{id}/validate', [PublicPaymentsController::class, 'adminValidate']);
    Route::get('admin/etat-civil-requests', [PublicEtatCivilController::class, 'adminIndex']);
    Route::post('admin/etat-civil-requests/{id}/approve', [PublicEtatCivilController::class, 'adminApprove']);
    Route::post('admin/etat-civil-requests/{id}/reject', [PublicEtatCivilController::class, 'adminReject']);
    Route::post('admin/etat-civil-requests/{id}/upload-document', [PublicEtatCivilController::class, 'uploadDocument']);
    
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

