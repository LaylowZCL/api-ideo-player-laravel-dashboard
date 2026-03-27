<template>
  <div class="admin-targets">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h1 class="h2 mb-1">Alvos AD</h1>
        <p class="text-muted mb-0">Auditoria do JSON diário e direccionamento por máquina/utilizador.</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" @click="loadTargets" :disabled="loading">
          <i class="bi bi-arrow-clockwise me-1"></i>
          Atualizar
        </button>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Estado do AD</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="small text-muted">Status de conexão</div>
                <div class="h5 mb-0" :class="adHealth.connected ? 'text-success' : 'text-danger'">
                  {{ adHealth.connected ? 'Conectado' : 'Falha' }}
                </div>
              </div>
              <div class="col-md-6">
                <div class="small text-muted">Bind</div>
                <div class="h5 mb-0" :class="adHealth.bound ? 'text-success' : 'text-warning'">
                  {{ adHealth.bound ? 'OK' : 'Não autenticado' }}
                </div>
              </div>
              <div class="col-12 small text-muted">
                Host: {{ adHealth.host || '-' }} | Porta: {{ adHealth.port || '-' }} | SSL: {{ adHealth.use_ssl ? 'Sim' : 'Não' }}
              </div>
              <div class="col-12" v-if="adHealth.error">
                <div class="alert alert-warning mb-0">{{ adHealth.error }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">JSON Diário</h5>
          </div>
          <div class="card-body">
            <div class="small text-muted">Arquivo</div>
            <div class="mb-2">{{ jsonStatus.path || '-' }}</div>
            <div class="row g-2">
              <div class="col-6">
                <div class="small text-muted">Registos</div>
                <div class="h5 mb-0">{{ jsonStatus.entries ?? 0 }}</div>
              </div>
              <div class="col-6">
                <div class="small text-muted">Última importação</div>
                <div class="h6 mb-0">{{ jsonStatus.last_import_at || '-' }}</div>
              </div>
            </div>
            <div class="small text-muted mt-2">Última alteração: {{ jsonStatus.last_modified || '-' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Filtros</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label small text-muted">Máquina</label>
            <input type="text" class="form-control" v-model="filters.machine" placeholder="DISPLAY001">
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted">Utilizador</label>
            <input type="text" class="form-control" v-model="filters.user" placeholder="joao">
          </div>
          <div class="col-md-4">
            <label class="form-label small text-muted">Grupo</label>
            <input type="text" class="form-control" v-model="filters.group" placeholder="GRP_VIDEO">
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted">Itens</label>
            <select class="form-select" v-model.number="filters.perPage">
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" @click="applyFilters" :disabled="loading">Aplicar</button>
            <button class="btn btn-outline-secondary" @click="resetFilters" :disabled="loading">Limpar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Registos importados</h5>
        <span class="badge bg-info">{{ pagination.total }} itens</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Máquina</th>
                <th>Utilizador</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Grupo</th>
                <th>Efetivo em</th>
                <th>Origem</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="target in targets" :key="target.id">
                <td class="fw-medium">{{ target.machine_name }}</td>
                <td>{{ target.user_name || '-' }}</td>
                <td>{{ target.user_display_name || '-' }}</td>
                <td>{{ target.user_email || '-' }}</td>
                <td>{{ target.group || '-' }}</td>
                <td>{{ target.effective_at || '-' }}</td>
                <td><span class="badge bg-secondary">{{ target.source }}</span></td>
              </tr>
              <tr v-if="targets.length === 0">
                <td colspan="7" class="text-center text-muted py-4">Nenhum registo encontrado.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">Página {{ pagination.current_page }} de {{ pagination.last_page }}</small>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1">Anterior</button>
            <button class="btn btn-outline-secondary" @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page">Próxima</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdTargetsPage',
  data() {
    return {
      loading: false,
      adHealth: {},
      jsonStatus: {},
      filters: {
        machine: '',
        user: '',
        group: '',
        perPage: 25
      },
      targets: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0
      }
    };
  },
  mounted() {
    this.refresh();
  },
  methods: {
    async refresh() {
      this.loading = true;
      await Promise.all([this.loadHealth(), this.loadJsonStatus(), this.loadTargets()]);
      this.loading = false;
    },
    async loadHealth() {
      const response = await this.$http.get('/api/ad/health');
      if (response.data) {
        this.adHealth = response.data;
      }
    },
    async loadJsonStatus() {
      const response = await this.$http.get('/api/ad/json-status');
      if (response.data) {
        this.jsonStatus = response.data;
      }
    },
    async loadTargets() {
      const params = {
        machine: this.filters.machine || undefined,
        user: this.filters.user || undefined,
        group: this.filters.group || undefined,
        per_page: this.filters.perPage,
        page: this.pagination.current_page
      };
      const response = await this.$http.get('/api/ad-targets', { params });
      if (response.data && response.data.success) {
        this.targets = response.data.targets.data || [];
        this.pagination = {
          current_page: response.data.targets.current_page,
          last_page: response.data.targets.last_page,
          total: response.data.targets.total
        };
      }
    },
    async applyFilters() {
      this.pagination.current_page = 1;
      await this.loadTargets();
    },
    async resetFilters() {
      this.filters = {
        machine: '',
        user: '',
        group: '',
        perPage: 25
      };
      await this.applyFilters();
    },
    async goToPage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.pagination.current_page = page;
      await this.loadTargets();
    }
  }
};
</script>
