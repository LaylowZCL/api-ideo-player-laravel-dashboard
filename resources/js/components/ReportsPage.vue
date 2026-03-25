<template>
  <div class="reports-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h1 class="h2 mb-1">Relatórios</h1>
        <p class="text-muted mb-0">Relatório exaustivo de reprodução, eventos e desempenho.</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary" @click="refreshAll" :disabled="loading">
          <i class="bi bi-arrow-clockwise me-1"></i>
          Atualizar
        </button>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Filtros avançados</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label small text-muted">Data inicial</label>
            <input type="date" class="form-control" v-model="filters.startDate">
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted">Data final</label>
            <input type="date" class="form-control" v-model="filters.endDate">
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Vídeo ID</label>
            <input type="text" class="form-control" v-model="filters.videoId" placeholder="Opcional">
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Plataforma</label>
            <select class="form-select" v-model="filters.platform">
              <option value="">Todas</option>
              <option value="windows">Windows</option>
              <option value="mac">Mac</option>
              <option value="linux">Linux</option>
              <option value="unknown">Desconhecida</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Evento</label>
            <select class="form-select" v-model="filters.eventType">
              <option value="">Todos</option>
              <option value="playback_started">Início</option>
              <option value="video_completed">Concluído</option>
              <option value="popup_opened">Popup aberto</option>
              <option value="popup_minimized">Popup minimizado</option>
              <option value="autoplay_blocked">Autoplay bloqueado</option>
              <option value="user_closed">Fechado pelo usuário</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Conclusão</label>
            <select class="form-select" v-model="filters.completed">
              <option value="">Todos</option>
              <option value="1">Concluído</option>
              <option value="0">Parcial</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Agrupar por</label>
            <select class="form-select" v-model="filters.groupBy">
              <option value="day">Dia</option>
              <option value="week">Semana</option>
              <option value="month">Mês</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Itens por página</label>
            <select class="form-select" v-model.number="filters.perPage">
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" @click="applyFilters" :disabled="loading">
              Aplicar filtros
            </button>
            <button class="btn btn-outline-secondary" @click="resetFilters" :disabled="loading">
              Limpar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3" v-for="stat in kpis" :key="stat.label">
        <div class="card h-100">
          <div class="card-body">
            <div class="small text-muted mb-1">{{ stat.label }}</div>
            <div class="h4 mb-0">{{ stat.value }}</div>
            <div class="small text-muted">{{ stat.caption }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Evolução por período</h5>
          </div>
          <div class="card-body">
            <div v-if="timeline.length" class="d-flex align-items-end justify-content-between timeline">
              <div v-for="item in timeline" :key="item.period" class="text-center timeline-item">
                <div class="small text-muted mb-1">{{ item.period }}</div>
                <div class="timeline-bar" :style="{ height: timelineHeight(item.count) + '%' }"></div>
                <div class="small fw-medium mt-1">{{ item.count }}</div>
              </div>
            </div>
            <div v-else class="text-center text-muted py-4">
              Nenhum dado disponível para o período selecionado.
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Distribuição por plataforma</h5>
          </div>
          <div class="card-body">
            <div v-for="platform in platformDistribution" :key="platform.label" class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small">{{ platform.label }}</span>
                <span class="badge bg-primary">{{ platform.count }}</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar" :style="{ width: platform.percentage + '%' }"></div>
              </div>
            </div>
            <div v-if="platformDistribution.length === 0" class="text-center text-muted py-4">
              Nenhuma plataforma registrada.
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Eventos mais frequentes</h5>
          </div>
          <div class="card-body">
            <div v-for="event in eventBreakdown" :key="event.label" class="d-flex justify-content-between align-items-center mb-2">
              <span class="small">{{ event.label }}</span>
              <span class="badge bg-secondary">{{ event.count }}</span>
            </div>
            <div v-if="eventBreakdown.length === 0" class="text-center text-muted py-4">
              Nenhum evento registrado.
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Top vídeos</h5>
          </div>
          <div class="card-body">
            <div v-for="video in topVideos" :key="video.video_id" class="d-flex justify-content-between align-items-center mb-2">
              <span class="small text-truncate" :title="video.video_title">{{ video.video_title }}</span>
              <span class="badge bg-primary">{{ video.count }}</span>
            </div>
            <div v-if="topVideos.length === 0" class="text-center text-muted py-4">
              Nenhum vídeo registrado.
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Relatórios detalhados</h5>
        <span class="badge bg-info">{{ pagination.total }} registros</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Vídeo</th>
                <th>Evento</th>
                <th>Plataforma</th>
                <th>Data/Hora</th>
                <th>Duração</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="report in reports" :key="report.id">
                <td class="fw-medium">{{ report.video_title }}</td>
                <td>{{ formatEvent(report.event_type) }}</td>
                <td><span class="badge bg-secondary">{{ formatPlatform(report.platform) }}</span></td>
                <td>{{ formatDate(report.viewed_at) }}</td>
                <td>{{ report.duration ? report.duration + 's' : '-' }}</td>
                <td>
                  <span class="badge" :class="report.completed ? 'bg-success' : 'bg-warning'">
                    {{ report.completed ? 'Concluído' : 'Parcial' }}
                  </span>
                </td>
              </tr>
              <tr v-if="reports.length === 0">
                <td colspan="6" class="text-center text-muted py-4">Nenhum relatório encontrado.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
          </small>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1">
              Anterior
            </button>
            <button class="btn btn-outline-secondary" @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page">
              Próxima
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReportsPage',
  data() {
    return {
      loading: false,
      filters: {
        startDate: '',
        endDate: '',
        videoId: '',
        platform: '',
        eventType: '',
        completed: '',
        groupBy: 'day',
        perPage: 25
      },
      stats: {},
      reports: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0
      }
    };
  },
  computed: {
    kpis() {
      return [
        { label: 'Total de eventos', value: this.stats.total_reports ?? 0, caption: 'Tudo no período' },
        { label: 'Inícios', value: this.stats.total_starts ?? 0, caption: 'playback_started' },
        { label: 'Conclusões', value: this.stats.total_completions ?? 0, caption: 'video_completed' },
        { label: 'Taxa de conclusão', value: (this.stats.completion_rate ?? 0) + '%', caption: 'Média geral' }
      ];
    },
    timeline() {
      return this.stats.timeline || [];
    },
    platformDistribution() {
      const raw = this.stats.by_platform || {};
      const total = Object.values(raw).reduce((sum, value) => sum + value, 0) || 1;
      return Object.entries(raw).map(([key, count]) => ({
        label: this.formatPlatform(key),
        count,
        percentage: Math.round((count / total) * 100)
      }));
    },
    eventBreakdown() {
      const raw = this.stats.event_breakdown || {};
      return Object.entries(raw).map(([key, count]) => ({
        label: this.formatEvent(key),
        count
      }));
    },
    topVideos() {
      return this.stats.top_videos || [];
    }
  },
  mounted() {
    this.refreshAll();
  },
  methods: {
    async refreshAll() {
      this.loading = true;
      await Promise.all([this.fetchStats(), this.fetchReports()]);
      this.loading = false;
    },
    async applyFilters() {
      this.pagination.current_page = 1;
      await this.refreshAll();
    },
    async resetFilters() {
      this.filters = {
        startDate: '',
        endDate: '',
        videoId: '',
        platform: '',
        eventType: '',
        completed: '',
        groupBy: 'day',
        perPage: 25
      };
      await this.applyFilters();
    },
    async fetchStats() {
      const params = {
        video_id: this.filters.videoId || undefined,
        start_date: this.filters.startDate || undefined,
        end_date: this.filters.endDate || undefined,
        group_by: this.filters.groupBy || 'day'
      };
      const response = await this.$http.get('/api/videos/report/stats', { params });
      if (response.data && response.data.success) {
        this.stats = response.data.stats || {};
      }
    },
    async fetchReports() {
      const params = {
        video_id: this.filters.videoId || undefined,
        start_date: this.filters.startDate || undefined,
        end_date: this.filters.endDate || undefined,
        platform: this.filters.platform || undefined,
        event_type: this.filters.eventType || undefined,
        completed: this.filters.completed !== '' ? this.filters.completed : undefined,
        per_page: this.filters.perPage,
        page: this.pagination.current_page
      };
      const response = await this.$http.get('/api/reports', { params });
      if (response.data && response.data.success) {
        this.reports = response.data.reports.data || [];
        this.pagination = {
          current_page: response.data.reports.current_page,
          last_page: response.data.reports.last_page,
          total: response.data.reports.total
        };
      }
    },
    async goToPage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.pagination.current_page = page;
      await this.fetchReports();
    },
    timelineHeight(value) {
      const max = Math.max(...this.timeline.map(item => item.count), 1);
      return Math.round((value / max) * 100);
    },
    formatPlatform(value) {
      const map = {
        windows: 'Windows',
        mac: 'Mac',
        linux: 'Linux',
        unknown: 'Desconhecida'
      };
      return map[value] || value || 'Desconhecida';
    },
    formatEvent(value) {
      const map = {
        playback_started: 'Início',
        video_completed: 'Concluído',
        popup_opened: 'Popup aberto',
        popup_minimized: 'Popup minimizado',
        autoplay_blocked: 'Autoplay bloqueado',
        user_closed: 'Fechado pelo usuário'
      };
      return map[value] || value || 'Evento';
    },
    formatDate(value) {
      if (!value) return '-';
      const date = new Date(value);
      return date.toLocaleString('pt-PT');
    }
  }
};
</script>

<style scoped>
.timeline {
  min-height: 180px;
  gap: 0.5rem;
}

.timeline-item {
  flex: 1;
}

.timeline-bar {
  width: 100%;
  min-height: 12px;
  background: var(--primary-color);
  border-radius: 0.5rem 0.5rem 0 0;
}
</style>
