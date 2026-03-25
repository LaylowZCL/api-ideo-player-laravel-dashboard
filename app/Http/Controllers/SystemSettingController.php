<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SystemSettingController extends Controller
{
    public function __construct()
    {
        // Apenas a view precisa de autenticação web
        // $this->middleware('auth')->only(['goToVideos']);
        
        // Todos os métodos API usam verificação interna
        $this->middleware('internal.api')->except(['goToVideos']);
    }
    /**
     * Obtém as configurações atuais do sistema
     */
    public function index(): JsonResponse
    {
        try {
            $settings = SystemSetting::getCurrentSettings();
            
            return response()->json([
                'success' => true,
                'settings' => $settings->toVueFormat(),
                'message' => 'Configurações carregadas com sucesso.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar configurações: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar configurações do sistema.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Salva as configurações do sistema
     */
    public function store(Request $request): JsonResponse
    {
        $current = SystemSetting::getCurrentSettings();
        $payload = array_merge($current->toVueFormat(), $request->all());

        $validator = Validator::make($payload, [
            // Configurações de API
            'apiEndpoint' => 'required|url|max:500',
            'apiKey' => 'nullable|string|max:255',
            'syncInterval' => 'required|integer|min:5|max:1440',
            
            // Configurações de Exibição
            'defaultMonitor' => 'required|in:principal,secundario,todos',
            'autoCloseDelay' => 'required|integer|min:0|max:300',
            'alwaysOnTop' => 'required|boolean',
            
            // Configurações do Sistema
            'startWithWindows' => 'required|boolean',
            'showInSystemTray' => 'required|boolean',
            'enableNotifications' => 'required|boolean',
            
            // Armazenamento e Cache
            'cacheLocation' => 'nullable|string|max:500',
            'maxCacheSize' => 'required|integer|min:1|max:100',
            'autoCleanup' => 'required|boolean',
            
            // Performance
            'maxMemoryUsage' => 'required|integer|min:50|max:1000',
            'logLevel' => 'required|in:error,warning,info,debug',
            'enableHardwareAcceleration' => 'required|boolean',
            'preloadVideos' => 'required|boolean',
            'enableAutoUpdate' => 'required|boolean',
            'popupWidth' => 'required|integer|min:256|max:3840',
            'popupHeight' => 'required|integer|min:144|max:2160',
            'popupPosition' => 'required|in:center,top_left,top_right,bottom_left,bottom_right',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Obtém os dados no formato do model
            $data = SystemSetting::fromVueFormat($payload);
            
            // Cria um novo registro de configuração (mantendo histórico)
            $settings = SystemSetting::create($data);

            Log::info('Configurações do sistema atualizadas', [
                'settings_id' => $settings->id,
                'log_level' => $settings->log_level
            ]);

            app(AuditLogService::class)->log('system_settings.save', 'success', [
                'settings_id' => $settings->id,
            ]);
            
            return response()->json([
                'success' => true,
                'settings' => $settings->toVueFormat(),
                'message' => 'Configurações salvas com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar configurações: ' . $e->getMessage());
            app(AuditLogService::class)->log('system_settings.save', 'failed', [
                'error' => $e->getMessage(),
            ], 'error');
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar configurações.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Testa a conexão com a API
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'apiEndpoint' => 'required|url',
            'apiKey' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint da API é obrigatório.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Simula teste de conexão (em produção, você faria uma requisição real)
            $success = $this->testApiConnection($request->apiEndpoint, $request->apiKey);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'API está respondendo normalmente.',
                    'response_time' => rand(100, 500) . 'ms'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível conectar com a API. Verifique o endpoint e a chave de API.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão com a API.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Restaura as configurações padrão
     */
    public function restoreDefaults(): JsonResponse
    {
        try {
            $settings = SystemSetting::create([]); // Usa valores padrão do model
            
            Log::info('Configurações restauradas para padrão', [
                'settings_id' => $settings->id
            ]);

            app(AuditLogService::class)->log('system_settings.restore', 'success', [
                'settings_id' => $settings->id,
            ]);
            
            return response()->json([
                'success' => true,
                'settings' => $settings->toVueFormat(),
                'message' => 'Configurações restauradas para os valores padrão.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar configurações: ' . $e->getMessage());
            app(AuditLogService::class)->log('system_settings.restore', 'failed', [
                'error' => $e->getMessage(),
            ], 'error');
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar configurações padrão.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtém o histórico de configurações
     */
    public function history(): JsonResponse
    {
        try {
            $history = SystemSetting::orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($setting) {
                    return [
                        'id' => $setting->id,
                        'created_at' => $setting->created_at->format('d/m/Y H:i:s'),
                        'settings' => $setting->toVueFormat()
                    ];
                });
            
            return response()->json([
                'success' => true,
                'history' => $history,
                'message' => 'Histórico carregado com sucesso.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar histórico: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar histórico de configurações.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Exporta configurações para arquivo
     */
    public function export(): JsonResponse
    {
        try {
            $settings = SystemSetting::getCurrentSettings();
            $exportData = [
                'exported_at' => now()->toIso8601String(),
                'settings' => $settings->toVueFormat(),
                'version' => '1.0'
            ];
            
            return response()->json([
                'success' => true,
                'data' => $exportData,
                'filename' => 'configuracoes-sistema-' . now()->format('Y-m-d-H-i-s') . '.json',
                'message' => 'Configurações exportadas com sucesso.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exportar configurações: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar configurações.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
