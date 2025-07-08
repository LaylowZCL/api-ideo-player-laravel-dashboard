<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $settings = Setting::first() ?? new Setting([
            'api_endpoint' => 'https://api.empresa.com/videos',
            'api_key' => '',
            'sync_interval' => 30,
            'default_monitor' => 'principal',
            'always_on_top' => true,
            'auto_close_delay' => 0,
            'start_with_windows' => true,
            'show_in_system_tray' => true,
            'enable_notifications' => true,
            'cache_location' => 'C:\\VideoScheduler\\Cache',
            'max_cache_size' => 5,
            'auto_cleanup' => true,
            'log_level' => 'info',
            'max_log_files' => 10,
            'enable_auto_update' => true,
            'max_memory_usage' => 200,
            'enable_hardware_acceleration' => true,
            'preload_videos' => true
        ]);

        return view('dashboard.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'api_endpoint' => 'required|url',
            'sync_interval' => 'required|integer|min:5|max:1440',
            'max_cache_size' => 'required|integer|min:1|max:100',
            'max_memory_usage' => 'required|integer|min:50|max:1000',
            'default_monitor' => 'required|in:principal,secundario,todos',
            'log_level' => 'required|in:error,warning,info,debug'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $settings = Setting::first() ?? new Setting();
        $settings->fill($request->all())->save();

        return response()->json(['message' => 'Configurações salvas com sucesso']);
    }

    public function reset()
    {
        $settings = Setting::first() ?? new Setting();
        $settings->fill([
            'api_endpoint' => 'https://api.empresa.com/videos',
            'api_key' => '',
            'sync_interval' => 30,
            'default_monitor' => 'principal',
            'always_on_top' => true,
            'auto_close_delay' => 0,
            'start_with_windows' => true,
            'show_in_system_tray' => true,
            'enable_notifications' => true,
            'cache_location' => 'C:\\VideoScheduler\\Cache',
            'max_cache_size' => 5,
            'auto_cleanup' => true,
            'log_level' => 'info',
            'max_log_files' => 10,
            'enable_auto_update' => true,
            'max_memory_usage' => 200,
            'enable_hardware_acceleration' => true,
            'preload_videos' => true
        ])->save();

        return response()->json(['message' => 'Configurações restauradas com sucesso']);
    }

    public function testConnection(Request $request)
    {
        // Simular teste de conexão
        $endpoint = $request->input('api_endpoint');
        if (!$endpoint) {
            return response()->json(['message' => 'Informe o endpoint da API primeiro'], 422);
        }

        // Simulação de sucesso/falha
        $success = rand(0, 100) > 30;
        if ($success) {
            return response()->json(['message' => 'Conexão bem-sucedida']);
        } else {
            return response()->json(['message' => 'Erro de conexão'], 500);
        }
    }
}
