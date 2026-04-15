<template>
  <div class="admin-targets">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h1 class="h2 mb-1">Alvos AD</h1>
        <p class="text-muted mb-0">Auditoria do ficheiro AD e direccionamento por máquina/utilizador.</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" @click="importAdFile" :disabled="loading || importLoading">
          <i class="bi bi-arrow-repeat me-1"></i>
          {{ importLoading ? 'A importar...' : 'Actualizar' }}
        </button>
      </div>
    </div>

    <div v-if="feedback.message" class="alert mb-4" :class="feedback.success ? 'alert-success' : 'alert-danger'">
      {{ feedback.message }}
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
            <h5 class="card-title mb-0">Ficheiro AD (`{{ jsonStatus.filename || 'não configurado' }}`)</h5>
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
              <div class="col-6">
                <div class="small text-muted">Grupos processados</div>
                <div class="h6 mb-0">{{ jsonStatus.groups_processed ?? 0 }}</div>
              </div>
              <div class="col-6">
                <div class="small text-muted">Alvos processados</div>
                <div class="h6 mb-0">{{ jsonStatus.targets_processed ?? 0 }}</div>
              </div>
              <div class="col-6">
                <div class="small text-muted">Clientes afectados</div>
                <div class="h6 mb-0">{{ (jsonStatus.clients_created ?? 0) + (jsonStatus.clients_updated ?? 0) }}</div>
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
                <th style="width: 72px;">#</th>
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
              <tr v-for="(target, index) in targets" :key="target.id" class="target-row" @click="openCreateUserModal(target)">
                <td class="text-muted">{{ rowNumber(index) }}</td>
                <td class="fw-medium">{{ target.machine_name }}</td>
                <td>{{ target.user_name || '-' }}</td>
                <td>{{ target.user_display_name || '-' }}</td>
                <td>{{ target.user_email || '-' }}</td>
                <td>{{ target.group || '-' }}</td>
                <td>{{ target.effective_at || '-' }}</td>
                <td><span class="badge bg-secondary">{{ target.source }}</span></td>
              </tr>
              <tr v-if="targets.length === 0">
                <td colspan="8" class="text-center text-muted py-4">Nenhum registo encontrado.</td>
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

    <div class="modal fade" id="adTargetUserModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Criar utilizador local a partir do alvo AD</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="createUserFromTarget">
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label">Nome</label>
                  <input v-model="userForm.name" type="text" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input v-model="userForm.email" type="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Nome de utilizador</label>
                  <input v-model="userForm.username" type="text" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Perfil</label>
                  <select v-model="userForm.role" class="form-select" required>
                    <option value="user">Utilizador</option>
                    <option value="manager">Gestor</option>
                    <option v-if="['super_admin', 'admin'].includes(currentUser.role)" value="admin">Administrador</option>
                    <option v-if="currentUser.role === 'super_admin'" value="super_admin">Super Administrador</option>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Funções liberadas</label>
                <div class="permissions-grid">
                  <label class="permission-chip" v-for="permission in availablePermissionOptions" :key="permission.key">
                    <input type="checkbox" :value="permission.key" v-model="userForm.permissions">
                    <span>{{ permission.label }}</span>
                  </label>
                </div>
              </div>

              <div class="alert alert-info">
                O utilizador receberá um email de boas-vindas do Banco de Moçambique com a palavra-passe inicial e será
                obrigado a alterá-la no primeiro acesso.
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                  Origem: {{ selectedTarget?.group || '-' }} · {{ selectedTarget?.machine_name || '-' }}
                </div>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary" :disabled="creatingUser">
                    {{ creatingUser ? 'A criar...' : 'Criar utilizador' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'AdTargetsPage',
  data() {
    return {
      loading: false,
      importLoading: false,
      creatingUser: false,
      userModal: null,
      selectedTarget: null,
      adHealth: {},
      jsonStatus: {},
      currentUser: {
        role: 'user'
      },
      permissionOptions: [
        { key: 'dashboard', label: 'Painel' },
        { key: 'videos', label: 'Vídeos' },
        { key: 'schedules', label: 'Agendamentos' },
        { key: 'reports', label: 'Relatórios' },
        { key: 'users', label: 'Utilizadores' },
        { key: 'groups', label: 'Grupos AD' },
        { key: 'targets', label: 'Alvos AD' },
        { key: 'clients', label: 'Clientes' },
        { key: 'campaigns', label: 'Campanhas' },
        { key: 'logs', label: 'Logs' },
        { key: 'settings', label: 'Configurações' }
      ],
      userForm: {
        target_id: null,
        name: '',
        email: '',
        username: '',
        role: 'user',
        permissions: ['dashboard', 'reports']
      },
      feedback: {
        success: true,
        message: ''
      },
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
  computed: {
    allowedPermissionKeys() {
      const matrix = {
        super_admin: this.permissionOptions.map(item => item.key),
        admin: ['dashboard', 'videos', 'schedules', 'reports', 'users', 'groups', 'targets', 'clients', 'campaigns', 'logs', 'settings'],
        manager: ['dashboard', 'videos', 'schedules', 'reports', 'clients', 'campaigns'],
        user: ['dashboard', 'reports']
      };

      return matrix[this.userForm.role] || matrix.user;
    },
    availablePermissionOptions() {
      return this.permissionOptions.filter(option => this.allowedPermissionKeys.includes(option.key));
    }
  },
  mounted() {
    this.userModal = new Modal(document.getElementById('adTargetUserModal'));
    this.refresh();
    this.loadCurrentUser();
  },
  methods: {
    async loadCurrentUser() {
      const response = await this.$http.get('/api/current-user');
      this.currentUser = response.data || { role: 'user' };
    },
    async refresh() {
      this.loading = true;
      await Promise.all([this.loadHealth(), this.loadJsonStatus(), this.loadTargets()]);
      this.loading = false;
    },
    openCreateUserModal(target) {
      this.selectedTarget = target;
      this.userForm = {
        target_id: target.id,
        name: target.user_display_name || '',
        email: target.user_email || '',
        username: target.user_name || '',
        role: 'user',
        permissions: ['dashboard', 'reports']
      };
      this.userModal.show();
    },
    async createUserFromTarget() {
      this.creatingUser = true;
      try {
        this.userForm.permissions = this.userForm.permissions.filter(permission =>
          this.allowedPermissionKeys.includes(permission)
        );

        const response = await this.$http.post('/api/users/from-ad-target', this.userForm);
        this.feedback = {
          success: !!response.data?.success,
          message: response.data?.message || 'Utilizador criado com sucesso.'
        };

        if (response.data?.success) {
          this.userModal.hide();
        }
      } catch (error) {
        const firstError = error.response?.data?.errors
          ? Object.values(error.response.data.errors)[0][0]
          : null;
        this.feedback = {
          success: false,
          message: firstError || error.response?.data?.message || 'Falha ao criar utilizador a partir do alvo AD.'
        };
      } finally {
        this.creatingUser = false;
      }
    },
    async importAdFile() {
      this.importLoading = true;
      this.feedback.message = '';

      try {
        const response = await this.$http.get('/actualizar-json');
        const success = !!response.data?.success;
        const output = response.data?.output ? ` ${response.data.output}` : '';

        this.feedback = {
          success,
          message: success
            ? `${response.data.message || 'Ficheiro AD importado com sucesso.'}${output}`
            : (response.data?.message || 'Não foi possível importar o ficheiro AD.')
        };

        if (success) {
          await this.refresh();
        }
      } catch (error) {
        this.feedback = {
          success: false,
          message: error.response?.data?.message || 'Falha ao importar o ficheiro AD.'
        };
      } finally {
        this.importLoading = false;
      }
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
    },
    rowNumber(index) {
      return ((this.pagination.current_page - 1) * this.filters.perPage) + index + 1;
    }
  }
};
</script>

<style scoped>
.target-row {
  cursor: pointer;
}

.target-row:hover {
  background: rgba(255, 255, 255, 0.06);
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.75rem;
}

.permission-chip {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 0.9rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.04);
}
</style>
