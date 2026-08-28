<?php

use App\Http\Controllers\Api\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\CustomersController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\LocationsController;
use App\Http\Controllers\Api\TechniciansController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
        Route::get('/me', [AuthenticatedSessionController::class, 'show']);

        Route::apiResource('users', UsersController::class);
        Route::apiResource('technicians', TechniciansController::class)->only(['index', 'show']);
        Route::apiResource('customers', CustomersController::class);
        Route::apiResource('locations', LocationsController::class);
        Route::apiResource('equipment', EquipmentController::class);
    });
});
