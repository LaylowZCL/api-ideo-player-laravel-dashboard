<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LogController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\SettingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::post('/schedules', [ScheduleController::class, 'store']);
    Route::post('/schedules/{id}/toggle', [ScheduleController::class, 'toggleStatus']);
    Route::post('/schedules/{id}/duplicate', [ScheduleController::class, 'duplicate']);
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
    Route::get('/videos', [VideoController::class, 'index']);
    Route::post('/videos/{id}/sync', [VideoController::class, 'sync']);
    Route::post('/videos/{id}/delete-cache', [VideoController::class, 'deleteFromCache']);
    Route::get('/logs', [LogController::class, 'index']);
    Route::post('/logs/clear', [LogController::class, 'clear']);
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);
    Route::post('/settings/reset', [SettingController::class, 'reset']);
    Route::post('/settings/test-connection', [SettingController::class, 'testConnection']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
