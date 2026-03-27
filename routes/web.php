<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\VideoReportController;
use App\Http\Controllers\ClientMonitorController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AdGroupController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AdHealthController;
use App\Http\Controllers\AdTargetController;
use App\Http\Controllers\Auth\TwoFactorController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::view('/documentacao', 'documentacao.index')->name('documentacao.index');
Route::view('/documentacao/index', 'documentacao.index')->name('documentacao.index.page');
Route::view('/documentacao/manual-solucao-mista', 'documentacao.manuais.manual-solucao-mista')->name('documentacao.manual-solucao-mista');
Route::view('/documentacao/manual-api', 'documentacao.manuais.manual-api')->name('documentacao.manual-api');
Route::view('/documentacao/manual-dashboard-web', 'documentacao.manuais.manual-dashboard-web')->name('documentacao.manual-dashboard-web');
Route::view('/documentacao/manual-app-electron', 'documentacao.manuais.manual-app-electron')->name('documentacao.manual-app-electron');
Route::view('/documentacao/ficha-tecnica', 'documentacao.manuais.ficha-tecnica')->name('documentacao.ficha-tecnica');

if (config('ad.dashboard_uses_ad')) {
    Auth::routes([
        'register' => false,
        'reset' => false,
        'confirm' => false,
        'verify' => false,
    ]);
} else {
    Auth::routes();
}

Route::middleware(['auth'])->group(function () {
    Route::get('/two-factor/setup', [TwoFactorController::class, 'showSetup'])->name('two-factor.setup');
    Route::post('/two-factor/setup', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verifyChallenge'])->name('two-factor.verify');
    Route::post('/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
});

Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/api/dashboard', [DashboardController::class, 'getDashboardData'])->middleware('api.auth');
Route::view('/relatorios', 'reports')->name('reports');
Route::get('/video', [VideoController::class, 'goToVideos'])->name('videos');
Route::get('/schedule', [ScheduleController::class, 'goToSchedule'])->name('schedule');
Route::get('/logs', [LogController::class, 'goToLogs'])->name('logs');

// Admin views
Route::view('/admin', 'admin.index')->middleware(['auth', 'can:isAdmin'])->name('admin.index');
Route::view('/admin/grupos', 'admin.ad-groups')->middleware(['auth', 'can:isAdmin'])->name('admin.groups');
Route::view('/admin/clientes', 'admin.clients')->middleware(['auth', 'can:isAdmin'])->name('admin.clients');
Route::view('/admin/campanhas', 'admin.campaigns')->middleware(['auth', 'can:isAdmin'])->name('admin.campaigns');
Route::view('/admin/logs', 'admin.logs')->middleware(['auth', 'can:isAdmin'])->name('admin.logs');
Route::view('/admin/alvos', 'admin.ad-targets')->middleware(['auth', 'can:isAdmin'])->name('admin.targets');
Route::get('/preview', [PreviewController::class, 'goToPreview'])->name('preview');
Route::get('/settings', [SettingController::class, 'goToSettings'])->name('settings');
Route::get('/users', [UserController::class, 'goToUsers'])->name('users');

Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/current-user', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_type' => $user->user_type ?? 'user',
            'role' => $user->roleName(),
            'permissions' => $user->permissionList(),
        ]);
    });

    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData']);

    // ==== VIDEOS ====
    Route::prefix('videos')->group(function () {
        Route::get('/', [VideoController::class, 'index']);
        Route::post('/upload', [VideoController::class, 'upload']);
        Route::post('/preview', [VideoController::class, 'preview']);
        Route::post('/sync', [VideoController::class, 'sync']);
        Route::post('/{id}/download', [VideoController::class, 'download']);
        Route::put('/{id}', [VideoController::class, 'update']);
        Route::delete('/{id}/cache', [VideoController::class, 'removeFromCache']);
        Route::delete('/{id}', [VideoController::class, 'destroy']);

        // Reports (internos)
        Route::get('/report/stats', [VideoReportController::class, 'stats']);
        Route::get('/{videoId}/reports', [VideoReportController::class, 'videoReports']);
    });

    // ==== REPORTS ====
    Route::get('/reports', [VideoReportController::class, 'index']);
    Route::get('/reports/export', [VideoReportController::class, 'exportExcel']);
    Route::post('/reports/export-email', [VideoReportController::class, 'emailExcel']);

    // ==== SCHEDULES ====
    Route::prefix('schedules')->group(function () {
        Route::get('/', [ScheduleController::class, 'index']); // Listar todos
        Route::get('/videos', [ScheduleController::class, 'getVideosForDropdown']); // Vídeos para dropdown
        Route::get('/today', [ScheduleController::class, 'scheduledVideosToday']); // Agendamentos de hoje
        Route::get('/player', [ScheduleController::class, 'getScheduleForPlayer']); // Para player externo

        Route::post('/', [ScheduleController::class, 'store']); // Criar
        Route::put('/{id}', [ScheduleController::class, 'update']); // Atualizar
        Route::post('/{id}/toggle', [ScheduleController::class, 'toggleStatus']); // Alternar status
        Route::post('/{id}/duplicate', [ScheduleController::class, 'duplicate']); // Duplicar
        Route::delete('/{id}', [ScheduleController::class, 'destroy']); // Excluir
    });

    // ==== USERS ====
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // ==== CAMPAIGNS ====
    Route::prefix('campaigns')->group(function () {
        Route::get('/', [CampaignController::class, 'index']);
        Route::post('/', [CampaignController::class, 'store']);
        Route::put('/{id}', [CampaignController::class, 'update']);
        Route::delete('/{id}', [CampaignController::class, 'destroy']);
    });

    // ==== AD GROUPS ====
    Route::prefix('ad-groups')->group(function () {
        Route::get('/', [AdGroupController::class, 'index']);
        Route::post('/', [AdGroupController::class, 'store']);
        Route::put('/{id}', [AdGroupController::class, 'update']);
        Route::delete('/{id}', [AdGroupController::class, 'destroy']);
    });

    // ==== CLIENTS ====
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::put('/{id}', [ClientController::class, 'update']);
    });

    // ==== LOGS ====
    Route::prefix('logs')->group(function () {
        Route::get('/', [LogController::class, 'indexJson']);
        Route::delete('/', [LogController::class, 'clearJson']);
        Route::get('/export', [LogController::class, 'export']);
    });

    // ==== AD HEALTH / TARGETS ====
    Route::get('/ad/health', [AdHealthController::class, 'health']);
    Route::get('/ad/json-status', [AdHealthController::class, 'jsonStatus']);
    Route::get('/ad-targets', [AdTargetController::class, 'index']);

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

    // Compatibilidade: endpoints com prefixo /api utilizados pelo Vue
    Route::prefix('api/system-settings')->group(function () {
        Route::get('/', [SystemSettingController::class, 'index']);
        Route::post('/', [SystemSettingController::class, 'store']);
        Route::post('/test-connection', [SystemSettingController::class, 'testConnection']);
        Route::post('/restore-defaults', [SystemSettingController::class, 'restoreDefaults']);
        Route::get('/history', [SystemSettingController::class, 'history']);
        Route::get('/export', [SystemSettingController::class, 'export']);
    });

    // Monitoramento de clientes (simples)
    Route::prefix('client')->group(function () {
        Route::get('/stats', [ClientMonitorController::class, 'stats']);
        Route::get('/online', [ClientMonitorController::class, 'online']);
    });

    // Dashboard admin (protegido)
    Route::get('/admin/clients', [ClientMonitorController::class, 'dashboard']);
});

Route::get('/clear-all', function () {
    $user = auth()->user();
    if (!$user || !$user->can('isAdmin')) {
        abort(403, 'Unauthorized. Admin access required.');
    }

    \Illuminate\Support\Facades\Artisan::call('optimize:clear');

    return response()->json([
        'success' => true,
        'message' => 'Caches limpos com sucesso.',
    ]);
})->middleware(['auth', 'throttle:10,1'])->name('ops.clear-all');
