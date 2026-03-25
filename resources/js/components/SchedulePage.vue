<template>
    <!-- Cabeçalho -->
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="h2 mb-1">Agendamentos</h1>
          <p class="text-muted mb-0">Gerencie os horários de execução dos vídeos</p>
        </div>
        <button class="btn btn-primary" @click="openCreateScheduleModal">
          <i class="bi bi-plus me-1"></i>
          Novo Agendamento
        </button>
      </div>
    </div>

    <!-- Modal de Agendamento -->
    <div class="modal fade" id="scheduleModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingSchedule ? 'Editar Agendamento' : 'Criar Novo Agendamento' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" @click="cancelForm"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="editingSchedule ? updateSchedule() : createSchedule()">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="schedule-title" class="form-label">Título *</label>
              <input type="text" class="form-control" id="schedule-title" 
                     v-model="formData.title" 
                     placeholder="Ex: Demonstração do produto" 
                     required>
            </div>
            <div class="col-md-6">
              <label for="schedule-video" class="form-label">Vídeo *</label>
              <select class="form-select" id="schedule-video" 
                      v-model="formData.video_url" 
                      required>
                <option value="">Selecione um vídeo</option>
                <option v-for="video in videos" :key="video.id" :value="video.name">
                  {{ video.title }} ({{ video.duration }})
                  <span v-if="video.cached" class="text-success">
                    <i class="bi bi-check-circle ms-1"></i> Cache
                  </span>
                </option>
              </select>
              <div class="form-text">
                <span v-if="selectedVideo" class="small">
                  URL: {{ selectedVideo.url }}
                </span>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="schedule-time" class="form-label">Horário *</label>
              <input type="time" class="form-control" id="schedule-time" 
                     v-model="formData.time" 
                     required>
            </div>
            <div class="col-md-6">
              <label for="schedule-monitor" class="form-label">Monitor *</label>
              <select class="form-select" id="schedule-monitor" 
                      v-model="formData.monitor" 
                      required>
                <option value="Principal">Principal</option>
                <option value="Secundário">Secundário</option>
                <option value="Todos">Todos os Monitores</option>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="schedule-campaign" class="form-label">Campanha</label>
              <select class="form-select" id="schedule-campaign" v-model="formData.campaign_id">
                <option :value="null">Sem campanha</option>
                <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                  {{ campaign.name }} (P{{ campaign.priority || 0 }})
                </option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="schedule-priority" class="form-label">Prioridade</label>
              <input type="number" class="form-control" id="schedule-priority"
                     v-model.number="formData.priority"
                     min="0" max="100" step="1">
              <div class="form-text">Maior prioridade vence em caso de conflito.</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Público-alvo</label>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small text-muted">Grupos (AD)</label>
                <select class="form-select" multiple v-model="formData.target_groups">
                  <option v-for="group in adGroups" :key="group.id" :value="group.id">
                    {{ group.name }}
                  </option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small text-muted">Clientes específicos</label>
                <select class="form-select" multiple v-model="formData.target_clients">
                  <option v-for="client in clients" :key="client.id" :value="client.id">
                    {{ client.client_id }}{{ client.hostname ? ` (${client.hostname})` : '' }}
                  </option>
                </select>
              </div>
            </div>
            <div class="form-text">Se não selecionar grupos nem clientes, o agendamento é global.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Dias da Semana *</label>
            <div class="d-flex flex-wrap gap-2">
              <span
                v-for="day in daysOfWeek"
                :key="day.short"
                class="day-badge"
                :class="{ 
                  'active': formData.days.includes(day.short),
                  'today': isToday(day.short) 
                }"
                @click="toggleDay(day.short)"
                :title="day.full"
              >
                {{ day.short }}
              </span>
            </div>
            <div class="form-text">Hoje: {{ currentDayName }}</div>
          </div>

          <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" 
                     id="schedule-active" v-model="formData.active">
              <label class="form-check-label" for="schedule-active">Ativo</label>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary" @click="cancelForm">
                Cancelar
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                {{ editingSchedule ? 'Atualizar' : 'Criar' }}
              </button>
            </div>
          </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card bg-info bg-opacity-10 border-info">
          <div class="card-body text-center py-3">
            <h3 class="mb-1">{{ stats.total }}</h3>
            <p class="small text-muted mb-0">Total Agendamentos</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success bg-opacity-10 border-success">
          <div class="card-body text-center py-3">
            <h3 class="mb-1">{{ stats.active }}</h3>
            <p class="small text-muted mb-0">Ativos</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-primary bg-opacity-10 border-primary">
          <div class="card-body text-center py-3">
            <h3 class="mb-1">{{ stats.today }}</h3>
            <p class="small text-muted mb-0">Para Hoje</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-warning bg-opacity-10 border-warning">
          <div class="card-body text-center py-3">
            <h3 class="mb-1">{{ nextSchedule }}</h3>
            <p class="small text-muted mb-0">Próximo Horário</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de Agendamentos -->
    <div id="schedules-list">
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-3 text-muted">Carregando agendamentos...</p>
      </div>

      <div v-else-if="schedules.length === 0" class="text-center py-5">
        <i class="bi bi-calendar-x text-muted fs-1"></i>
        <h4 class="mt-3 text-muted">Nenhum agendamento encontrado</h4>
        <p class="text-muted">Clique em "Novo Agendamento" para começar</p>
      </div>

      <div v-else>
        <div class="card mb-3" v-for="schedule in schedules" :key="schedule.id">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div class="flex-fill">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <h5 class="mb-0">{{ schedule.title }}</h5>
                  <span class="badge" :class="schedule.active ? 'bg-success' : 'bg-secondary'">
                    {{ schedule.active ? 'Ativo' : 'Inativo' }}
                  </span>
                  <span v-if="isToday(schedule.days)" class="badge bg-primary">
                    <i class="bi bi-star-fill me-1"></i>Hoje
                  </span>
                  <span class="badge" :class="getMonitorBadgeClass(schedule.monitor)">
                    {{ schedule.monitor }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-4 small text-muted mb-2">
                  <span>
                    <i class="bi bi-clock me-1"></i>
                    {{ schedule.time }} • {{ schedule.duration }}
                  </span>
                  <span>
                    <i class="bi bi-play-circle me-1"></i>
                    {{ schedule.video_url }}
                  </span>
                  <span v-if="schedule.campaign">
                    <i class="bi bi-flag me-1"></i>
                    {{ schedule.campaign.name }}
                  </span>
                  <span>
                    <i class="bi bi-bar-chart me-1"></i>
                    P{{ schedule.priority || 0 }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <i class="bi bi-calendar3 text-muted"></i>
                  <div class="d-flex gap-1">
                    <span class="badge" 
                          :class="isToday(day) ? 'bg-primary' : 'bg-secondary bg-opacity-50'"
                      v-for="day in schedule.days" 
                      :key="day">
                      {{ day }}
                    </span>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 small text-muted">
                  <i class="bi bi-people text-muted"></i>
                  <span v-if="!schedule.target_groups?.length && !schedule.target_clients?.length">
                    Global
                  </span>
                  <span v-else>
                    <span v-if="schedule.target_groups?.length">Grupos: {{ schedule.target_groups.length }}</span>
                    <span v-if="schedule.target_groups?.length && schedule.target_clients?.length"> • </span>
                    <span v-if="schedule.target_clients?.length">Clientes: {{ schedule.target_clients.length }}</span>
                  </span>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" 
                        @click="toggleScheduleStatus(schedule.id)"
                        :title="schedule.active ? 'Desativar' : 'Ativar'">
                  <i class="bi" :class="schedule.active ? 'bi-power' : 'bi-power-off'"></i>
                </button>
                <button class="btn btn-outline-secondary btn-sm" 
                        @click="duplicateSchedule(schedule.id)"
                        title="Duplicar">
                  <i class="bi bi-files"></i>
                </button>
                <button class="btn btn-outline-secondary btn-sm" 
                        @click="editSchedule(schedule)"
                        title="Editar">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-outline-danger btn-sm" 
                        @click="deleteSchedule(schedule.id)"
                        title="Excluir">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmação</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>{{ confirmMessage }}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" @click="confirmAction" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast de Notificação -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
      <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" :class="toastClass">
          <i class="bi me-2" :class="toastIcon"></i>
          <strong class="me-auto">{{ toastTitle }}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
          {{ toastMessage }}
        </div>
      </div>
    </div>
</template>

<script>
import { Modal, Toast } from 'bootstrap';

export default {
  data() {
    return {
      editingSchedule: null,
      loading: false,
      videos: [],
      campaigns: [],
      adGroups: [],
      clients: [],
      daysOfWeek: [
        { short: "seg", full: "Segunda" },
        { short: "ter", full: "Terça" },
        { short: "qua", full: "Quarta" },
        { short: "qui", full: "Quinta" },
        { short: "sex", full: "Sexta" },
        { short: "sab", full: "Sábado" },
        { short: "dom", full: "Domingo" }
      ],
      formData: {
        title: '',
        video_url: '',
        time: '',
        days: [],
        monitor: 'Principal',
        active: true,
        campaign_id: null,
        priority: 0,
        target_groups: [],
        target_clients: []
      },
      schedules: [],
      stats: {
        total: 0,
        active: 0,
        today: 0,
        next: null
      },
      nextSchedule: 'N/A',
      currentDayName: '',
      
      // Modal
      scheduleModal: null,
      confirmMessage: '',
      confirmCallback: null,
      confirmModal: null,
      
      // Toast
      toast: null,
      toastTitle: '',
      toastMessage: '',
      toastClass: '',
      toastIcon: ''
    };
  },
  computed: {
    selectedVideo() {
      return this.videos.find(v =>
        v.name === this.formData.video_url
        || v.title === this.formData.video_url
        || v.url === this.formData.video_url
        || v.id === this.formData.video_id
      );
    },
    todayShort() {
      const daysMap = {
        'Sunday': 'dom',
        'Monday': 'seg', 
        'Tuesday': 'ter',
        'Wednesday': 'qua',
        'Thursday': 'qui',
        'Friday': 'sex',
        'Saturday': 'sab'
      };
      const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
      return daysMap[today] || '';
    }
  },
  mounted() {
    this.scheduleModal = new Modal(document.getElementById('scheduleModal'));
    this.confirmModal = new Modal(document.getElementById('confirmModal'));
    this.toast = new Toast(document.getElementById('toast'));
    this.loadCurrentDay();
    this.loadSchedules();
    this.loadVideos();
    this.loadCampaigns();
    this.loadAdGroups();
    this.loadClients();
  },
  methods: {
    loadCurrentDay() {
      const date = new Date();
      const days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
      this.currentDayName = days[date.getDay()];
    },
    
    isToday(dayShort) {
      return dayShort === this.todayShort;
    },
    
    async loadSchedules() {
      try {
        this.loading = true;
        const response = await axios.get('/api/schedules');
        this.schedules = response.data;
        this.calculateStats();
      } catch (error) {
        console.error('Erro ao carregar agendamentos:', error);
        this.showToast('Erro', 'Falha ao carregar agendamentos', 'error', 'bi-exclamation-triangle');
      } finally {
        this.loading = false;
      }
    },
    
    calculateStats() {
      const today = this.todayShort;
      
      this.stats.total = this.schedules.length;
      this.stats.active = this.schedules.filter(s => s.active).length;
      this.stats.today = this.schedules.filter(s => 
        s.active && s.days && s.days.includes(today)
      ).length;
      
      // Encontrar próximo horário de hoje
      const now = new Date();
      const currentTime = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
      
      const todaySchedules = this.schedules.filter(s => 
        s.active && s.days && s.days.includes(today) && s.time >= currentTime
      );
      
      if (todaySchedules.length > 0) {
        const next = todaySchedules.reduce((earliest, current) => 
          earliest.time < current.time ? earliest : current
        );
        this.nextSchedule = next.time;
      } else {
        this.nextSchedule = 'N/A';
      }
    },
    
    async loadVideos() {
      try {
        const response = await axios.get('/api/videos');
        this.videos = response.data.videos || [];
      } catch (error) {
        console.error('Erro ao carregar vídeos:', error);
        this.showToast('Erro', 'Falha ao carregar lista de vídeos', 'error', 'bi-exclamation-triangle');
      }
    },

    async loadCampaigns() {
      try {
        const response = await axios.get('/api/campaigns');
        this.campaigns = response.data.campaigns || [];
      } catch (error) {
        console.error('Erro ao carregar campanhas:', error);
        this.showToast('Erro', 'Falha ao carregar campanhas', 'error', 'bi-exclamation-triangle');
      }
    },

    async loadAdGroups() {
      try {
        const response = await axios.get('/api/ad-groups');
        this.adGroups = response.data.groups || [];
      } catch (error) {
        console.error('Erro ao carregar grupos:', error);
        this.showToast('Erro', 'Falha ao carregar grupos', 'error', 'bi-exclamation-triangle');
      }
    },

    async loadClients() {
      try {
        const response = await axios.get('/api/clients');
        this.clients = response.data.clients || [];
      } catch (error) {
        console.error('Erro ao carregar clientes:', error);
        this.showToast('Erro', 'Falha ao carregar clientes', 'error', 'bi-exclamation-triangle');
      }
    },
    
    openCreateScheduleModal() {
      this.editingSchedule = null;
      this.resetForm();
      this.scheduleModal.show();
    },
    
    resetForm() {
      this.formData = {
        title: '',
        video_url: '',
        time: '',
        days: [],
        monitor: 'Principal',
        active: true,
        campaign_id: null,
        priority: 0,
        target_groups: [],
        target_clients: []
      };
      this.editingSchedule = null;
    },
    
    cancelForm() {
      this.scheduleModal.hide();
      this.resetForm();
    },
    
    toggleDay(day) {
      const index = this.formData.days.indexOf(day);
      if (index === -1) {
        this.formData.days.push(day);
      } else {
        this.formData.days.splice(index, 1);
      }
    },
    
    editSchedule(schedule) {
      const selectedVideo = this.videos.find(video => {
        return video.id === schedule.video_id
          || video.name === schedule.video_url
          || video.title === schedule.video_url
          || video.url === schedule.video_url;
      });

      this.editingSchedule = schedule;
      this.formData = {
        title: schedule.title,
        video_url: selectedVideo ? selectedVideo.name : schedule.video_url,
        time: schedule.time,
        days: schedule.days || [],
        monitor: schedule.monitor,
        active: schedule.active,
        campaign_id: schedule.campaign_id || null,
        priority: schedule.priority || 0,
        target_groups: schedule.target_groups || [],
        target_clients: schedule.target_clients || []
      };
      this.scheduleModal.show();
    },
    
    async createSchedule() {
      if (!this.validateForm()) {
        return;
      }

      this.loading = true;
      try {
        const response = await axios.post('/api/schedules', this.formData);
        
        if (response.data.success) {
          this.schedules.push(response.data.schedule);
          this.calculateStats();
          this.scheduleModal.hide();
          this.resetForm();
          this.showToast('Sucesso', 'Agendamento criado com sucesso', 'success', 'bi-check-circle');
        } else {
          throw new Error(response.data.message);
        }
      } catch (error) {
        console.error('Erro ao criar agendamento:', error);
        this.showToast('Erro', error.response?.data?.message || 'Falha ao criar agendamento', 'error', 'bi-exclamation-triangle');
      } finally {
        this.loading = false;
      }
    },
    
    async updateSchedule() {
      if (!this.validateForm()) {
        return;
      }

      this.loading = true;
      try {
        const response = await axios.put(`/api/schedules/${this.editingSchedule.id}`, this.formData);
        
        if (response.data.success) {
          const index = this.schedules.findIndex(s => s.id === this.editingSchedule.id);
          if (index !== -1) {
            this.schedules[index] = response.data.schedule;
          }
          this.calculateStats();
          this.scheduleModal.hide();
          this.resetForm();
          this.showToast('Sucesso', 'Agendamento atualizado com sucesso', 'success', 'bi-check-circle');
        } else {
          throw new Error(response.data.message);
        }
      } catch (error) {
        console.error('Erro ao atualizar agendamento:', error);
        const validationErrors = error.response?.data?.errors;
        const firstValidationError = validationErrors
          ? Object.values(validationErrors).flat()[0]
          : null;

        this.showToast(
          'Erro',
          firstValidationError || error.response?.data?.message || 'Falha ao atualizar agendamento',
          'error',
          'bi-exclamation-triangle'
        );
      } finally {
        this.loading = false;
      }
    },
    
    async toggleScheduleStatus(id) {
      try {
        const response = await axios.post(`/api/schedules/${id}/toggle`);
        
        if (response.data.success) {
          const schedule = this.schedules.find(s => s.id === id);
          if (schedule) {
            schedule.active = response.data.active;
            this.calculateStats();
            this.showToast('Status Alterado', 
              `Agendamento ${schedule.active ? 'ativado' : 'desativado'}`, 
              'info', 'bi-info-circle');
          }
        }
      } catch (error) {
        console.error('Erro ao alterar status:', error);
        this.showToast('Erro', 'Falha ao alterar status do agendamento', 'error', 'bi-exclamation-triangle');
      }
    },
    
    async duplicateSchedule(id) {
      try {
        const response = await axios.post(`/api/schedules/${id}/duplicate`);
        
        if (response.data.success) {
          this.schedules.push(response.data.schedule);
          this.calculateStats();
          this.showToast('Agendamento Duplicado', 'Cópia criada com sucesso', 'success', 'bi-check-circle');
        }
      } catch (error) {
        console.error('Erro ao duplicar agendamento:', error);
        this.showToast('Erro', 'Falha ao duplicar agendamento', 'error', 'bi-exclamation-triangle');
      }
    },
    
    deleteSchedule(id) {
      const schedule = this.schedules.find(s => s.id === id);
      if (schedule) {
        this.showConfirmModal(
          `Tem certeza que deseja excluir o agendamento "${schedule.title}"?`,
          async () => {
            this.loading = true;
            try {
              const response = await axios.delete(`/api/schedules/${id}`);
              
              if (response.data.success) {
                this.schedules = this.schedules.filter(s => s.id !== id);
                this.calculateStats();
                this.showToast('Agendamento Removido', 'O agendamento foi excluído com sucesso', 'success', 'bi-check-circle');
              }
            } catch (error) {
              console.error('Erro ao excluir agendamento:', error);
              this.showToast('Erro', 'Falha ao excluir agendamento', 'error', 'bi-exclamation-triangle');
            } finally {
              this.loading = false;
            }
          }
        );
      }
    },
    
    validateForm() {
      if (!this.formData.title.trim()) {
        this.showToast('Atenção', 'Informe um título para o agendamento', 'warning', 'bi-exclamation-triangle');
        return false;
      }
      if (!this.formData.video_url) {
        this.showToast('Atenção', 'Selecione um vídeo', 'warning', 'bi-exclamation-triangle');
        return false;
      }
      if (!this.formData.time) {
        this.showToast('Atenção', 'Informe um horário', 'warning', 'bi-exclamation-triangle');
        return false;
      }
      if (this.formData.days.length === 0) {
        this.showToast('Atenção', 'Selecione pelo menos um dia da semana', 'warning', 'bi-exclamation-triangle');
        return false;
      }
      return true;
    },
    
    getMonitorBadgeClass(monitor) {
      const classes = {
        'Principal': 'bg-primary',
        'Secundário': 'bg-info',
        'Todos': 'bg-success'
      };
      return classes[monitor] || 'bg-secondary';
    },
    
    showConfirmModal(message, callback) {
      this.confirmMessage = message;
      this.confirmCallback = callback;
      this.confirmModal.show();
    },
    
    confirmAction() {
      if (this.confirmCallback) {
        this.confirmCallback();
      }
      this.confirmModal.hide();
    },
    
    showToast(title, message, type = 'info', icon = 'bi-info-circle') {
      const toastClasses = {
        'success': 'bg-success text-white',
        'error': 'bg-danger text-white',
        'warning': 'bg-warning text-dark',
        'info': 'bg-info text-white'
      };
      
      this.toastTitle = title;
      this.toastMessage = message;
      this.toastClass = toastClasses[type] || 'bg-info text-white';
      this.toastIcon = icon;
      
      this.toast.show();
    }
  }
};
</script>

<style scoped>
.day-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 50rem;
  background-color: #e9ecef;
  color: #495057;
  cursor: pointer;
  transition: all 0.2s;
  user-select: none;
  font-size: 0.875rem;
  font-weight: 500;
}

.day-badge:hover {
  background-color: #dee2e6;
  transform: scale(1.05);
}

.day-badge.active {
  background-color: #0d6efd;
  color: white;
}

.day-badge.today {
  border: 2px solid #198754;
}

.day-badge.active.today {
  background-color: #198754;
  border-color: #198754;
}

.card {
  border: none;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.badge {
  font-weight: 500;
  letter-spacing: 0.3px;
}

.btn-outline-secondary:hover {
  background-color: #6c757d;
  color: white;
}

.toast {
  min-width: 300px;
}
</style>
