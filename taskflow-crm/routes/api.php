<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Auth (público) ───────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
    });

    // ── Autenticado ──────────────────────────────────────────────
    Route::middleware('auth:api')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/logout',  [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me',       [AuthController::class, 'me']);
        });

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Tasks
        Route::apiResource('tasks', TaskController::class);
        Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);

        // Clients
        Route::apiResource('clients', ClientController::class);
    });
});