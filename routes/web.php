<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get('/change-password-static', function () {
    $user = User::where('email', 'fernandozucula@gmail.com')->first();

    if ($user) {
        // Mudar a senha para uma nova senha (exemplo: 'novaSenha123')
        $user->password = Hash::make('20002004');
        $user->save();

        return "Senha alterada com sucesso!";
    }

    return "Usuário não encontrado.";
});


Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/api/dashboard', [DashboardController::class, 'dashboard']);
Route::get('/video', [VideoController::class, 'goToVideos'])->name('videos');
Route::get('/schedule', [ScheduleController::class, 'goToSchedule'])->name('schedule');
Route::get('/logs', [LogController::class, 'goToLogs'])->name('logs');
Route::get('/preview', [PreviewController::class, 'goToPreview'])->name('preview');
Route::get('/settings', [SettingController::class, 'goToSettings'])->name('settings');

Route::get('/clear-all', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return 'Todo cache foi limpo';
});