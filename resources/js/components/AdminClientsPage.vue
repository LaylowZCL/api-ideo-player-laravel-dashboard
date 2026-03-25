<template>
  <div>
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h1 class="page-title mb-1">Clientes</h1>
        <p class="page-subtitle mb-0">Controle de dispositivos, grupos e chaves por ponto</p>
      </div>
    </div>

    <div class="card toolbar-card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-center">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-transparent border-0 text-muted">
                <i class="bi bi-search"></i>
              </span>
              <input type="text" class="form-control" placeholder="Pesquisar por client_id ou hostname" v-model="searchTerm">
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="platformFilter">
              <option value="">Todas as plataformas</option>
              <option value="win32">Windows</option>
              <option value="darwin">macOS</option>
              <option value="linux">Linux</option>
            </select>
          </div>
          <div class="col-md-3 text-end">
            <span class="chip chip-muted">{{ filteredClients.length }} clientes</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
      <p class="text-muted mt-3">Carregando clientes...</p>
    </div>

    <div v-else>
      <div class="client-card" v-for="client in filteredClients" :key="client.id">
        <div class="client-main">
          <div class="client-avatar">
            <i class="bi bi-pc-display"></i>
          </div>
          <div>
            <div class="client-title">{{ client.client_id }}</div>
            <div class="client-subtitle">
              {{ client.hostname || 'Sem hostname' }} • {{ client.platform || 'N/A' }} • v{{ client.version || '0.0' }}
            </div>
            <div class="client-tags">
              <span class="chip chip-admin">Grupos: {{ client.ad_groups?.length || 0 }}</span>
              <span class="chip chip-muted">Último ping: {{ formatDate(client.last_seen_at) }}</span>
            </div>
          </div>
        </div>
        <div class="client-actions">
          <button class="btn btn-outline-light btn-sm" @click="editClient(client)">
            <i class="bi bi-pencil"></i>
          </button>
        </div>
      </div>
      <div v-if="filteredClients.length === 0" class="text-center text-muted py-4">
        Nenhum cliente encontrado.
      </div>
    </div>

    <div class="modal fade" id="clientModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Editar Cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveClient">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Client ID</label>
                  <input type="text" class="form-control" v-model="formData.client_id" disabled>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Hostname</label>
                  <input type="text" class="form-control" v-model="formData.hostname">
                </div>
                <div class="col-md-6">
                  <label class="form-label">API Key</label>
                  <input type="text" class="form-control" v-model="formData.api_key" placeholder="Opcional">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Grupos</label>
                  <select class="form-select" multiple v-model="formData.ad_group_ids">
                    <option v-for="group in groups" :key="group.id" :value="group.id">
                      {{ group.name }}
                    </option>
                  </select>
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
  name: 'AdminClientsPage',
  data() {
    return {
      clients: [],
      groups: [],
      loading: false,
      saving: false,
      modal: null,
      searchTerm: '',
      platformFilter: '',
      editingClient: null,
      formData: {
        client_id: '',
        hostname: '',
        api_key: '',
        ad_group_ids: []
      }
    };
  },
  computed: {
    filteredClients() {
      const term = this.searchTerm.trim().toLowerCase();
      return this.clients.filter(client => {
        const matchesTerm = !term || client.client_id.toLowerCase().includes(term) || (client.hostname || '').toLowerCase().includes(term);
        const matchesPlatform = !this.platformFilter || client.platform === this.platformFilter;
        return matchesTerm && matchesPlatform;
      });
    }
  },
  mounted() {
    this.modal = new Modal(document.getElementById('clientModal'));
    this.fetchClients();
    this.fetchGroups();
  },
  methods: {
    formatDate(value) {
      if (!value) return 'N/A';
      return new Date(value).toLocaleString();
    },
    async fetchClients() {
      this.loading = true;
      try {
        const response = await axios.get('/api/clients');
        this.clients = response.data.clients || [];
      } finally {
        this.loading = false;
      }
    },
    async fetchGroups() {
      const response = await axios.get('/api/ad-groups');
      this.groups = response.data.groups || [];
    },
    editClient(client) {
      this.editingClient = client;
      this.formData = {
        client_id: client.client_id,
        hostname: client.hostname || '',
        api_key: client.api_key || '',
        ad_group_ids: (client.ad_groups || []).map(g => g.id)
      };
      this.modal.show();
    },
    async saveClient() {
      if (!this.editingClient) return;
      this.saving = true;
      try {
        await axios.put(`/api/clients/${this.editingClient.id}`, {
          hostname: this.formData.hostname,
          api_key: this.formData.api_key,
          ad_group_ids: this.formData.ad_group_ids
        });
        await this.fetchClients();
        this.modal.hide();
      } finally {
        this.saving = false;
      }
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

.client-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.2rem 1.4rem;
  border-radius: 18px;
  background: rgba(7, 38, 78, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 1rem;
}

.client-main {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.client-avatar {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.client-title {
  font-family: "Inter";
  font-size: 1.1rem;
  font-weight: 600;
}

.client-subtitle {
  color: rgba(210, 225, 255, 0.7);
  font-size: 0.85rem;
}

.client-tags {
  margin-top: 0.4rem;
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
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
</style>
