<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ClientAppController;
use App\Http\Controllers\ClientMonitorController;

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


Route::middleware(['api.auth', 'client.auth'])->group(function () {
    // Schedules (apenas Electron)
    Route::get('/schedules/clients', [ClientAppController::class, 'schedules']);

    // Videos agendados (Electron)
    Route::get('/scheduled/videos', [ClientAppController::class, 'scheduledVideos']);
    Route::get('/scheduled/videos/next', [ClientAppController::class, 'scheduledVideosNext']);

    // Subtitles (legendas para vídeos)
    Route::get('/subtitles/{schedule_id}', [ClientAppController::class, 'getSubtitle']);

    // Reports (Electron)
    Route::post('/videos/report', [ClientAppController::class, 'storeReport']);

    // Ping/heartbeat (chamado pela app Electron)
    Route::post('/ping', [ClientMonitorController::class, 'ping']);
});
