<template>
    <!-- Cabeçalho -->
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="h2 mb-1">Configurações</h1>
          <p class="text-muted mb-0">Personalize o comportamento do VideoScheduler</p>
        </div>
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-outline-secondary" @click="resetSettings">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Restaurar Padrão
          </button>
          <button class="btn btn-primary" @click="saveSettings">
            <i class="bi bi-check me-1"></i>
            Salvar
          </button>
        </div>
      </div>
    </div>

    <!-- Configurações -->
    <div class="row g-4">
      <!-- Configuração da API -->
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-globe me-2"></i>
              Configuração da API
            </h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="api-endpoint" class="form-label">Endpoint da API</label>
                <input type="url" class="form-control" id="api-endpoint" v-model="settings.apiEndpoint" :placeholder="defaultApiEndpoint">
              </div>
              <div class="col-md-6">
                <label for="api-key" class="form-label">Chave da API</label>
                <input type="password" class="form-control" id="api-key" v-model="settings.apiKey" placeholder="Sua chave de API">
              </div>
              <div class="col-md-6">
                <label for="sync-interval" class="form-label">Intervalo de Sincronização (minutos)</label>
                <input type="number" class="form-control" id="sync-interval" v-model="settings.syncInterval" min="5" max="1440">
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" @click="testConnection">
                  <i class="bi bi-wifi me-1"></i>
                  Testar Conexão
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Configurações de Exibição --
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-display me-2"></i>
              Configurações de Exibição
            </h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label for="default-monitor" class="form-label">Monitor Padrão</label>
              <select class="form-select" id="default-monitor" v-model="settings.defaultMonitor">
                <option value="principal">Monitor Principal</option>
                <option value="secundario">Monitor Secundário</option>
                <option value="todos">Todos os Monitores</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="auto-close-delay" class="form-label">Auto-fechamento (segundos, 0 = manual)</label>
              <input type="number" class="form-control" id="auto-close-delay" v-model="settings.autoCloseDelay" min="0" max="300">
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <label class="form-label mb-1">Sempre no topo</label>
                <p class="small text-muted mb-0">Manter popup sempre visível</p>
              </div>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="always-on-top" v-model="settings.alwaysOnTop">
              </div>
            </div>
          </div>
        </div>
      </div>

      -- Configurações do Sistema --
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-gear me-2"></i>
              Configurações do Sistema
            </h5>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
              <div>
                <label class="form-label mb-1">Iniciar com Windows</label>
                <p class="small text-muted mb-0">Executar automaticamente no boot</p>
              </div>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="start-with-windows" v-model="settings.startWithWindows">
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
              <div>
                <label class="form-label mb-1">Mostrar na bandeja do sistema</label>
                <p class="small text-muted mb-0">Ícone na área de notificação</p>
              </div>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="show-in-system-tray" v-model="settings.showInSystemTray">
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
              <div>
                <label class="form-label mb-1">Habilitar notificações</label>
                <p class="small text-muted mb-0">Avisos do sistema</p>
              </div>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="enable-notifications" v-model="settings.enableNotifications">
              </div>
            </div>
          </div>
        </div>
      </div>

      -- Armazenamento e Cache --
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-hdd me-2"></i>
              Armazenamento e Cache
            </h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-8">
                <label for="cache-location" class="form-label">Localização do Cache</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="cache-location" v-model="settings.cacheLocation" placeholder="C:\\VideoScheduler\\Cache">
                  <button class="btn btn-outline-secondary" type="button">
                    <i class="bi bi-folder"></i>
                    Procurar
                  </button>
                </div>
              </div>
              <div class="col-md-4">
                <label for="max-cache-size" class="form-label">Tamanho máximo do cache (GB)</label>
                <input type="number" class="form-control" id="max-cache-size" v-model="settings.maxCacheSize" min="1" max="100">
              </div>
              <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <label class="form-label mb-1">Limpeza automática</label>
                    <p class="small text-muted mb-0">Remover vídeos antigos automaticamente</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="auto-cleanup" v-model="settings.autoCleanup">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      -- Performance --
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-speedometer2 me-2"></i>
              Performance
            </h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="max-memory-usage" class="form-label">Uso máximo de memória (MB)</label>
                <input type="number" class="form-control" id="max-memory-usage" v-model="settings.maxMemoryUsage" min="50" max="1000">
              </div>
              <div class="col-md-6">
                <label for="log-level" class="form-label">Nível de log</label>
                <select class="form-select" id="log-level" v-model="settings.logLevel">
                  <option value="error">Apenas erros</option>
                  <option value="warning">Avisos e erros</option>
                  <option value="info">Informações</option>
                  <option value="debug">Debug (verbose)</option>
                </select>
              </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
              <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <label class="form-label mb-1">Aceleração por hardware</label>
                    <p class="small text-muted mb-0">Usar GPU para decodificação</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="enable-hardware-acceleration" v-model="settings.enableHardwareAcceleration">
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <label class="form-label mb-1">Pré-carregar vídeos</label>
                    <p class="small text-muted mb-0">Carregar na memória antes da execução</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="preload-videos" v-model="settings.preloadVideos">
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <label class="form-label mb-1">Atualizações automáticas</label>
                    <p class="small text-muted mb-0">Baixar e instalar automaticamente</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="enable-auto-update" v-model="settings.enableAutoUpdate">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    -->
    </div>
 

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
          <div class="modal-header border-secondary">
            <h5 class="modal-title">Confirmação</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>{{ confirmMessage }}</p>
          </div>
          <div class="modal-footer border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" @click="confirmAction">Confirmar</button>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
  data() {
    return {
      defaultApiEndpoint: (window.APP_CONFIG && window.APP_CONFIG.apiEndpoint) ? window.APP_CONFIG.apiEndpoint : "http://127.0.0.1:8000/api/videos",
      settings: {
        // Configurações de API
        apiEndpoint: (window.APP_CONFIG && window.APP_CONFIG.apiEndpoint) ? window.APP_CONFIG.apiEndpoint : "",
        apiKey: "",
        syncInterval: "30",
        
        // Configurações de Exibição
        defaultMonitor: "principal",
        alwaysOnTop: true,
        autoCloseDelay: "0",
        
        // Configurações do Sistema
        startWithWindows: true,
        showInSystemTray: true,
        enableNotifications: true,
        
        // Armazenamento e Cache
        cacheLocation: "C:\\VideoScheduler\\Cache",
        maxCacheSize: "5",
        autoCleanup: true,
        
        // Performance
        logLevel: "info",
        maxMemoryUsage: "200",
        enableHardwareAcceleration: true,
        preloadVideos: true,
        enableAutoUpdate: true
      },
      confirmMessage: '',
      confirmCallback: null,
      confirmModal: null,
      isLoading: false,
      isTestingConnection: false
    };
  },
  mounted() {
    // Inicializa o modal
    this.confirmModal = new Modal(document.getElementById('confirmModal'));
    
    // Carrega as configurações salvas
    this.loadSettings();
  },
  methods: {
    async loadSettings() {
      this.isLoading = true;
      try {
        const response = await axios.get('/api/system-settings');
        if (response.data.success) {
          this.settings = response.data.settings;
          this.showToast('Sucesso', 'Configurações carregadas com sucesso', 'success');
        }
      } catch (error) {
        console.error('Erro ao carregar configurações:', error);
        this.showToast('Erro', 'Não foi possível carregar as configurações', 'error');
      } finally {
        this.isLoading = false;
      }
    },
    async saveSettings() {
      // Validação dos campos obrigatórios
      if (!this.settings.apiEndpoint) {
        this.showToast('Erro', 'O endpoint da API é obrigatório', 'error');
        return;
      }

      if (this.settings.syncInterval < 5 || this.settings.syncInterval > 1440) {
        this.showToast('Erro', 'O intervalo de sincronização deve estar entre 5 e 1440 minutos', 'error');
        return;
      }

      if (this.settings.maxCacheSize < 1 || this.settings.maxCacheSize > 100) {
        this.showToast('Erro', 'O tamanho máximo do cache deve estar entre 1 e 100 GB', 'error');
        return;
      }

      if (this.settings.maxMemoryUsage < 50 || this.settings.maxMemoryUsage > 1000) {
        this.showToast('Erro', 'O uso máximo de memória deve estar entre 50 e 1000 MB', 'error');
        return;
      }

      this.isLoading = true;
      try {
        const response = await axios.post('/api/system-settings', this.settings);
        
        if (response.data.success) {
          this.showToast('Configurações Salvas', 'As configurações foram aplicadas com sucesso', 'success');
          // Atualiza as configurações locais com a resposta do servidor
          this.settings = response.data.settings;
        } else {
          this.showToast('Erro', response.data.message || 'Falha ao salvar configurações', 'error');
        }
      } catch (error) {
        console.error('Erro ao salvar configurações:', error);
        
        if (error.response?.data?.errors) {
          const errors = error.response.data.errors;
          const errorMessage = Object.values(errors).flat().join(', ');
          this.showToast('Erro de Validação', errorMessage, 'error');
        } else {
          this.showToast('Erro', 'Falha ao salvar configurações. Tente novamente.', 'error');
        }
      } finally {
        this.isLoading = false;
      }
    },
    async resetSettings() {
      this.showConfirmModal(
        'Tem certeza que deseja restaurar as configurações padrão? Todas as configurações personalizadas serão perdidas.',
        async () => {
          this.isLoading = true;
          try {
            const response = await axios.post('/api/system-settings/restore-defaults');
            
            if (response.data.success) {
              this.settings = response.data.settings;
              this.showToast('Configurações Restauradas', 'As configurações padrão foram restauradas', 'success');
            } else {
              this.showToast('Erro', response.data.message || 'Falha ao restaurar configurações', 'error');
            }
          } catch (error) {
            console.error('Erro ao restaurar configurações:', error);
            this.showToast('Erro', 'Falha ao restaurar configurações. Tente novamente.', 'error');
          } finally {
            this.isLoading = false;
          }
        }
      );
    },
    async testConnection() {
      if (!this.settings.apiEndpoint) {
        this.showToast('Erro', 'Informe o endpoint da API primeiro', 'error');
        return;
      }

      this.isTestingConnection = true;
      try {
        const response = await axios.post('/api/system-settings/test-connection', {
          apiEndpoint: this.settings.apiEndpoint,
          apiKey: this.settings.apiKey
        });
        
        if (response.data.success) {
          this.showToast('Conexão Bem-sucedida', response.data.message, 'success');
        } else {
          this.showToast('Erro de Conexão', response.data.message, 'error');
        }
      } catch (error) {
        console.error('Erro ao testar conexão:', error);
        
        if (error.response?.data?.message) {
          this.showToast('Erro de Conexão', error.response.data.message, 'error');
        } else {
          this.showToast('Erro', 'Falha ao testar conexão. Tente novamente.', 'error');
        }
      } finally {
        this.isTestingConnection = false;
      }
    },
    showConfirmModal(message, callback) {
      this.confirmMessage = message;
      this.confirmCallback = callback;
      this.confirmModal.show();
    },
    confirmAction() {
      if (this.confirmCallback) {
        this.confirmCallback();
      }
      this.confirmModal.hide();
    },
    showToast(title, message, type) {
      // Implementação com Bootstrap Toast
      const toastEl = document.createElement('div');
      toastEl.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
      toastEl.setAttribute('role', 'alert');
      toastEl.setAttribute('aria-live', 'assertive');
      toastEl.setAttribute('aria-atomic', 'true');
      
      toastEl.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">
            <strong>${title}</strong><br>${message}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      `;
      
      const toastContainer = document.getElementById('toastContainer') || this.createToastContainer();
      toastContainer.appendChild(toastEl);
      
      const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
      toast.show();
      
      // Remove o toast após ser escondido
      toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
      });
    },
    createToastContainer() {
      const container = document.createElement('div');
      container.id = 'toastContainer';
      container.className = 'toast-container position-fixed top-0 end-0 p-3';
      document.body.appendChild(container);
      return container;
    },
    // Método para limpar cache manualmente
    async clearCache() {
      this.showConfirmModal(
        'Tem certeza que deseja limpar todo o cache? Todos os vídeos baixados serão removidos.',
        async () => {
          this.isLoading = true;
          try {
            // Aqui você implementaria a lógica para limpar o cache
            // Exemplo: await axios.post('/api/system-settings/clear-cache');
            
            // Simulação
            setTimeout(() => {
              this.showToast('Cache Limpo', 'Todo o cache foi removido com sucesso', 'success');
              this.isLoading = false;
            }, 1500);
          } catch (error) {
            console.error('Erro ao limpar cache:', error);
            this.showToast('Erro', 'Falha ao limpar cache. Tente novamente.', 'error');
            this.isLoading = false;
          }
        }
      );
    }
  }
};
</script>
