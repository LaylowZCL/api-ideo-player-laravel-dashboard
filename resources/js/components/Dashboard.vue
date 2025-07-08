
<template>
    <div>
        <div class="mb-4">
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Resumo geral do VideoScheduler</p>
        </div>

        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3" v-for="stat in stats" :key="stat.title">
                <div class="card">
                    <div class="card-body text-center">
                        <i :class="stat.icon + ' fs-3 mb-2 ' + stat.color"></i>
                        <h4 class="mb-1">{{ stat.value }}</h4>
                        <small class="text-muted">{{ stat.description }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Logs Recentes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="border-bottom">
                            <tr>
                                <th class="border-0 ps-3">Horário</th>
                                <th class="border-0">Status</th>
                                <th class="border-0">Evento</th>
                                <th class="border-0">Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in recentLogs" :key="log.id">
                                <td class="ps-3">
                                    <span class="font-monospace small">{{ log.time }}</span>
                                </td>
                                <td>
                                    <span class="badge" :class="getLogStatusBadgeClass(log.status)">
                                        {{ log.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td>{{ log.event }}</td>
                                <td class="text-muted">{{ log.video }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Upcoming Schedules -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Próximos Agendamentos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="border-bottom">
                            <tr>
                                <th class="border-0 ps-3">Título</th>
                                <th class="border-0">Vídeo</th>
                                <th class="border-0">Horário</th>
                                <th class="border-0">Dias</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="schedule in upcomingSchedules" :key="schedule.id">
                                <td class="ps-3">{{ schedule.title }}</td>
                                <td>{{ schedule.video }}</td>
                                <td>{{ schedule.time }}</td>
                                <td>{{ schedule.days.join(', ') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            stats: [],
            recentLogs: [],
            upcomingSchedules: []
        };
    },
    mounted() {
        this.fetchDashboardData();
    },
    methods: {
        fetchDashboardData() {
            axios.get('/api/dashboard')
                .then(response => {
                    this.stats = response.data.stats;
                    this.recentLogs = response.data.recentLogs;
                    this.upcomingSchedules = response.data.upcomingSchedules;
                })
                .catch(error => {
                    this.showToast('Erro', 'Falha ao carregar dados do dashboard', 'error');
                });
        },
        getLogStatusBadgeClass(status) {
            switch (status) {
                case 'success': return 'bg-success';
                case 'error': return 'bg-danger';
                case 'warning': return 'bg-warning';
                case 'info': return 'bg-info';
                default: return 'bg-secondary';
            }
        },
        showToast(title, message, type) {
            alert(`${title}: ${message} (${type})`);
        }
    }
}
</script>
