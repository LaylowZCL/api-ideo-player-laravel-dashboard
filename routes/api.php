<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VideoController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\VideoReportController;
use App\Http\Controllers\ClientAppController;

/*
|--------------------------------------------------------------------------
| User (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/*
|--------------------------------------------------------------------------
| Health Check (Public)
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'service' => 'Video API']);
});


/*
|--------------------------------------------------------------------------
| Public/Internal API (Consumidas pelo Laravel + Vue)
|--------------------------------------------------------------------------
| Estas rotas são abertas para a interface web da aplicação, seja Laravel
| ou componentes Vue. Não usar api.auth aqui.
|--------------------------------------------------------------------------
*/

// ==== VIDEOS ====
Route::prefix('videos')->group(function () {
    Route::get('/', [VideoController::class, 'index']);
    Route::post('/upload', [VideoController::class, 'upload']);
    Route::post('/preview', [VideoController::class, 'preview']);
    Route::post('/sync', [VideoController::class, 'sync']);
    Route::post('/{id}/download', [VideoController::class, 'download']);
    Route::delete('/{id}/cache', [VideoController::class, 'removeFromCache']);
    Route::delete('/{id}', [VideoController::class, 'destroy']);

    // Reports (internos)
    Route::get('/report/stats', [VideoReportController::class, 'stats']);
    Route::get('/{videoId}/reports', [VideoReportController::class, 'videoReports']);
});

// ==== SCHEDULES ====
Route::prefix('schedules')->group(function () {
    Route::get('/', [ScheduleController::class, 'index']);
    Route::post('/', [ScheduleController::class, 'store']);
    Route::delete('/{id}', [ScheduleController::class, 'destroy']);
    Route::put('/{id}/status', [ScheduleController::class, 'toggleStatus']);
    Route::post('/{id}/duplicate', [ScheduleController::class, 'duplicate']);
});

// ==== SETTINGS ====
Route::prefix('settings')->group(function () {
    Route::get('/', [SettingController::class, 'index']);
    Route::post('/', [SettingController::class, 'store']);
});

// ==== SYSTEM SETTINGS ====
Route::prefix('system-settings')->group(function () {
    Route::get('/', [SystemSettingController::class, 'index']);
    Route::post('/', [SystemSettingController::class, 'store']);
    Route::post('/test-connection', [SystemSettingController::class, 'testConnection']);
    Route::post('/restore-defaults', [SystemSettingController::class, 'restoreDefaults']);
    Route::get('/history', [SystemSettingController::class, 'history']);
    Route::get('/export', [SystemSettingController::class, 'export']);
});


/*
|--------------------------------------------------------------------------
| Protected API (Electron App)
|--------------------------------------------------------------------------
| **IMPORTANTE:** Estas rotas continuam exactamente juntas, como pediste.
| A app Electron consome apenas estas e precisam de segurança dedicada.
|--------------------------------------------------------------------------
*/


Route::middleware(['api.auth'])->group(function () {
    // Schedules (apenas Electron)
    Route::get('/schedules/clients', [ClientAppController::class, 'schedules']);

    // Videos agendados (Electron)
    Route::get('/scheduled/videos', [ClientAppController::class, 'scheduledVideos']);

    // Reports (Electron)
    Route::post('/videos/report', [ClientAppController::class, 'storeReport']);
});
