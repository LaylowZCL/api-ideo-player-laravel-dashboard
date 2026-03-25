<template>
  <div>
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h1 class="page-title mb-1">Grupos</h1>
        <p class="page-subtitle mb-0">Gestão de grupos AD e segmentações de audiência</p>
      </div>
      <button class="btn btn-primary" @click="openModal">
        <i class="bi bi-plus me-1"></i>
        Novo Grupo
      </button>
    </div>

    <div class="card toolbar-card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-center">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-transparent border-0 text-muted">
                <i class="bi bi-search"></i>
              </span>
              <input type="text" class="form-control" placeholder="Pesquisar por nome ou DN" v-model="searchTerm">
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="statusFilter">
              <option value="">Todos</option>
              <option value="1">Ativos</option>
              <option value="0">Inativos</option>
            </select>
          </div>
          <div class="col-md-3 text-end">
            <span class="chip chip-muted">{{ filteredGroups.length }} grupos</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
      <p class="text-muted mt-3">Carregando grupos...</p>
    </div>

    <div v-else class="table-responsive">
      <table class="table table-dark table-hover align-middle">
        <thead>
          <tr>
            <th>Nome</th>
            <th>DN</th>
            <th>Fonte</th>
            <th>Status</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="group in filteredGroups" :key="group.id">
            <td class="fw-semibold">{{ group.name }}</td>
            <td class="text-muted">{{ group.dn || '-' }}</td>
            <td>
              <span class="chip" :class="group.source === 'ad' ? 'chip-admin' : 'chip-user'">
                {{ group.source || 'manual' }}
              </span>
            </td>
            <td>
              <span class="chip" :class="group.active ? 'chip-manager' : 'chip-muted'">
                {{ group.active ? 'Ativo' : 'Inativo' }}
              </span>
            </td>
            <td class="text-end">
              <button class="btn btn-outline-light btn-sm me-2" @click="editGroup(group)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm" @click="deleteGroup(group)">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
          <tr v-if="filteredGroups.length === 0">
            <td colspan="5" class="text-center text-muted py-4">Nenhum grupo encontrado.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="groupModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingGroup ? 'Editar Grupo' : 'Novo Grupo' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveGroup">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nome *</label>
                  <input type="text" class="form-control" v-model="formData.name" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">DN</label>
                  <input type="text" class="form-control" v-model="formData.dn">
                </div>
                <div class="col-md-6">
                  <label class="form-label">SID</label>
                  <input type="text" class="form-control" v-model="formData.sid">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Fonte</label>
                  <select class="form-select" v-model="formData.source">
                    <option value="manual">Manual</option>
                    <option value="ad">AD</option>
                    <option value="client">Cliente</option>
                  </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" v-model="formData.active">
                    <label class="form-check-label">Ativo</label>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                  Salvar
                </button>
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
  name: 'AdGroupsPage',
  data() {
    return {
      groups: [],
      loading: false,
      saving: false,
      modal: null,
      editingGroup: null,
      searchTerm: '',
      statusFilter: '',
      formData: {
        name: '',
        dn: '',
        sid: '',
        source: 'manual',
        active: true
      }
    };
  },
  computed: {
    filteredGroups() {
      const term = this.searchTerm.trim().toLowerCase();
      return this.groups.filter(group => {
        const matchesTerm = !term || group.name.toLowerCase().includes(term) || (group.dn || '').toLowerCase().includes(term);
        const matchesStatus = this.statusFilter === '' || String(Number(group.active)) === this.statusFilter;
        return matchesTerm && matchesStatus;
      });
    }
  },
  mounted() {
    this.modal = new Modal(document.getElementById('groupModal'));
    this.fetchGroups();
  },
  methods: {
    async fetchGroups() {
      this.loading = true;
      try {
        const response = await axios.get('/api/ad-groups');
        this.groups = response.data.groups || [];
      } finally {
        this.loading = false;
      }
    },
    openModal() {
      this.editingGroup = null;
      this.formData = { name: '', dn: '', sid: '', source: 'manual', active: true };
      this.modal.show();
    },
    editGroup(group) {
      this.editingGroup = group;
      this.formData = {
        name: group.name,
        dn: group.dn || '',
        sid: group.sid || '',
        source: group.source || 'manual',
        active: !!group.active
      };
      this.modal.show();
    },
    async saveGroup() {
      this.saving = true;
      try {
        if (this.editingGroup) {
          await axios.put(`/api/ad-groups/${this.editingGroup.id}`, this.formData);
        } else {
          await axios.post('/api/ad-groups', this.formData);
        }
        await this.fetchGroups();
        this.modal.hide();
      } finally {
        this.saving = false;
      }
    },
    async deleteGroup(group) {
      if (!confirm(`Deseja remover o grupo ${group.name}?`)) return;
      await axios.delete(`/api/ad-groups/${group.id}`);
      await this.fetchGroups();
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

.chip-user {
  background: linear-gradient(135deg, #5c6e8f, #738bb5);
}
</style>
