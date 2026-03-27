<template>
  <div>
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h1 class="page-title mb-1">Logs & Auditoria</h1>
        <p class="page-subtitle mb-0">Histórico de ações do dashboard com filtros avançados</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-light" @click="exportLogs">
          <i class="bi bi-download me-1"></i>Exportar
        </button>
        <button class="btn btn-outline-danger" @click="clearLogs">
          <i class="bi bi-trash me-1"></i>Limpar
        </button>
      </div>
    </div>

    <div class="card toolbar-card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-center">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-transparent border-0 text-muted">
                <i class="bi bi-search"></i>
              </span>
              <input type="text" class="form-control" placeholder="Pesquisar por evento ou actor" v-model="filters.search" @input="fetchLogs">
            </div>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="filters.level" @change="fetchLogs">
              <option value="">Nível</option>
              <option value="info">Informação</option>
              <option value="warning">Aviso</option>
              <option value="error">Erro</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="filters.status" @change="fetchLogs">
              <option value="">Status</option>
              <option value="success">Sucesso</option>
              <option value="failed">Falhou</option>
            </select>
          </div>
          <div class="col-md-2">
            <button class="btn btn-outline-light w-100" @click="resetFilters">
              Limpar filtros
            </button>
          </div>
          <div class="col-md-2 text-end">
            <span class="chip chip-muted">{{ total }} registos</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
      <p class="text-muted mt-3">A carregar registos...</p>
    </div>

    <div v-else class="table-responsive">
      <table class="table table-dark table-hover align-middle">
        <thead>
          <tr>
            <th>Horário</th>
            <th>Evento</th>
            <th>Ator</th>
            <th>Status</th>
            <th>Nível</th>
            <th class="text-end">Detalhes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in logs" :key="log.id">
            <td class="font-monospace small">{{ log.time }}</td>
            <td class="fw-semibold">{{ log.event }}</td>
            <td class="text-muted">{{ parseActor(log).actor_email || 'N/A' }}</td>
            <td>
              <span class="chip" :class="log.status === 'success' ? 'chip-manager' : 'chip-admin'">
                {{ log.status }}
              </span>
            </td>
            <td>
              <span class="chip chip-muted">{{ log.level }}</span>
            </td>
            <td class="text-end">
              <button class="btn btn-outline-light btn-sm" @click="showDetails(log)">
                <i class="bi bi-eye"></i>
              </button>
            </td>
          </tr>
          <tr v-if="logs.length === 0">
            <td colspan="6" class="text-center text-muted py-4">Nenhum log encontrado.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <small class="text-muted">Página {{ page }} de {{ lastPage }}</small>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-light btn-sm" :disabled="page <= 1" @click="changePage(page - 1)">Anterior</button>
        <button class="btn btn-outline-light btn-sm" :disabled="page >= lastPage" @click="changePage(page + 1)">Próximo</button>
      </div>
    </div>

    <div class="modal fade" id="logModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalhes do Log</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <pre class="log-detail">{{ selectedLogDetails }}</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'AdminLogsPage',
  data() {
    return {
      logs: [],
      total: 0,
      page: 1,
      lastPage: 1,
      loading: false,
      modal: null,
      selectedLogDetails: '',
      filters: {
        search: '',
        level: '',
        status: ''
      }
    };
  },
  mounted() {
    this.modal = new Modal(document.getElementById('logModal'));
    this.fetchLogs();
  },
  methods: {
    async fetchLogs() {
      this.loading = true;
      try {
        const response = await axios.get('/api/logs', {
          params: {
            page: this.page,
            search: this.filters.search,
            level: this.filters.level,
            status: this.filters.status
          }
        });
        this.logs = response.data.logs || [];
        this.total = response.data.total || 0;
        this.page = response.data.page || 1;
        this.lastPage = response.data.last_page || 1;
      } finally {
        this.loading = false;
      }
    },
    changePage(nextPage) {
      this.page = nextPage;
      this.fetchLogs();
    },
    resetFilters() {
      this.filters = { search: '', level: '', status: '' };
      this.page = 1;
      this.fetchLogs();
    },
    parseActor(log) {
      try {
        return JSON.parse(log.video || '{}');
      } catch {
        return {};
      }
    },
    showDetails(log) {
      const details = this.parseActor(log);
      this.selectedLogDetails = JSON.stringify({
        ...details,
        event: log.event,
        status: log.status,
        level: log.level,
        time: log.time
      }, null, 2);
      this.modal.show();
    },
    exportLogs() {
      window.location.href = '/api/logs/export';
    },
    async clearLogs() {
      if (!confirm('Pretende mesmo limpar todos os registos?')) return;
      await axios.delete('/api/logs');
      this.fetchLogs();
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Fraunces:wght@600&display=swap');

.page-title {
  font-family: "Inter";
  font-size: 2.1rem;
  color: #f2f7ff;
}

.page-subtitle {
  font-family: "Inter";
  color: rgba(210, 225, 255, 0.7);
}

.toolbar-card {
  background: linear-gradient(135deg, rgba(8, 35, 72, 0.9), rgba(11, 51, 100, 0.9));
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
}

.toolbar-card .form-control,
.toolbar-card .form-select {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #f5f7ff;
}

.chip {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #fff;
}

.chip-muted {
  background: rgba(255, 255, 255, 0.12);
}

.chip-admin {
  background: linear-gradient(135deg, #1c4fa3, #2b76d4);
}

.chip-manager {
  background: linear-gradient(135deg, #1f7aa1, #2db9c9);
}

.log-detail {
  background: rgba(0, 0, 0, 0.4);
  color: #f2f7ff;
  padding: 1rem;
  border-radius: 12px;
  font-size: 0.85rem;
}
</style>
