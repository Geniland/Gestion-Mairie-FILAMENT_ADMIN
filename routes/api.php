<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentsController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::prefix('agents')->group(function () {
    Route::get('/', [AgentsController::class, 'index']);
    Route::get('/{id}', [AgentsController::class, 'show']);
    Route::post('/', [AgentsController::class, 'store']);
    Route::put('/{id}', [AgentsController::class, 'update']);
    Route::delete('/{id}', [AgentsController::class, 'destroy']);
});