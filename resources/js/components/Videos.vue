<template>
    <div>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">Vídeos</h1>
                    <p class="text-muted mb-0">Gerencie os vídeos disponíveis no VideoScheduler</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-primary" @click="syncAll">
                        <i class="bi bi-arrow-repeat me-1"></i>
                        Sincronizar Todos
                    </button>
                </div>
            </div>
        </div>

        <!-- Videos List -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="border-bottom">
                            <tr>
                                <th class="border-0 ps-3">Nome</th>
                                <th class="border-0">Título</th>
                                <th class="border-0">Tamanho</th>
                                <th class="border-0">Duração</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 pe-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="video in videos" :key="video.id">
                                <td class="ps-3">{{ video.name }}</td>
                                <td>{{ video.title }}</td>
                                <td>{{ video.size }}</td>
                                <td>{{ video.duration }}</td>
                                <td>
                                    <span class="badge" :class="getStatusBadgeClass(video.status)">
                                        {{ video.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="pe-3">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" @click="syncVideo(video.id)" :disabled="video.status === 'downloading'">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" @click="previewVideo(video.url)">
                                            <i class="bi bi-play"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" @click="deleteFromCache(video.id)" :disabled="!video.cached">
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
    </div>
</template>

<script>
export default {
    data() {
        return {
            videos: []
        };
    },
    mounted() {
        this.fetchVideos();
    },
    methods: {
        fetchVideos() {
            axios.get('/api/videos')
                .then(response => {
                    this.videos = response.data;
                })
                .catch(error => {
                    this.showToast('Erro', 'Falha ao carregar vídeos', 'error');
                });
        },
        syncVideo(id) {
            axios.post(`/api/videos/${id}/sync`)
                .then(response => {
                    const video = this.videos.find(v => v.id === id);
                    Object.assign(video, response.data.video);
                    this.showToast('Sucesso', 'Vídeo sincronizado com sucesso', 'success');
                })
                .catch(error => {
                    this.showToast('Erro', 'Falha ao sincronizar vídeo', 'error');
                });
        },
        syncAll() {
            this.videos.forEach(video => {
                if (video.status !== 'synced' && video.status !== 'downloading') {
                    this.syncVideo(video.id);
                }
            });
        },
        deleteFromCache(id) {
            if (confirm('Tem certeza que deseja remover este vídeo do cache?')) {
                axios.post(`/api/videos/${id}/delete-cache`)
                    .then(response => {
                        const video = this.videos.find(v => v.id === id);
                        video.cached = false;
                        video.status = 'pending';
                        this.showToast('Sucesso', 'Vídeo removido do cache', 'success');
                    })
                    .catch(error => {
                        this.showToast('Erro', 'Falha ao remover vídeo do cache', 'error');
                    });
            }
        },
        previewVideo(url) {
            window.open(url, '_blank');
        },
        getStatusBadgeClass(status) {
            switch (status) {
                case 'synced': return 'bg-success';
                case 'downloading': return 'bg-info';
                case 'pending': return 'bg-warning';
                case 'error': return 'bg-danger';
                default: return 'bg-secondary';
            }
        },
        showToast(title, message, type) {
            alert(`${title}: ${message} (${type})`);
        }
    }
}
</script>
