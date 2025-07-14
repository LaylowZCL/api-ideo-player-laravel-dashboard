<template>

    <!-- Cabeçalho -->
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="h2 mb-1">Gerenciamento de Vídeos</h1>
          <p class="text-muted mb-0">Controle do cache local e sincronização com a API</p>
        </div>
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-outline-secondary" @click="refreshVideos" :disabled="isRefreshing">
            <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin': isRefreshing }"></i>
            <span>{{ isRefreshing ? 'Sincronizando...' : 'Sincronizar' }}</span>
          </button>
          <button class="btn btn-primary" @click="showUploadModal">
            <i class="bi bi-upload me-1"></i>
            Upload Manual
          </button>
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="small text-muted mb-1">Vídeos em Cache</p>
                <h3 class="h4 mb-0">{{ stats.cached_videos }}/{{ stats.total_videos }}</h3>
              </div>
              <i class="bi bi-hdd fs-2 text-success"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="small text-muted mb-1">Espaço Utilizado</p>
                <h3 class="h4 mb-0">{{ stats.total_size }}</h3>
              </div>
              <i class="bi bi-camera-video fs-2 text-info"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="small text-muted mb-1">Status da API</p>
                <h3 class="h4 mb-0">Online</h3>
              </div>
              <i class="bi bi-wifi fs-2 text-success"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Busca -->
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="position-relative">
          <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
          <input
            type="text"
            class="form-control ps-5"
            placeholder="Buscar vídeos..."
            v-model="searchTerm"
            @input="filterVideos"
          >
        </div>
      </div>
    </div>

    <!-- Lista de Vídeos -->
    <div id="videos-list">
      <div
        class="card mb-3 video-item"
        v-for="video in filteredVideos"
        :key="video.id"
        :data-video-name="video.name.toLowerCase()"
        :data-video-title="video.title.toLowerCase()"
      >
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <!-- Thumbnail -->
            <div class="video-thumbnail">
              <i class="bi bi-camera-video fs-4 text-muted"></i>
            </div>

            <!-- Informações do Vídeo -->
            <div class="flex-fill">
              <div class="d-flex align-items-center gap-3 mb-1">
                <h6 class="mb-0">{{ video.title }}</h6>
                <span class="badge" :class="getStatusBadgeClass(video.status)">
                  {{ getStatusText(video.status) }}
                </span>
              </div>
              <p class="small text-muted mb-2">{{ video.name }}</p>
              <div class="d-flex align-items-center gap-4 small text-muted">
                <span>
                  <i class="bi bi-clock me-1"></i>
                  {{ video.duration }}
                </span>
                <span>{{ video.size }}</span>
                <span v-if="video.lastSync">Sincronizado: {{ video.lastSync }}</span>
              </div>
              <div class="mt-2" v-if="video.status === 'downloading'">
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar" style="width: 45%"></div>
                </div>
                <small class="text-muted">Baixando... 45%</small>
              </div>
            </div>

            <!-- Ações -->
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-outline-secondary btn-sm" @click="previewVideo(video.id)">
                <i class="bi bi-play"></i>
              </button>

              <button
                v-if="video.cached"
                class="btn btn-outline-danger btn-sm"
                @click="deleteVideoFromCache(video.id)"
              >
                <i class="bi bi-trash"></i>
              </button>

              <button
                v-else
                class="btn btn-outline-secondary btn-sm"
                @click="downloadVideo(video.id)"
                :disabled="video.status === 'downloading'"
              >
                <i class="bi bi-download"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Upload -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
          <div class="modal-header border-secondary">
            <h5 class="modal-title">Upload de Vídeo</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="uploadVideo">
              <div class="mb-3">
                <label for="videoTitle" class="form-label">Título</label>
                <input type="text" class="form-control" id="videoTitle" v-model="uploadData.title" required>
              </div>
              <div class="mb-3">
                <label for="videoFile" class="form-label">Arquivo de Vídeo</label>
                <input type="file" class="form-control" id="videoFile" @change="handleFileUpload" accept="video/*" required>
              </div>
              <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" :disabled="isUploading">
                  <span v-if="isUploading">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Enviando...
                  </span>
                  <span v-else>Enviar</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
  data() {
    return {
      videos: [],
      filteredVideos: [],
      stats: {
        total_videos: 0,
        cached_videos: 0,
        total_size: '0 GB',
        api_status: 'online'
      },
      searchTerm: '',
      isRefreshing: false,
      uploadModal: null,
      uploadData: {
        title: '',
        file: null
      },
      isUploading: false
    };
  },
  mounted() {
    this.uploadModal = new Modal(document.getElementById('uploadModal'));
    this.loadVideos();
  },
  methods: {
    async loadVideos() {
      try {
        const response = await axios.get('/api/videos');
        this.videos = response.data.videos;
        this.filteredVideos = [...this.videos];
        this.stats = response.data.stats;
      } catch (error) {
        console.error('Erro ao carregar vídeos:', error);
        this.showToast('Erro', 'Falha ao carregar vídeos', 'error');
      }
    },
    async refreshVideos() {
      this.isRefreshing = true;
      try {
        const response = await axios.post('/api/videos/sync');
        this.showToast('Sucesso', response.data.message, 'success');
        await this.loadVideos();
      } catch (error) {
        console.error('Erro ao sincronizar vídeos:', error);
        this.showToast('Erro', 'Falha ao sincronizar vídeos', 'error');
      } finally {
        this.isRefreshing = false;
      }
    },
    filterVideos() {
      if (!this.searchTerm) {
        this.filteredVideos = [...this.videos];
        return;
      }

      const term = this.searchTerm.toLowerCase();
      this.filteredVideos = this.videos.filter(video =>
        video.name.toLowerCase().includes(term) ||
        video.title.toLowerCase().includes(term)
      );
    },
    getStatusBadgeClass(status) {
      switch (status) {
        case 'synced': return 'bg-success';
        case 'pending': return 'bg-warning';
        case 'error': return 'bg-danger';
        case 'downloading': return 'bg-info';
        default: return 'bg-secondary';
      }
    },
    getStatusText(status) {
      switch (status) {
        case 'synced': return 'Sincronizado';
        case 'pending': return 'Pendente';
        case 'error': return 'Erro';
        case 'downloading': return 'Baixando';
        default: return 'Desconhecido';
      }
    },
    async downloadVideo(id) {
      try {
        const video = this.videos.find(v => v.id === id);
        if (video) {
          video.status = 'downloading';

          const response = await axios.post(`/api/videos/${id}/download`);
          this.showToast('Sucesso', response.data.message, 'success');
          await this.loadVideos();
        }
      } catch (error) {
        console.error('Erro ao baixar vídeo:', error);
        this.showToast('Erro', 'Falha ao baixar vídeo', 'error');
      }
    },
    deleteVideoFromCache(id) {
      this.showConfirmModal(
        'Tem certeza que deseja remover este vídeo do cache?',
        async () => {
          try {
            await axios.delete(`/api/videos/${id}/cache`);
            this.showToast('Sucesso', 'Vídeo removido do cache', 'success');
            await this.loadVideos();
          } catch (error) {
            console.error('Erro ao remover vídeo:', error);
            this.showToast('Erro', 'Falha ao remover vídeo', 'error');
          }
        }
      );
    },
    previewVideo(id) {
      const video = this.videos.find(v => v.id === id);
      if (video) {
        this.showToast('Preview', `Reproduzindo: ${video.title}`, 'info');
        // Implementar a lógica de preview aqui
      }
    },
    showUploadModal() {
      this.uploadData = { title: '', file: null };
      this.uploadModal.show();
    },
    handleFileUpload(event) {
      this.uploadData.file = event.target.files[0];
    },
    async uploadVideo() {
      if (!this.uploadData.file || !this.uploadData.title) {
        this.showToast('Erro', 'Preencha todos os campos', 'error');
        return;
      }

      this.isUploading = true;

      try {
        const formData = new FormData();
        formData.append('title', this.uploadData.title);
        formData.append('video', this.uploadData.file);

        const response = await axios.post('/api/videos/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        this.showToast('Sucesso', response.data.message, 'success');
        this.uploadModal.hide();
        await this.loadVideos();
      } catch (error) {
        console.error('Erro ao enviar vídeo:', error);
        this.showToast('Erro', 'Falha ao enviar vídeo', 'error');
      } finally {
        this.isUploading = false;
      }
    },
    showConfirmModal(message, callback) {
      // Implementação do modal de confirmação
      if (confirm(message)) {
        callback();
      }
    },
    showToast(title, message, type) {
      // Implementação do toast (pode usar uma biblioteca como Toastification)
      console.log(`[${type}] ${title}: ${message}`);
    }
  }
};
</script>
