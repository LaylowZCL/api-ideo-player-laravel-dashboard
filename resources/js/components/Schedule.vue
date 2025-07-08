<template>
    <div>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">Agendamentos</h1>
                    <p class="text-muted mb-0">Gerencie os agendamentos de vídeos</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-primary" @click="showCreateModal">
                        <i class="bi bi-plus-circle me-1"></i>
                        Novo Agendamento
                    </button>
                </div>
            </div>
        </div>

        <!-- Schedules List -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="border-bottom">
                            <tr>
                                <th class="border-0 ps-3">Título</th>
                                <th class="border-0">Vídeo</th>
                                <th class="border-0">Horário</th>
                                <th class="border-0">Dias</th>
                                <th class="border-0">Monitor</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 pe-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="schedule in schedules" :key="schedule.id">
                                <td class="ps-3">{{ schedule.title }}</td>
                                <td>{{ schedule.video }}</td>
                                <td>{{ schedule.time }}</td>
                                <td>{{ schedule.days.join(', ') }}</td>
                                <td>{{ schedule.monitor }}</td>
                                <td>
                                    <span class="badge" :class="schedule.active ? 'bg-success' : 'bg-secondary'">
                                        {{ schedule.active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="pe-3">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" @click="toggleStatus(schedule.id)">
                                            <i :class="schedule.active ? 'bi bi-pause' : 'bi bi-play'"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" @click="duplicateSchedule(schedule.id)">
                                            <i class="bi bi-files"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" @click="deleteSchedule(schedule.id)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Schedule Modal -->
        <div class="modal fade" id="createScheduleModal" tabindex="-1" aria-labelledby="createScheduleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createScheduleModalLabel">Novo Agendamento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="createSchedule">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título</label>
                                <input type="text" class="form-control" v-model="newSchedule.title" required>
                            </div>
                            <div class="mb-3">
                                <label for="video" class="form-label">Vídeo</label>
                                <input type="text" class="form-control" v-model="newSchedule.video" required>
                            </div>
                            <div class="mb-3">
                                <label for="time" class="form-label">Horário</label>
                                <input type="time" class="form-control" v-model="newSchedule.time" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dias</label>
                                <div class="form-check" v-for="day in days" :key="day">
                                    <input class="form-check-input" type="checkbox" :value="day" v-model="newSchedule.days">
                                    <label class="form-check-label">{{ day }}</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="monitor" class="form-label">Monitor</label>
                                <select class="form-select" v-model="newSchedule.monitor" required>
                                    <option value="Principal">Principal</option>
                                    <option value="Secundário">Secundário</option>
                                    <option value="Todos">Todos</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duração</label>
                                <input type="text" class="form-control" v-model="newSchedule.duration" required>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="newSchedule.active">
                                <label class="form-check-label">Ativo</label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" @click="createSchedule">Salvar</button>
                    </div>
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
            schedules: [],
            newSchedule: {
                title: '',
                video: '',
                time: '',
                days: [],
                monitor: 'Principal',
                active: true,
                duration: ''
            },
            days: ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom']
        };
    },
    mounted() {
        this.fetchSchedules();
    },
    methods: {
        fetchSchedules() {
            axios.get('/api/schedules')
                .then(response => {
                    this.schedules = response.data;
                })
                .catch(error => {
                    this.showToast('Erro', 'Falha ao carregar agendamentos', 'error');
                });
        },
        showCreateModal() {
            const modal = new Modal(document.getElementById('createScheduleModal'));
            modal.show();
        },
        createSchedule() {
            axios.post('/api/schedules', this.newSchedule)
                .then(response => {
                    this.schedules.push(response.data.schedule);
                    this.resetNewSchedule();
                    document.getElementById('createScheduleModal')._modal.hide();
                    this.showToast('Sucesso', 'Agendamento criado com sucesso', 'success');
                })
                .catch(error => {
                    this.showToast('Erro', error.response.data.errors || 'Falha ao criar agendamento', 'error');
                });
        },
        toggleStatus(id) {
            axios.post(`/api/schedules/${id}/toggle`)
                .then(response => {
                    const schedule = this.schedules.find(s => s.id === id);
                    schedule.active = response.data.active;
                    this.showToast('Sucesso', 'Status alterado com sucesso', 'success');
                })
                .catch(error => {
                    this.showToast('Erro', 'Falha ao alterar status', 'error');
                });
        },
        duplicateSchedule(id) {
            axios.post(`/api/schedules/${id}/duplicate`)
                .then(response => {
                    this.schedules.push(response.data.schedule);
                    this.showToast('Sucesso', 'Agendamento duplicado com sucesso', 'success');
                })
                .catch(error => {
                    this.showToast('Erro', 'Falha ao duplicar agendamento', 'error');
                });
        },
        deleteSchedule(id) {
            if (confirm('Tem certeza que deseja excluir este agendamento?')) {
                axios.delete(`/api/schedules/${id}`)
                    .then(() => {
                        this.schedules = this.schedules.filter(s => s.id !== id);
                        this.showToast('Sucesso', 'Agendamento removido com sucesso', 'success');
                    })
                    .catch(error => {
                        this.showToast('Erro', 'Falha ao remover agendamento', 'error');
                    });
            }
        },
        resetNewSchedule() {
            this.newSchedule = {
                title: '',
                video: '',
                time: '',
                days: [],
                monitor: 'Principal',
                active: true,
                duration: ''
            };
        },
        showToast(title, message, type) {
            alert(`${title}: ${message} (${type})`);
        }
    }
}
</script>
