<template>
    <div>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">Configurações</h1>
                    <p class="text-muted mb-0">Gerencie as configurações do VideoScheduler</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary" @click="resetSettings">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Restaurar Padrões
                    </button>
                    <button class="btn btn-primary" @click="testConnection">
                        <i class="bi bi-plug me-1"></i>
                        Testar Conexão
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="card">
            <div class="card-body">
                <form @submit.prevent="saveSettings">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="api_endpoint" class="form-label">Endpoint da API</label>
                            <input type="url" class="form-control" v-model="settings.api_endpoint" required>
                        </div>
                        <div class="col-md-6">
                            <label for="api_key" class="form-label">Chave da API</label>
                            <input type="text" class="form-control" v-model="settings.api_key">
                        </div>
                        <div class="col-md-4">
                            <label for="sync_interval" class="form-label">Intervalo de Sincronização (minutos)</label>
                            <input type="number" class="form-control" v-model="settings.sync_interval" min="5" max="1440" required>
                        </div>
                        <div class="col-md-4">
                            <label for="default_monitor" class="form-label">Monitor Padrão</label>
                            <select class="form-select" v-model="settings.default_monitor" required>
                                <option value="principal">Principal</option>
                                <option value="secundario">Secundário</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="max_cache_size" class="form-label">Tamanho Máximo do Cache (GB)</label>
                            <input type="number" class="form-control" v-model="settings.max_cache_size" min="1" max="100" required>
                        </div>
                        <div class="col-md-4">
                            <label for="log_level" class="form-label">Nível de Log</label>
                            <select class="form-select" v-model="settings.log_level" required>
                                <option value="error">Erro</option>
                                <option value="warning">Aviso</option>
                                <option value="info">Informação</option>
                                <option value="debug">Debug</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="max_log_files" class="form-label">Máximo de Arquivos de Log</label>
                            <input type="number" class="form-control" v-model="settings.max_log_files" min="1" max="100" required>
                        </div>
                        <div class="col-md-4">
                            <label for="max_memory_usage" class="form-label">Uso Máximo de Memória (MB)</label>
                            <input type="number" class="form-control" v-model="settings.max_memory_usage" min="50" max="1000" required>
                        </div>
                        <div class="col-12">
                            <label for="cache_location" class="form-label">Local do Cache</label>
                            <input type="text" class="form-control" v-model="settings.cache_location" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="settings.always_on_top">
                                <label class="form-check-label">Sempre no Topo</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="settings.start_with_windows">
                                <label class="form-check-label">Iniciar com o Windows</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="settings.show_in_system_tray">
                                <label class="form-check-label">Mostrar na Bandeja do Sistema</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="settings.enable_notifications">
                                <label class="form-check-label">Ativar Notificações</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="settings.auto_cleanup">
                                <label class="form-check-label">Limpeza Automática do Cache</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="settings.enable_auto_update">
                                <label class="form-check-label">Ativar Atualizações Automáticas</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="settings.enable_hardware_acceleration">
                                <label class="form-check-label">Ativar Aceleração de Hardware</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="settings.preload_videos">
                                <label class="form-check-label">Pré-carregar Vídeos</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            settings: {
                api_endpoint: '',
                api_key: '',
                sync_interval: 30,
                default_monitor: 'principal',
                always_on_top: true,
                auto_close_delay: 0,
                start_with_windows: true,
                show_in_system_tray: true,
                enable_notifications: true,
                cache_location: '',
                max_cache_size: 5,
                auto_cleanup: true,
                log_level: 'info',
                max_log_files: 10,
                enable_auto_update: true,
                max_memory_usage: 200,
                enable_hardware_acceleration: true,
                preload_videos: true
            }
        };
    },
    mounted() {
        this.fetchSettings();
    },
    methods: {
        fetchSettings() {
            axios.get('/api/settings')
                .then(response => {
                    this.settings = response.data;
                })
                .catch(error => {
                    this.showToast('Erro', 'Falha ao carregar configurações', 'error');
                });
        },
        saveSettings() {
            axios.post('/api/settings', this.settings)
                .then(response => {
                    this.showToast('Sucesso', 'Configurações salvas com sucesso', 'success');
                })
                .catch(error => {
                    this.showToast('Erro', error.response.data.errors || 'Falha ao salvar configurações', 'error');
                });
        },
        resetSettings() {
            if (confirm('Tem certeza que deseja restaurar as configurações padrão?')) {
                axios.post('/api/settings/reset')
                    .then(response => {
                        this.fetchSettings();
                        this.showToast('Sucesso', 'Configurações restauradas com sucesso', 'success');
                    })
                    .catch(error => {
                        this.showToast('Erro', 'Falha ao restaurar configurações', 'error');
                    });
            }
        },
        testConnection() {
            axios.post('/api/settings/test-connection', { api_endpoint: this.settings.api_endpoint })
                .then(response => {
                    this.showToast('Sucesso', response.data.message, 'success');
                })
                .catch(error => {
                    this.showToast('Erro', error.response.data.message || 'Falha ao testar conexão', 'error');
                });
        },
        showToast(title, message, type) {
            alert(`${title}: ${message} (${type})`);
        }
    }
}
</script>
