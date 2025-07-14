<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VideoController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotas para Vídeos
Route::get('/videos', [VideoController::class, 'index']);
Route::post('/videos/sync', [VideoController::class, 'sync']);
Route::post('/videos/{id}/download', [VideoController::class, 'download']);
Route::delete('/videos/{id}/cache', [VideoController::class, 'removeFromCache']);
Route::post('/videos/upload', [VideoController::class, 'upload']);
Route::post('/videos/preview', [VideoController::class, 'preview']);

// Rotas para Agendamentos
Route::get('/schedules', [ScheduleController::class, 'index']);
Route::post('/schedules', [ScheduleController::class, 'store']);
Route::put('/schedules/{id}/status', [ScheduleController::class, 'toggleStatus']);
Route::post('/schedules/{id}/duplicate', [ScheduleController::class, 'duplicate']);
Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);

// Rotas para Configurações
Route::get('/settings', [SettingController::class, 'index']);
Route::post('/settings', [SettingController::class, 'store']);
