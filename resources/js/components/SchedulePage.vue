<template>
    <!-- Cabeçalho -->
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="h2 mb-1">Agendamentos</h1>
          <p class="text-muted mb-0">Gerencie os horários de execução dos vídeos</p>
        </div>
        <button class="btn btn-primary" @click="toggleScheduleForm">
          <i class="bi bi-plus me-1"></i>
          Novo Agendamento
        </button>
      </div>
    </div>

    <!-- Formulário de Novo Agendamento -->
    <div id="schedule-form" class="card mb-4" v-show="showForm">
      <div class="card-header">
        <h5 class="card-title mb-0">Criar Novo Agendamento</h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="createSchedule">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="schedule-title" class="form-label">Título</label>
              <input type="text" class="form-control" id="schedule-title" v-model="newSchedule.title" placeholder="Ex: Demonstração do produto" required>
            </div>
            <div class="col-md-6">
              <label for="schedule-video" class="form-label">Arquivo de Vídeo</label>
              <input type="text" class="form-control" id="schedule-video" v-model="newSchedule.video" placeholder="Ex: demo_produto.mp4" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="schedule-time" class="form-label">Horário</label>
              <input type="time" class="form-control" id="schedule-time" v-model="newSchedule.time" required>
            </div>
            <div class="col-md-6">
              <label for="schedule-monitor" class="form-label">Monitor</label>
              <select class="form-select" id="schedule-monitor" v-model="newSchedule.monitor" required>
                <option value="Principal">Principal</option>
                <option value="Secundário">Secundário</option>
                <option value="Todos">Todos os Monitores</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Dias da Semana</label>
            <div class="d-flex flex-wrap gap-2">
              <span
                v-for="day in daysOfWeek"
                :key="day.short"
                class="day-badge"
                :class="{ 'active': newSchedule.days.includes(day.short) }"
                @click="toggleDay(day.short)"
              >
                {{ day.short }}
              </span>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="schedule-active" v-model="newSchedule.active">
              <label class="form-check-label" for="schedule-active">Ativo</label>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary" @click="toggleScheduleForm">
                Cancelar
              </button>
              <button type="submit" class="btn btn-primary">
                Criar Agendamento
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Lista de Agendamentos -->
    <div id="schedules-list">
      <div class="card mb-3" v-for="schedule in schedules" :key="schedule.id" :data-schedule-id="schedule.id">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div class="flex-fill">
              <div class="d-flex align-items-center gap-3 mb-2">
                <h5 class="mb-0">{{ schedule.title }}</h5>
                <span class="badge" :class="schedule.active ? 'bg-success' : 'bg-secondary'">
                  {{ schedule.active ? 'Ativo' : 'Inativo' }}
                </span>
              </div>
              <div class="d-flex align-items-center gap-4 small text-muted mb-2">
                <span>
                  <i class="bi bi-clock me-1"></i>
                  {{ schedule.time }} • {{ schedule.duration }}
                </span>
                <span>
                  <i class="bi bi-display me-1"></i>
                  {{ schedule.monitor }}
                </span>
                <span>
                  <i class="bi bi-play-circle me-1"></i>
                  {{ schedule.video }}
                </span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-calendar3 text-muted"></i>
                <div class="d-flex gap-1">
                  <span class="badge bg-secondary bg-opacity-50" v-for="day in schedule.days" :key="day">
                    {{ day }}
                  </span>
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-outline-secondary btn-sm" @click="toggleScheduleStatus(schedule.id)">
                <i class="bi bi-power"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm" @click="duplicateSchedule(schedule.id)">
                <i class="bi bi-files"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm" @click="editSchedule(schedule.id)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm" @click="deleteSchedule(schedule.id)">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
          <div class="modal-header border-secondary">
            <h5 class="modal-title">Confirmação</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>{{ confirmMessage }}</p>
          </div>
          <div class="modal-footer border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" @click="confirmAction">Confirmar</button>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  data() {
    return {
      showForm: false,
      daysOfWeek: [
        { short: "seg", full: "Segunda" },
        { short: "ter", full: "Terça" },
        { short: "qua", full: "Quarta" },
        { short: "qui", full: "Quinta" },
        { short: "sex", full: "Sexta" },
        { short: "sab", full: "Sábado" },
        { short: "dom", full: "Domingo" }
      ],
      newSchedule: {
        title: '',
        video: '',
        time: '',
        days: [],
        monitor: 'Principal',
        active: true,
        duration: "0:00"
      },
      schedules: [
        // Exemplo de dados - na implementação real, estes dados viriam de uma API
        {
          id: 1,
          title: "Demonstração do Produto",
          video: "demo_produto.mp4",
          time: "09:00",
          days: ["seg", "ter", "qua"],
          monitor: "Principal",
          active: true,
          duration: "2:30"
        },
        {
          id: 2,
          title: "Promoção Especial",
          video: "promocao.mp4",
          time: "12:30",
          days: ["sex", "sab"],
          monitor: "Todos",
          active: false,
          duration: "1:45"
        }
      ],
      confirmMessage: '',
      confirmCallback: null,
      confirmModal: null,
      nextId: 3
    };
  },
  mounted() {
    this.confirmModal = new Modal(document.getElementById('confirmModal'));
    this.loadSchedules();
  },
  methods: {
    loadSchedules() {
      // Aqui você faria uma requisição para obter os agendamentos
      // Exemplo com axios:
      /*
      axios.get('/api/schedules')
        .then(response => {
          this.schedules = response.data;
          this.nextId = this.schedules.length > 0 ?
            Math.max(...this.schedules.map(s => s.id)) + 1 : 1;
        })
        .catch(error => {
          console.error('Erro ao carregar agendamentos:', error);
        });
      */
    },
    toggleScheduleForm() {
      this.showForm = !this.showForm;
      if (!this.showForm) {
        this.resetScheduleForm();
      }
    },
    resetScheduleForm() {
      this.newSchedule = {
        title: '',
        video: '',
        time: '',
        days: [],
        monitor: 'Principal',
        active: true,
        duration: "0:00"
      };
    },
    toggleDay(day) {
      const index = this.newSchedule.days.indexOf(day);
      if (index === -1) {
        this.newSchedule.days.push(day);
      } else {
        this.newSchedule.days.splice(index, 1);
      }
    },
    createSchedule() {
      if (!this.newSchedule.title || !this.newSchedule.video || !this.newSchedule.time || this.newSchedule.days.length === 0) {
        this.showToast('Erro', 'Preencha todos os campos obrigatórios', 'error');
        return;
      }

      const schedule = {
        ...this.newSchedule,
        id: this.nextId++
      };

      // Aqui você faria uma requisição para salvar o agendamento
      /*
      axios.post('/api/schedules', schedule)
        .then(response => {
          this.schedules.push(response.data);
          this.toggleScheduleForm();
          this.showToast('Sucesso', 'Agendamento criado com sucesso', 'success');
        })
        .catch(error => {
          console.error('Erro ao criar agendamento:', error);
          this.showToast('Erro', 'Falha ao criar agendamento', 'error');
        });
      */

      // Simulação de sucesso
      this.schedules.push(schedule);
      this.toggleScheduleForm();
      this.showToast('Sucesso', 'Agendamento criado com sucesso', 'success');
    },
    toggleScheduleStatus(id) {
      const schedule = this.schedules.find(s => s.id === id);
      if (schedule) {
        schedule.active = !schedule.active;

        // Aqui você faria uma requisição para atualizar o status
        /*
        axios.put(`/api/schedules/${id}/status`, { active: schedule.active })
          .then(() => {
            this.showToast('Status Alterado', `Agendamento ${schedule.active ? 'ativado' : 'desativado'}`, 'info');
          })
          .catch(error => {
            console.error('Erro ao atualizar status:', error);
            schedule.active = !schedule.active; // Reverte se falhar
          });
        */

        this.showToast('Status Alterado', `Agendamento ${schedule.active ? 'ativado' : 'desativado'}`, 'info');
      }
    },
    duplicateSchedule(id) {
      const schedule = this.schedules.find(s => s.id === id);
      if (schedule) {
        const newSchedule = {
          ...schedule,
          id: this.nextId++,
          title: schedule.title + ' (Cópia)',
          active: false
        };

        // Aqui você faria uma requisição para salvar a cópia
        /*
        axios.post('/api/schedules', newSchedule)
          .then(response => {
            this.schedules.push(response.data);
            this.showToast('Agendamento Duplicado', 'Cópia criada com sucesso', 'success');
          })
          .catch(error => {
            console.error('Erro ao duplicar agendamento:', error);
          });
        */

        // Simulação de sucesso
        this.schedules.push(newSchedule);
        this.showToast('Agendamento Duplicado', 'Cópia criada com sucesso', 'success');
      }
    },
    editSchedule(id) {
      this.showToast('Em Desenvolvimento', 'Funcionalidade de edição será implementada em breve', 'info');
    },
    deleteSchedule(id) {
      const schedule = this.schedules.find(s => s.id === id);
      if (schedule) {
        this.showConfirmModal(
          `Tem certeza que deseja excluir o agendamento "${schedule.title}"?`,
          () => {
            // Aqui você faria uma requisição para excluir
            /*
            axios.delete(`/api/schedules/${id}`)
              .then(() => {
                this.schedules = this.schedules.filter(s => s.id !== id);
                this.showToast('Agendamento Removido', 'O agendamento foi excluído com sucesso', 'success');
              })
              .catch(error => {
                console.error('Erro ao excluir agendamento:', error);
              });
            */

            // Simulação de sucesso
            this.schedules = this.schedules.filter(s => s.id !== id);
            this.showToast('Agendamento Removido', 'O agendamento foi excluído com sucesso', 'success');
          }
        );
      }
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
    showToast(title, message, type) {
      // Implementação do toast pode ser feita com um componente Vue ou biblioteca como Toastification
      console.log(`[${type}] ${title}: ${message}`);
      // Exemplo com Toastification:
      // this.$toast[type](message, { title });
    }
  }
};
</script>
