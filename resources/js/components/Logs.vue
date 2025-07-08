<template>
    <div>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">Logs do Sistema</h1>
                    <p class="text-muted mb-0">Histórico de atividades e eventos do VideoScheduler</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary" @click="exportLogs">
                        <i class="bi bi-download me-1"></i>
                        Exportar Logs
                    </button>
                    <button class="btn btn-outline-secondary" @click="clearLogs">
                        <i class="bi bi-trash me-1"></i>
                        Limpar Logs
                    </button>
                    <button class="btn btn-primary" @click="refreshLogs">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Atualizar
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="log-level-filter" class="form-label">Nível</label>
                        <select class="form-select" v-model="levelFilter" @change="filterLogs">
                            <option value="">Todos os níveis</option>
                            <option value="error">Erro</option>
                            <option value="warning">Aviso</option>
                            <option value="info">Informação</option>
                            <option value="debug">Debug</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="log-status-filter" class="form-label">Status</label>
                        <select class="form-select" v-model="statusFilter" @change="filterLogs">
                            <option value="">Todos os status</option>
                            <option value="success">Sucesso</option>
                            <option value="error">Erro</option>
                            <option value="warning">Aviso</option>
                            <option value="info">Informação</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="log-search" class="form-label">Buscar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" class="form-control ps-5" v-model="searchTerm" placeholder="Buscar por evento, vídeo..." @keyup="filterLogs">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button class="btn btn-outline-secondary" @click="resetFilters">
                                <i class="bi bi-x-circle me-1"></i>
                                Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3" v-for="stat in stats" :key="stat.title">
                <div class="card">
                    <div class="card-body text-center">
                        <i :class="stat.icon + ' fs-3 mb-2'"></i>
                        <h4 class="mb-1">{{ stat.count }}</h4>
                        <small class="text-muted">{{ stat.title }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs List -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Histórico de Eventos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="border-bottom">
                            <tr>
                                <th class="border-0 ps-3">Horário</th>
                                <th class="border-0">Status</th>
                                <th class="border-0">Evento</th>
                                <th class="border-0">Detalhes</th>
                                <th class="border-0">Nível</th>
                                <th class="border-0 pe-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(log, index) in filteredLogs" :key="log.id" class="log-row" :data-level="log.level" :data-status="log.status" :data-event="log.event.toLowerCase()" :data-video="log.video.toLowerCase()">
                                <td class="ps-3">
                                    <span class="font-monospace small">{{ log.time }}</span>
                                </td>
                                <td>
                                    <span class="badge" :class="getLogStatusBadgeClass(log.status)">
                                        <i :class="getLogStatusIcon(log.status) + ' me-1'"></i>
                                        {{ getLogStatusText(log.status) }}
                                    </span>
                                </td>
                                <td>{{ log.event }}</td>
                                <td class="text-muted">{{ log.video }}</td>
                                <td>
                                    <span class="badge" :class="getLogLevelBadgeClass(log.level)">
                                        {{ log.level.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="pe-3">
                                    <button class="btn btn-outline-secondary btn-sm" @click="viewLogDetails(index)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">Mostrando {{ filteredLogs.length }} de {{ logs.length }} registros</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <span class="page-link">Anterior</span>
                    </li>
                    <li class="page-item active">
                        <span class="page-link">1</span>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">Próximo</span>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            logs: [],
            levelFilter: '',
            statusFilter: '',
            searchTerm: '',
            stats: [
                { title: 'Sucessos', count: 0, icon: 'bi bi-check-circle-fill text-success' },
                { title: 'Avisos', count: 0, icon: 'bi bi-exclamation-triangle-fill text-warning' },
                { title: 'Erros', count: 0, icon: 'bi bi-x-circle-fill text-danger' },
                { title: 'Informações', count: 0, icon: 'bi bi-info-circle-fill text-info' },
            ]
        };
    },
    computed: {
        filteredLogs() {
            return this.logs.filter(log => {
                let showRow = true;
                if (this.levelFilter && log.level !== this.levelFilter) {
                    showRow = false;
                }
                if (this.statusFilter && log.status !== this.statusFilter) {
                    showRow = false;
                }
                if (this.searchTerm && !log.event.toLowerCase().includes(this.searchTerm.toLowerCase()) && !log.video.toLowerCase().includes(this.searchTerm.toLowerCase())) {
                    showRow = false;
                }
                return showRow;
            });
        }
    },
    mounted() {
        this.fetchLogs();
    },
    methods: {
        fetchLogs() {
            axios.get('/api/logs')
                .then(response => {
                    this.logs = response.data;
                    this.updateStats();
                })
                .catch(error => {
                    this.showToast('Erro', 'Falha ao carregar logs', 'error');
                });
        },
        updateStats() {
            this.stats[0].count = this.logs.filter(l => l.status === 'success').length;
            this.stats[1].count = this.logs.filter(l => l.status === 'warning').length;
            this.stats[2].count = this.logs.filter(l => l.status === 'error').length;
            this.stats[3].count = this.logs.filter(l => l.status === 'info').length;
        },
        getLogStatusBadgeClass(status) {
            switch (status) {
                case 'success': return 'bg-success';
                case 'error': return 'bg-danger';
                case 'warning': return 'bg-warning';
                case 'info': return 'bg-info';
                default: return 'bg-secondary';
            }
        },
        getLogStatusIcon(status) {
            switch (status) {
                case 'success': return 'bi-check-circle-fill';
                case 'error': return 'bi-x-circle-fill';
                case 'warning': return 'bi-exclamation-triangle-fill';
                case 'info': return 'bi-info-circle-fill';
                default: return 'bi-circle-fill';
            }
        },
        getLogStatusText(status) {
            switch (status) {
                case 'success': return 'Sucesso';
                case 'error': return 'Erro';
                case 'warning': return 'Aviso';
                case 'info': return 'Info';
                default: return 'Desconhecido';
            }
        },
        getLogLevelBadgeClass(level) {
            switch (level) {
                case 'error': return 'bg-danger bg-opacity-25 text-danger';
                case 'warning': return 'bg-warning bg-opacity-25 text-warning';
                case 'info': return 'bg-info bg-opacity-25 text-info';
                case 'debug': return 'bg-secondary bg-opacity-25 text-secondary';
                default: return 'bg-secondary';
            }
        },
        filterLogs() {
            this.updateStats();
        },
        resetFilters() {
            this.levelFilter = '';
            this.statusFilter = '';
            this.searchTerm = '';
            this.updateStats();
        },
        refreshLogs() {
            this.fetchLogs();
            this.showToast('Logs Atualizados', 'Lista de logs recarregada', 'success');
        },
        exportLogs() {
            window.location.href = '/logs/export';
        },
        clearLogs() {
            if (confirm('Tem certeza que deseja limpar todos os logs? Esta ação não pode ser desfeita.')) {
                axios.post('/logs/clear')
                    .then(() => {
                        this.fetchLogs();
                        this.showToast('Logs Limpos', 'Todos os logs foram removidos', 'success');
                    })
                    .catch(error => {
                        this.showToast('Erro', 'Falha ao limpar logs', 'error');
                    });
            }
        },
        viewLogDetails(index) {
            const log = this.logs[index];
            this.showToast('Detalhes do Log', `${log.event} - ${log.video} (${log.time})`, 'info');
        },
        showToast(title, message, type) {
            // Implementar toast com Bootstrap ou Vue
            alert(`${title}: ${message} (${type})`);
        }
    }
}
</script>