<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\SettingController;


Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/videos', [VideoController::class, 'goToVideos'])->name('videos');
Route::get('/schedule', [ScheduleController::class, 'goToSchedule'])->name('schedule');
Route::get('/logs', [LogController::class, 'goToLogs'])->name('logs');
Route::get('/preview', [PreviewController::class, 'goToPreview'])->name('preview');
Route::get('/settings', [SettingController::class, 'goToSettings'])->name('settings');

