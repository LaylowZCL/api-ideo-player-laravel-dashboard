<template>

    <div id="dashboard-section">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">Dashboard</h1>
                    <p class="text-muted mb-0">Monitoramento e controle do VideoScheduler</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2 px-3 py-1 bg-success bg-opacity-10 text-success rounded-pill">
                        <i class="bi bi-wifi"></i>
                        <span class="small fw-medium">Online</span>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshDashboard()">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Atualizar
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="small text-muted mb-1">Vídeos Agendados</p>
                                    <h3 class="h4 mb-1">12</h3>
                                    <p class="small text-muted mb-0">Próximos 7 dias</p>
                                </div>
                                <i class="bi-calendar3 fs-2 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="small text-muted mb-1">Vídeos em Cache</p>
                                    <h3 class="h4 mb-1">8</h3>
                                    <p class="small text-muted mb-0">2.4 GB total</p>
                                </div>
                                <i class="bi-camera-video fs-2 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="small text-muted mb-1">Execuções Hoje</p>
                                    <h3 class="h4 mb-1">3</h3>
                                    <p class="small text-muted mb-0">100% sucesso</p>
                                </div>
                                <i class="bi-play-circle fs-2 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="small text-muted mb-1">Próxima Execução</p>
                                    <h3 class="h4 mb-1">14:30</h3>
                                    <p class="small text-muted mb-0">Em 2h 15m</p>
                                </div>
                                <i class="bi-clock fs-2 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>

        </div>
<!--
        <div class="row g-4">
            Recent Activity --
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Atividade Recente</h5>
                    </div>
                    <div class="card-body">

                            <div class="activity-item mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="small text-muted font-monospace">13:45</div>
                                    <div class="flex-fill">
                                        <div class="small fw-medium">Vídeo executado com sucesso</div>
                                        <div class="small text-muted">promo_produto_a.mp4</div>
                                    </div>
                                    <span class="badge bg-success">
                                        Sucesso
                                    </span>
                                </div>
                            </div>

                            <div class="activity-item mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="small text-muted font-monospace">12:30</div>
                                    <div class="flex-fill">
                                        <div class="small fw-medium">Cache atualizado</div>
                                        <div class="small text-muted">3 vídeos baixados</div>
                                    </div>
                                    <span class="badge bg-info">
                                        Info
                                    </span>
                                </div>
                            </div>

                            <div class="activity-item mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="small text-muted font-monospace">09:15</div>
                                    <div class="flex-fill">
                                        <div class="small fw-medium">Vídeo executado com sucesso</div>
                                        <div class="small text-muted">tutorial_sistema.mp4</div>
                                    </div>
                                    <span class="badge bg-success">
                                        Sucesso
                                    </span>
                                </div>
                            </div>

                            <div class="activity-item mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="small text-muted font-monospace">08:00</div>
                                    <div class="flex-fill">
                                        <div class="small fw-medium">Sistema iniciado</div>
                                        <div class="small text-muted">Background service</div>
                                    </div>
                                    <span class="badge bg-info">
                                        Info
                                    </span>
                                </div>
                            </div>

                    </div>
                </div>
            </div>

             Upcoming Schedules
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Próximos Agendamentos</h5>
                    </div>
                    <div class="card-body">

                            <div class="activity-item mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-center" style="min-width: 48px;">
                                        <div class="schedule-time">14:30</div>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="fw-medium">Demonstração Produto</div>
                                        <div class="d-flex align-items-center gap-3 small text-muted">
                                            <span>
                                                <i class="bi bi-clock me-1"></i>
                                                2:45
                                            </span>
                                            <span>
                                                <i class="bi bi-display me-1"></i>
                                                Principal
                                            </span>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="activity-item mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-center" style="min-width: 48px;">
                                        <div class="schedule-time">16:00</div>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="fw-medium">Treinamento Segurança</div>
                                        <div class="d-flex align-items-center gap-3 small text-muted">
                                            <span>
                                                <i class="bi bi-clock me-1"></i>
                                                5:20
                                            </span>
                                            <span>
                                                <i class="bi bi-display me-1"></i>
                                                Secundário
                                            </span>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="activity-item mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-center" style="min-width: 48px;">
                                        <div class="schedule-time">18:30</div>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="fw-medium">Avisos Importantes</div>
                                        <div class="d-flex align-items-center gap-3 small text-muted">
                                            <span>
                                                <i class="bi bi-clock me-1"></i>
                                                1:15
                                            </span>
                                            <span>
                                                <i class="bi bi-display me-1"></i>
                                                Principal
                                            </span>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                </div>
                            </div>

                    </div>
                </div>
            </div>
        </div>        -->
    </div>

</template>

<script>
export default {
    name: 'DashboardPage',
    data() {
        return {
            dashboardData: null,
            isLoading: false,
            error: null
        }
    },
    mounted() {
        console.log('Xetafaken DASHBOARD...')
    },
    methods: {
        async fetchDashboardData() {
            try {
                this.isLoading = true;
                const response = await api.get('/dashboard');
                this.dashboardData = response.data;
            } catch (error) {
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
