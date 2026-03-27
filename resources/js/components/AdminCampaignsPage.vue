<template>
  <div>
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h1 class="page-title mb-1">Campanhas</h1>
        <p class="page-subtitle mb-0">Defina prioridades e janelas de publicação</p>
      </div>
      <button class="btn btn-primary" @click="openModal">
        <i class="bi bi-plus me-1"></i>
        Nova Campanha
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
              <input type="text" class="form-control" placeholder="Pesquisar por nome" v-model="searchTerm">
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="statusFilter">
              <option value="">Todos</option>
              <option value="1">Ativas</option>
              <option value="0">Inativas</option>
            </select>
          </div>
          <div class="col-md-3 text-end">
            <span class="chip chip-muted">{{ filteredCampaigns.length }} campanhas</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
      <p class="text-muted mt-3">A carregar campanhas...</p>
    </div>

    <div v-else>
      <div class="campaign-card" v-for="campaign in filteredCampaigns" :key="campaign.id">
        <div>
          <div class="campaign-title">{{ campaign.name }}</div>
          <div class="campaign-subtitle">{{ campaign.description || 'Sem descrição' }}</div>
          <div class="campaign-tags">
            <span class="chip chip-admin">Prioridade {{ campaign.priority || 0 }}</span>
            <span class="chip" :class="campaign.active ? 'chip-manager' : 'chip-muted'">
              {{ campaign.active ? 'Ativa' : 'Inativa' }}
            </span>
          </div>
        </div>
        <div class="campaign-actions">
          <button class="btn btn-outline-light btn-sm me-2" @click="editCampaign(campaign)">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-outline-danger btn-sm" @click="deleteCampaign(campaign)">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
      <div v-if="filteredCampaigns.length === 0" class="text-center text-muted py-4">
        Nenhuma campanha encontrada.
      </div>
    </div>

    <div class="modal fade" id="campaignModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingCampaign ? 'Editar Campanha' : 'Nova Campanha' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveCampaign">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nome *</label>
                  <input type="text" class="form-control" v-model="formData.name" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Prioridade</label>
                  <input type="number" class="form-control" v-model.number="formData.priority" min="0" max="100">
                </div>
                <div class="col-md-12">
                  <label class="form-label">Descrição</label>
                  <textarea class="form-control" rows="2" v-model="formData.description"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Início</label>
                  <input type="datetime-local" class="form-control" v-model="formData.starts_at">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Fim</label>
                  <input type="datetime-local" class="form-control" v-model="formData.ends_at">
                </div>
                <div class="col-md-3">
                  <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" v-model="formData.active">
                    <label class="form-check-label">Ativa</label>
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
  name: 'AdminCampaignsPage',
  data() {
    return {
      campaigns: [],
      loading: false,
      saving: false,
      modal: null,
      editingCampaign: null,
      searchTerm: '',
      statusFilter: '',
      formData: {
        name: '',
        description: '',
        active: true,
        priority: 0,
        starts_at: '',
        ends_at: ''
      }
    };
  },
  computed: {
    filteredCampaigns() {
      const term = this.searchTerm.trim().toLowerCase();
      return this.campaigns.filter(campaign => {
        const matchesTerm = !term || campaign.name.toLowerCase().includes(term);
        const matchesStatus = this.statusFilter === '' || String(Number(campaign.active)) === this.statusFilter;
        return matchesTerm && matchesStatus;
      });
    }
  },
  mounted() {
    this.modal = new Modal(document.getElementById('campaignModal'));
    this.fetchCampaigns();
  },
  methods: {
    async fetchCampaigns() {
      this.loading = true;
      try {
        const response = await axios.get('/api/campaigns');
        this.campaigns = response.data.campaigns || [];
      } finally {
        this.loading = false;
      }
    },
    openModal() {
      this.editingCampaign = null;
      this.formData = { name: '', description: '', active: true, priority: 0, starts_at: '', ends_at: '' };
      this.modal.show();
    },
    editCampaign(campaign) {
      this.editingCampaign = campaign;
      this.formData = {
        name: campaign.name,
        description: campaign.description || '',
        active: !!campaign.active,
        priority: campaign.priority || 0,
        starts_at: campaign.starts_at ? campaign.starts_at.slice(0, 16) : '',
        ends_at: campaign.ends_at ? campaign.ends_at.slice(0, 16) : ''
      };
      this.modal.show();
    },
    async saveCampaign() {
      this.saving = true;
      try {
        const payload = { ...this.formData };
        if (this.editingCampaign) {
          await axios.put(`/api/campaigns/${this.editingCampaign.id}`, payload);
        } else {
          await axios.post('/api/campaigns', payload);
        }
        await this.fetchCampaigns();
        this.modal.hide();
      } finally {
        this.saving = false;
      }
    },
    async deleteCampaign(campaign) {
      if (!confirm(`Deseja remover a campanha ${campaign.name}?`)) return;
      await axios.delete(`/api/campaigns/${campaign.id}`);
      await this.fetchCampaigns();
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

.campaign-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.2rem 1.4rem;
  border-radius: 18px;
  background: rgba(7, 38, 78, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 1rem;
}

.campaign-title {
  font-family: "Inter";
  font-size: 1.1rem;
  font-weight: 600;
}

.campaign-subtitle {
  color: rgba(210, 225, 255, 0.7);
  font-size: 0.85rem;
}

.campaign-tags {
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

.chip-manager {
  background: linear-gradient(135deg, #1f7aa1, #2db9c9);
}
</style>
