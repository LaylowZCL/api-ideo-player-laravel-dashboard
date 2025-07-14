<template>
  <div>
    <!-- Cabeçalho -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
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

    <!-- Filtros -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Nível</label>
            <select class="form-select" v-model="filters.level">
              <option value="">Todos os níveis</option>
              <option value="error">Erro</option>
              <option value="warning">Aviso</option>
              <option value="info">Informação</option>
              <option value="debug">Debug</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" v-model="filters.status">
              <option value="">Todos os status</option>
              <option value="success">Sucesso</option>
              <option value="error">Erro</option>
              <option value="warning">Aviso</option>
              <option value="info">Informação</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input type="text" class="form-control" placeholder="Buscar por evento ou vídeo" v-model="filters.search" />
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-outline-secondary w-100" @click="resetFilters">
              <i class="bi bi-x-circle me-1"></i>
              Limpar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div class="row g-3 mb-4">
      <StatsCard icon="bi-check-circle-fill" color="success" :count="countByStatus('success')" label="Sucessos" />
      <StatsCard icon="bi-exclamation-triangle-fill" color="warning" :count="countByStatus('warning')" label="Avisos" />
      <StatsCard icon="bi-x-circle-fill" color="danger" :count="countByStatus('error')" label="Erros" />
      <StatsCard icon="bi-info-circle-fill" color="info" :count="countByStatus('info')" label="Informações" />
    </div>

    <!-- Tabela de Logs -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Histórico de Eventos</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-dark table-hover mb-0">
            <thead class="border-bottom">
              <tr>
                <th class="ps-3">Horário</th>
                <th>Status</th>
                <th>Evento</th>
                <th>Detalhes</th>
                <th>Nível</th>
                <th class="pe-3">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(log, index) in filteredLogs" :key="index">
                <td class="ps-3">
                  <span class="font-monospace small">{{ log.time }}</span>
                </td>
                <td>
                  <span :class="'badge ' + getStatusClass(log.status)">
                    <i :class="getStatusIcon(log.status) + ' me-1'"></i>
                    {{ getStatusLabel(log.status) }}
                  </span>
                </td>
                <td>{{ log.event }}</td>
                <td class="text-muted">{{ log.video }}</td>
                <td>
                  <span :class="'badge ' + getLevelClass(log.level)">
                    {{ log.level.toUpperCase() }}
                  </span>
                </td>
                <td class="pe-3">
                  <button class="btn btn-outline-secondary btn-sm" @click="viewLogDetails(log)">
                    <i class="bi bi-eye"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredLogs.length === 0">
                <td colspan="6" class="text-center text-muted py-3">Nenhum log encontrado.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Contador -->
    <div class="d-flex justify-content-between align-items-center mt-3">
      <small class="text-muted">
        Mostrando {{ filteredLogs.length }} de {{ logs.length }} registros
      </small>
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><span class="page-link">Anterior</span></li>
          <li class="page-item active"><span class="page-link">1</span></li>
          <li class="page-item disabled"><span class="page-link">Próximo</span></li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LogsPage',
  components: {
    StatsCard: {
      props: ['icon', 'color', 'count', 'label'],
      template: `
        <div class="col-md-3">
          <div class="card">
            <div class="card-body text-center">
              <i :class="'bi ' + icon + ' text-' + color + ' fs-3 mb-2'"></i>
              <h4 class="mb-1">{{ count }}</h4>
              <small class="text-muted">{{ label }}</small>
            </div>
          </div>
        </div>
      `
    }
  },
  data() {
    return {
      logs: window.dashboardData?.logs ?? [],
      filters: {
        level: '',
        status: '',
        search: ''
      }
    };
  },
  computed: {
    filteredLogs() {
      return this.logs.filter(log => {
        const levelMatch = !this.filters.level || log.level === this.filters.level;
        const statusMatch = !this.filters.status || log.status === this.filters.status;
        const searchMatch = !this.filters.search ||
          log.event.toLowerCase().includes(this.filters.search.toLowerCase()) ||
          log.video.toLowerCase().includes(this.filters.search.toLowerCase());
        return levelMatch && statusMatch && searchMatch;
      });
    }
  },
  methods: {
    countByStatus(status) {
      return this.logs.filter(log => log.status === status).length;
    },
    getStatusClass(status) {
      return {
        success: 'bg-success',
        error: 'bg-danger',
        warning: 'bg-warning',
        info: 'bg-info'
      }[status] || 'bg-secondary';
    },
    getStatusIcon(status) {
      return {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
      }[status] || 'bi-circle-fill';
    },
    getStatusLabel(status) {
      return {
        success: 'Sucesso',
        error: 'Erro',
        warning: 'Aviso',
        info: 'Informação'
      }[status] || 'Desconhecido';
    },
    getLevelClass(level) {
      return {
        error: 'bg-danger bg-opacity-25 text-danger',
        warning: 'bg-warning bg-opacity-25 text-warning',
        info: 'bg-info bg-opacity-25 text-info',
        debug: 'bg-secondary bg-opacity-25 text-secondary'
      }[level] || 'bg-secondary';
    },
    resetFilters() {
      this.filters = { level: '', status: '', search: '' };
    },
    refreshLogs() {
      alert('Logs atualizados!');
    },
    exportLogs() {
      const csv = 'Horário,Status,Evento,Detalhes,Nível\n' +
        this.logs.map(l =>
          [l.time, l.status, l.event, l.video, l.level].join(',')
        ).join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.setAttribute('download', `logs_${new Date().toISOString().slice(0, 10)}.csv`);
      link.click();
    },
    clearLogs() {
      if (confirm('Deseja realmente limpar todos os logs?')) {
        this.logs = [];
      }
    },
    viewLogDetails(log) {
      alert(`Detalhes do Log:\n${log.event} - ${log.video} (${log.time})`);
    }
  }
}
</script>
