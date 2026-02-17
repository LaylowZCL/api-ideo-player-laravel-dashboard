<template>
    <div id="dashboard-section">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">Dashboard</h1>
                    <p class="text-muted mb-0">
                        Última atualização: {{ lastUpdate || 'Carregando...' }}
                    </p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2 px-3 py-1 bg-success bg-opacity-10 text-success rounded-pill">
                        <i class="bi bi-wifi"></i>
                        <span class="small fw-medium">Online</span>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" @click="fetchDashboardData" :disabled="isLoading">
                        <i class="bi" :class="isLoading ? 'bi-arrow-clockwise spin' : 'bi-arrow-clockwise'"></i>
                        {{ isLoading ? 'Atualizando...' : 'Atualizar' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading && !hasData" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-3 text-muted">Carregando dados do dashboard...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ error }}
            <button class="btn btn-sm btn-outline-danger ms-3" @click="fetchDashboardData">
                Tentar novamente
            </button>
        </div>

        <!-- SEUS CARDS ORIGINAIS -->
        <div class="row g-3 mb-4" v-if="hasData">
            <div class="col-md-6 col-lg-3" v-for="stat in originalStats" :key="stat.title">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="small text-muted mb-1">{{ stat.title }}</p>
                                <h3 class="h4 mb-1">{{ stat.value }}</h3>
                                <p class="small text-muted mb-0">{{ stat.description }}</p>
                            </div>
                            <i :class="[stat.icon, 'fs-2', stat.color]"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NOVOS CARDS - Estatísticas de Visualização -->
        <div class="row g-3 mb-4" v-if="hasData">
            <div class="col-md-6 col-lg-3" v-for="stat in reportStats" :key="stat.title">
                <div class="card h-100 border-start border-3" :class="getBorderColor(stat.color)">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="small text-muted mb-1">{{ stat.title }}</p>
                                <h3 class="h4 mb-1">{{ stat.value }}</h3>
                                <p class="small text-muted mb-0">{{ stat.description }}</p>
                            </div>
                            <i :class="[stat.icon, 'fs-2', stat.color]"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de Estatísticas do Dia -->
        <div class="row g-3 mb-4" v-if="hasData">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-eye fs-1 text-primary mb-2"></i>
                        <h4 class="mb-1">{{ viewStats.today_views || 0 }}</h4>
                        <p class="small text-muted mb-0">Visualizações Hoje</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle fs-1 text-success mb-2"></i>
                        <h4 class="mb-1">{{ viewStats.today_completions || 0 }}</h4>
                        <p class="small text-muted mb-0">Concluídos Hoje</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history fs-1 text-info mb-2"></i>
                        <h4 class="mb-1">{{ viewStats.avg_duration || 0 }}s</h4>
                        <p class="small text-muted mb-0">Duração Média</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" v-if="viewStats.top_video">
                    <div class="card-body text-center">
                        <i class="bi bi-trophy fs-1 text-warning mb-2"></i>
                        <h6 class="mb-1 text-truncate" :title="viewStats.top_video.title">
                            {{ viewStats.top_video.title }}
                        </h6>
                        <p class="small text-muted mb-0">{{ viewStats.top_video.views }} visualizações</p>
                    </div>
                </div>
                <div class="card" v-else>
                    <div class="card-body text-center">
                        <i class="bi bi-trophy fs-1 text-warning mb-2"></i>
                        <h6 class="mb-1">Nenhum vídeo</h6>
                        <p class="small text-muted mb-0">Ainda não há dados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="row g-4" v-if="hasData">
            <!-- Gráfico de Visualizações -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Visualizações nos Últimos 7 Dias</h5>
                        <span class="badge bg-primary">{{ totalWeeklyViews }} visualizações</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-end justify-content-between h-100">
                            <div v-for="day in chartData.daily_views" :key="day.date" 
                                 class="text-center mx-1" style="flex: 1;">
                                <div class="small text-muted mb-1">{{ day.date }}</div>
                                <div class="bar-container" style="height: 120px;">
                                    <div class="bar bg-primary rounded-top" 
                                         :style="{ height: calculateBarHeight(day.views) + '%' }"
                                         :title="day.views + ' visualizações'">
                                    </div>
                                </div>
                                <div class="small mt-1 fw-medium">{{ day.views }}</div>
                            </div>
                        </div>
                        <div class="mt-3 small text-muted text-center">
                            {{ getWeekRange() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribuição por Plataforma -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Plataformas Mais Usadas</h5>
                    </div>
                    <div class="card-body">
                        <div v-for="platform in chartData.platform_distribution" :key="platform.platform" 
                             class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small">
                                    <i class="bi" :class="getPlatformIcon(platform.platform)"></i>
                                    {{ formatPlatformLabel(platform.platform) }}
                                </span>
                                <span class="badge bg-primary">{{ platform.count }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" 
                                     :style="{ width: calculatePercentage(platform.count) + '%' }">
                                </div>
                            </div>
                        </div>
                        <div v-if="chartData.platform_distribution.length === 0" class="text-center py-4 text-muted">
                            <i class="bi bi-laptop fs-1"></i>
                            <p class="mt-2 mb-0">Nenhum dado de plataforma disponível</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Atividade Recente -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Atividade Recente</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div v-for="log in recentLogs" :key="log.id" class="list-group-item border-0">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="small text-muted font-monospace">
                                        {{ formatTime(log.created_at) }}
                                    </div>
                                    <div class="flex-fill">
                                        <div class="small fw-medium">{{ log.action || 'Ação do Sistema' }}</div>
                                        <div class="small text-muted">{{ log.details || 'Sem detalhes' }}</div>
                                    </div>
                                    <span class="badge bg-success">
                                        Sucesso
                                    </span>
                                </div>
                            </div>
                            <div v-if="recentLogs.length === 0" class="list-group-item border-0 text-center py-4">
                                <i class="bi bi-journal-x text-muted fs-1"></i>
                                <p class="text-muted mt-2 mb-0">Nenhuma atividade recente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Próximos Agendamentos -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Próximos Agendamentos</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div v-for="schedule in upcomingSchedules" :key="schedule.id" class="list-group-item border-0">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-center" style="min-width: 48px;">
                                        <div class="schedule-time fw-bold">{{ schedule.time }}</div>
                                        <div class="small text-muted">{{ getTimeUntil(schedule.time) }}</div>
                                    </div>
                                    <div class="flex-fill">
                                        <div class="fw-medium">{{ schedule.title }}</div>
                                        <div class="d-flex align-items-center gap-3 small text-muted">
                                            <span v-if="schedule.video">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ schedule.video.duration || 'N/A' }}
                                            </span>
                                            <span>
                                                <i class="bi bi-display me-1"></i>
                                                {{ schedule.monitor }}
                                            </span>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                </div>
                            </div>
                            <div v-if="upcomingSchedules.length === 0" class="list-group-item border-0 text-center py-4">
                                <i class="bi bi-calendar-x text-muted fs-1"></i>
                                <p class="text-muted mt-2 mb-0">Nenhum agendamento próximo</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Relatórios Recentes de Vídeos -->
        <div class="row mt-4" v-if="hasData">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Relatórios Recentes de Vídeos</h5>
                        <span class="badge bg-info">{{ recentReports.length }} relatórios</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Vídeo</th>
                                        <th>Evento</th>
                                        <th>Plataforma</th>
                                        <th>Horário</th>
                                        <th>Duração</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(report, index) in paginatedRecentReports" :key="index">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-play-circle fs-5 text-primary"></i>
                                                <span class="fw-medium">{{ report.video_title }}</span>
                                            </div>
                                        </td>
                                        <td>{{ formatEventTypeLabel(report.event_type) }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ formatPlatformLabel(report.platform) }}</span>
                                        </td>
                                        <td>{{ report.viewed_at }}</td>
                                        <td>{{ report.duration }}s</td>
                                        <td>
                                            <span v-if="report.completed" class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Concluído
                                            </span>
                                            <span v-else class="badge bg-warning">
                                                <i class="bi bi-pause-circle me-1"></i>
                                                Parcial
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="recentReports.length === 0">
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-camera-video fs-1"></i>
                                            <p class="mt-2 mb-0">Nenhum relatório disponível</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="recentReports.length > reportsPerPage" class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                Mostrando {{ paginationStartItem }}-{{ paginationEndItem }} de {{ recentReports.length }}
                            </small>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Paginação dos relatórios">
                                <button class="btn btn-outline-secondary" @click="goToPreviousReportsPage" :disabled="reportsCurrentPage === 1">
                                    Anterior
                                </button>
                                <button
                                    v-for="page in reportsPageNumbers"
                                    :key="page"
                                    class="btn"
                                    :class="page === reportsCurrentPage ? 'btn-primary' : 'btn-outline-secondary'"
                                    @click="goToReportsPage(page)"
                                >
                                    {{ page }}
                                </button>
                                <button class="btn btn-outline-secondary disabled">
                                    de {{ reportsTotalPages }}
                                </button>
                                <button class="btn btn-outline-secondary" @click="goToNextReportsPage" :disabled="reportsCurrentPage === reportsTotalPages">
                                    Próxima
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DashboardPage',
    data() {
        return {
            // Seus dados originais
            originalStats: [],
            recentLogs: [],
            upcomingSchedules: [],
            
            // Novos dados
            reportStats: [],
            viewStats: {},
            chartData: {
                daily_views: [],
                platform_distribution: []
            },
            recentReports: [],
            reportsCurrentPage: 1,
            reportsPerPage: 10,
            
            // Estado
            isLoading: false,
            hasData: false,
            error: null,
            lastUpdate: null
        }
    },
    computed: {
        totalWeeklyViews() {
            if (!this.chartData.daily_views) return 0;
            return this.chartData.daily_views.reduce((total, day) => total + day.views, 0);
        },
        reportsTotalPages() {
            return Math.max(1, Math.ceil(this.recentReports.length / this.reportsPerPage));
        },
        reportsPageNumbers() {
            return Array.from({ length: this.reportsTotalPages }, (_, i) => i + 1);
        },
        paginatedRecentReports() {
            const start = (this.reportsCurrentPage - 1) * this.reportsPerPage;
            return this.recentReports.slice(start, start + this.reportsPerPage);
        },
        paginationStartItem() {
            return ((this.reportsCurrentPage - 1) * this.reportsPerPage) + 1;
        },
        paginationEndItem() {
            return Math.min(this.reportsCurrentPage * this.reportsPerPage, this.recentReports.length);
        }
    },
    watch: {
        recentReports() {
            this.reportsCurrentPage = 1;
        }
    },
    mounted() {
        //console.log('Dashboard montado - buscando dados...');
        this.fetchDashboardData();
        
        // Atualizar a cada 60 segundos
        this.interval = setInterval(() => {
            this.fetchDashboardData();
        }, 60000);
    },
    beforeUnmount() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    },
    methods: {
        async fetchDashboardData() {
            try {
                //console.log('OLA madafak')

                this.isLoading = true;
                this.error = null;
                
                // URL correta - ajuste conforme sua configuração
                const response = await this.$http.get('/api/dashboard/data');

                /*/ Adicione este header para identificar que é uma requisição do Vue
                const response = await this.$http.get('/api/dashboard/data', {
                    headers: {
                        'X-Request-Source': 'Vue-Component',
                        'Accept': 'application/json'
                    }
                });*/

                //console.log(response)
                
                if (response.data.success) {
                    // Seus dados originais
                    this.originalStats = response.data.stats || [];
                    this.recentLogs = response.data.recentLogs || [];
                    this.upcomingSchedules = response.data.upcomingSchedules || [];
                    
                    // Novos dados
                    this.reportStats = response.data.reportStats || [];
                    this.viewStats = response.data.viewStats || {};
                    this.chartData = response.data.chartData || {
                        daily_views: [],
                        platform_distribution: []
                    };
                    this.recentReports = response.data.recentReports || [];
                    
                    this.lastUpdate = response.data.timestamp || new Date().toLocaleTimeString();
                    this.hasData = true;
                    
                    //console.log('Dados do dashboard carregados com sucesso!');
                } else {
                    throw new Error(response.data.message || 'Erro ao carregar dados');
                }

            } catch (error) {
                //console.error('Erro ao carregar dashboard:', error);
                this.error = 'Não foi possível carregar os dados do dashboard. Verifique sua conexão.';
                this.hasData = false;
                
                // Dados de exemplo para desenvolvimento
                this.loadSampleData();
            } finally {
                this.isLoading = false;
            }
        },
        
        loadSampleData() {
            //console.log('Carregando dados de exemplo...');
            
            // Dados de exemplo (apenas para desenvolvimento)
            this.originalStats = [
                {
                    'title': 'Vídeos Agendados',
                    'value': '12',
                    'description': 'Próximos 7 dias',
                    'icon': 'bi-calendar3',
                    'color': 'text-info'
                },
                {
                    'title': 'Vídeos em Cache',
                    'value': '8',
                    'description': '2.4 GB total',
                    'icon': 'bi-camera-video',
                    'color': 'text-success'
                },
                {
                    'title': 'Vídeos Disponíveis',
                    'value': '24',
                    'description': 'Na API externa',
                    'icon': 'bi-play-circle',
                    'color': 'text-primary'
                },
                {
                    'title': 'Próxima Execução',
                    'value': '14:30',
                    'description': 'Em breve',
                    'icon': 'bi-clock',
                    'color': 'text-warning'
                }
            ];
            
            this.reportStats = [
                {
                    'title': 'Total de Visualizações',
                    'value': '156',
                    'description': 'Desde o início',
                    'icon': 'bi-eye',
                    'color': 'text-primary'
                },
                {
                    'title': 'Vídeos Concluídos',
                    'value': '89',
                    'description': 'Assistidos até o fim',
                    'icon': 'bi-check-circle',
                    'color': 'text-success'
                },
                {
                    'title': 'Sessões Ativas',
                    'value': '42',
                    'description': 'Sessões únicas',
                    'icon': 'bi-people',
                    'color': 'text-info'
                },
                {
                    'title': 'Taxa de Conclusão',
                    'value': '57%',
                    'description': 'De visualizações',
                    'icon': 'bi-graph-up',
                    'color': 'text-warning'
                }
            ];
            
            this.viewStats = {
                today_views: 12,
                today_completions: 8,
                avg_duration: 142.5,
                top_video: {
                    title: 'Alongamento Corporal Completo',
                    views: 45
                }
            };
            
            this.chartData.daily_views = [
                { date: 'Seg', full_date: '09/12', views: 8 },
                { date: 'Ter', full_date: '10/12', views: 12 },
                { date: 'Qua', full_date: '11/12', views: 15 },
                { date: 'Qui', full_date: '12/12', views: 10 },
                { date: 'Sex', full_date: '13/12', views: 18 },
                { date: 'Sáb', full_date: '14/12', views: 5 },
                { date: 'Dom', full_date: '15/12', views: 2 }
            ];
            
            this.chartData.platform_distribution = [
                { platform: 'Windows', count: 85 },
                { platform: 'macOS', count: 42 },
                { platform: 'Linux', count: 18 },
                { platform: 'Android', count: 11 }
            ];
            
            this.recentLogs = [
                {
                    id: 1,
                    action: 'Vídeo executado com sucesso',
                    details: 'promo_produto_a.mp4',
                    created_at: new Date().toISOString(),
                    status: 'success'
                },
                {
                    id: 2,
                    action: 'Cache atualizado',
                    details: '3 vídeos baixados',
                    created_at: new Date(Date.now() - 3600000).toISOString(),
                    status: 'success'
                }
            ];
            
            this.upcomingSchedules = [
                {
                    id: 1,
                    title: 'Demonstração Produto',
                    time: '14:30',
                    monitor: 'Principal',
                    video: { duration: '2:45' }
                },
                {
                    id: 2,
                    title: 'Treinamento Segurança',
                    time: '16:00',
                    monitor: 'Secundário',
                    video: { duration: '5:20' }
                }
            ];
            
            this.recentReports = [
                {
                    video_title: 'Alongamento Corporal Completo',
                    event_type: 'Vídeo Concluído',
                    platform: 'Windows',
                    viewed_at: '14:30',
                    duration: 182.3,
                    completed: true
                },
                {
                    video_title: 'Treinamento de Segurança',
                    event_type: 'Reprodução Iniciada',
                    platform: 'macOS',
                    viewed_at: '13:45',
                    duration: 45.2,
                    completed: false
                }
            ];
            
            this.lastUpdate = new Date().toLocaleTimeString();
            this.hasData = true;
        },
        goToPreviousReportsPage() {
            if (this.reportsCurrentPage > 1) {
                this.reportsCurrentPage--;
            }
        },
        goToReportsPage(page) {
            if (page >= 1 && page <= this.reportsTotalPages) {
                this.reportsCurrentPage = page;
            }
        },
        goToNextReportsPage() {
            if (this.reportsCurrentPage < this.reportsTotalPages) {
                this.reportsCurrentPage++;
            }
        },
        
        formatTime(dateTime) {
            if (!dateTime) return '';
            try {
                const date = new Date(dateTime);
                return date.toLocaleTimeString('pt-BR', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            } catch (e) {
                return '--:--';
            }
        },
        
        getBorderColor(colorClass) {
            const colorMap = {
                'text-primary': 'border-primary',
                'text-success': 'border-success',
                'text-info': 'border-info',
                'text-warning': 'border-warning'
            };
            return colorMap[colorClass] || 'border-secondary';
        },
        
        calculateBarHeight(views) {
            if (!this.chartData.daily_views || this.chartData.daily_views.length === 0) return 0;
            const maxViews = Math.max(...this.chartData.daily_views.map(d => d.views));
            if (maxViews === 0) return 0;
            return (views / maxViews) * 100;
        },
        
        calculatePercentage(count) {
            if (!this.chartData.platform_distribution || this.chartData.platform_distribution.length === 0) return 0;
            const total = this.chartData.platform_distribution.reduce((sum, p) => sum + p.count, 0);
            if (total === 0) return 0;
            return (count / total) * 100;
        },
        
        getWeekRange() {
            if (this.chartData.daily_views && this.chartData.daily_views.length > 1) {
                const first = this.chartData.daily_views[0];
                const last = this.chartData.daily_views[this.chartData.daily_views.length - 1];
                return `${first.full_date} - ${last.full_date}`;
            }
            return '';
        },
        
        getPlatformIcon(platform) {
            const normalized = this.normalizePlatform(platform);
            const icons = {
                windows: 'bi-windows',
                macos: 'bi-apple',
                linux: 'bi-ubuntu',
                android: 'bi-android',
                ios: 'bi-phone'
            };
            return icons[normalized] || 'bi-laptop';
        },

        formatPlatformLabel(platform) {
            const normalized = this.normalizePlatform(platform);
            const labels = {
                windows: 'Windows',
                macos: 'macOS',
                linux: 'Linux',
                android: 'Android',
                ios: 'iOS',
                unknown: 'Desconhecido'
            };

            return labels[normalized] || platform || 'Desconhecido';
        },

        normalizePlatform(platform) {
            const value = String(platform || '').toLowerCase();

            if (value.includes('win32') || value.includes('win64') || value.includes('windows')) return 'windows';
            if (value.includes('darwin') || value.includes('mac') || value.includes('osx') || value.includes('macos')) return 'macos';
            if (value.includes('linux') || value.includes('ubuntu') || value.includes('debian') || value.includes('fedora')) return 'linux';
            if (value.includes('android')) return 'android';
            if (value.includes('ios') || value.includes('iphone') || value.includes('ipad')) return 'ios';
            if (!value || value === 'desconhecido' || value === 'unknown') return 'unknown';

            return value;
        },
        
        formatEventTypeLabel(eventType) {
            if (!eventType) return 'Evento';

            const labels = {
                popup_opened: 'Popup aberto',
                playback_started: 'Reprodução iniciada',
                playback_paused: 'Reprodução pausada',
                playback_resumed: 'Reprodução retomada',
                playback_completed: 'Reprodução concluída',
                playback_error: 'Erro na reprodução',
                video_loaded: 'Vídeo carregado',
                video_completed: 'Vídeo concluído',
                video_interrupted: 'Vídeo interrompido',
                user_closed: 'Fechado pelo usuário',
                window_loaded: 'Janela carregada',
                autoplay_blocked: 'Reprodução automática bloqueada',
                popup_minimized: 'Popup minimizado'
            };

            if (labels[eventType]) {
                return labels[eventType];
            }

            if (!eventType.includes('_')) {
                return eventType;
            }

            const normalized = eventType.replace(/_/g, ' ');
            return normalized.charAt(0).toUpperCase() + normalized.slice(1);
        },
        
        getTimeUntil(time) {
            // Implementação simples - pode ser aprimorada
            return 'em breve';
        }
    }
}
</script>

<style scoped>
.schedule-time {
    font-size: 1.1rem;
    font-weight: 600;
    color: #ba9a6a;
}

.list-group-item {
    padding: 1rem;
    transition: background-color 0.2s;
}

.list-group-item:hover {
background-color: rgba(0,0,0,0.02);
}

.bar-container {
position: relative;
width: 100%;
min-height: 120px;
}

.bar {
position: absolute;
bottom: 0;
left: 10%;
right: 10%;
transition: height 0.3s ease;
background: linear-gradient(to top, #ba9a6a, #fdaa2e);
}

.bar:hover {
opacity: 0.8;
transform: scale(1.05);
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

.table th {
border-top: none;
border-bottom: 2px solid #dee2e6;
}

.table td {
vertical-align: middle;
}

.progress {
border-radius: 4px;
overflow: hidden;
background-color: #e9ecef;
}

.progress-bar {
background: linear-gradient(to right, #ba9a6a, #fdaa2e);
transition: width 1s ease-in-out;
}

.spin {
animation: spin 1s linear infinite;
}

@keyframes spin {
from { transform: rotate(0deg); }
to { transform: rotate(360deg); }
}

/* Animações para barras */
.bar {
animation: growBar 0.8s ease-out;
}

@keyframes growBar {
from { height: 0%; }
to { height: var(--final-height); }
}

/* Responsividade */
@media (max-width: 768px) {
.schedule-time {
font-size: 0.9rem;
}

.bar-container {
    height: 80px;
}

.card-body {
    padding: 1rem;
}
}

.border-start {
border-left-width: 3px !important;
}

/* Badge personalizado */
.badge {
font-weight: 500;
letter-spacing: 0.3px;
}
</style>
