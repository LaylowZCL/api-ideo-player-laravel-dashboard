<template>
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="h2 mb-1">Preview de Vídeos</h1>
        <p class="text-muted mb-0">Visualize e teste os vídeos antes da execução</p>
      </div>
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-secondary" @click="testPopup">
          <i class="bi bi-window me-1"></i>
          Testar Popup
        </button>
        <button class="btn btn-primary" @click="previewOnMonitor">
          <i class="bi bi-display me-1"></i>
          Preview no Monitor
        </button>
      </div>
    </div>

    <div class="row mt-4">
      <!-- Video Player -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Player de Vídeo</h5>
          </div>
          <div class="card-body">
            <div class="bg-dark rounded position-relative text-light" style="height: 400px;">
              <div v-if="!currentVideo" class="d-flex align-items-center justify-content-center h-100 text-muted">
                <div class="text-center">
                  <i class="bi bi-play-circle display-1 mb-3"></i>
                  <p class="mb-0">Selecione um vídeo para reproduzir</p>
                </div>
              </div>

                <div v-else class="h-100">
                    <video
                        ref="videoElement"
                        class="w-100 h-100"
                        style="object-fit: contain;"
                        controls
                        preload="metadata"
                    >
                        <source :src="currentVideo.url" :type="getVideoMimeType(currentVideo.name)">
                        Seu navegador não suporta o elemento de vídeo.
                        <p class="text-danger small mt-2">
                        Se o vídeo não carrega, verifique se a URL está correta: {{ currentVideo.url }}
                        </p>
                    </video>
                    <div class="position-absolute top-0 start-0 p-2 bg-dark bg-opacity-75 text-light small">
                        {{ currentVideo.title }}
                    </div>
                    <video @error="handleVideoError"></video>
                </div>
            </div>

            <!-- Video Controls -->
            <div class="d-flex align-items-center justify-content-between mt-3 p-3 bg-dark bg-opacity-10 rounded">
              <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary btn-sm" @click="togglePlayPause" :disabled="!currentVideo">
                  <i :class="isPlaying ? 'bi bi-pause' : 'bi bi-play'"></i>
                </button>
                <button class="btn btn-outline-secondary btn-sm" @click="stopVideo" :disabled="!currentVideo">
                  <i class="bi bi-stop"></i>
                </button>
                <span class="small text-muted">{{ currentTimeFormatted }} / {{ currentVideo?.duration ?? '00:00' }}</span>
              </div>
              <div class="d-flex align-items-center gap-3">
                <span class="small text-muted">Volume:</span>
                <input
                  type="range"
                  class="form-range"
                  style="width: 100px;"
                  min="0"
                  max="100"
                  v-model="volume"
                  @input="setVolume"
                />
                <button class="btn btn-outline-secondary btn-sm" @click="toggleFullscreen">
                  <i class="bi bi-fullscreen"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Video List -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Vídeos Disponíveis</h5>
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <div
                v-for="video in cachedVideos"
                :key="video.id"
                class="list-group-item list-group-item-action video-list-item"
                :class="{ active: currentVideo?.id === video.id }"
                @click="selectVideo(video)"
              >
                <div class="d-flex align-items-center gap-3">
                  <div class="video-thumbnail flex-shrink-0">
                    <i class="bi bi-camera-video fs-5 text-muted"></i>
                  </div>
                  <div class="flex-fill">
                    <h6 class="mb-1">{{ video.title }}</h6>
                    <p class="mb-1 small text-muted">{{ video.name }}</p>
                    <div class="d-flex justify-content-between small text-muted">
                      <span><i class="bi bi-clock me-1"></i>{{ video.duration }}</span>
                      <span>{{ formatFileSize(video.size) }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="cachedVideos.length === 0" class="list-group-item text-center text-muted py-4">
                <i class="bi bi-camera-video-off display-4 mb-3"></i>
                <p class="mb-0">Nenhum vídeo em cache</p>
                <small>Sincronize vídeos na seção "Vídeos"</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Monitor Config -->
        <div class="card mt-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Configuração de Monitor</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label for="monitor-select" class="form-label">Monitor de Destino</label>
              <select class="form-select" id="monitor-select" v-model="monitor">
                <option value="primary">Monitor Principal</option>
                <option value="secondary">Monitor Secundário</option>
                <option value="all">Todos os Monitores</option>
              </select>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small">Largura</label>
                <input type="number" class="form-control form-control-sm" v-model.number="popupWidth" />
              </div>
              <div class="col-6">
                <label class="form-label small">Altura</label>
                <input type="number" class="form-control form-control-sm" v-model.number="popupHeight" />
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <label class="form-label mb-0">Sempre no Topo</label>
              <input class="form-check-input" type="checkbox" v-model="alwaysOnTop" />
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <label class="form-label mb-0">Auto-fechar</label>
              <input class="form-check-input" type="checkbox" v-model="autoClose" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PreviewPage',
  data() {
    return {
      videos: [],
      currentVideo: null,
      isPlaying: false,
      volume: 50,
      currentTime: 0,
      monitor: 'primary',
      popupWidth: 800,
      popupHeight: 600,
      alwaysOnTop: true,
      autoClose: false
    }
  },
  computed: {
    cachedVideos() {
      return this.videos.filter(v => v.cached && v.url);
    },
    currentTimeFormatted() {
      const minutes = Math.floor(this.currentTime / 60);
      const seconds = Math.floor(this.currentTime % 60);
      return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
  },
  async created() {
    await this.loadVideos();
  },
  beforeUnmount() {
    this.clearUpdateInterval();
  },
  methods: {
    handleVideoError() {
        console.error('Erro no elemento de vídeo:', this.$refs.videoElement.error);
        this.showToast('Erro', 'Falha ao carregar o vídeo', 'error');
    },
    toggleFullscreen() {
      const video = this.$refs.videoElement;
      if (!video) return;

      if (document.fullscreenElement) {
        document.exitFullscreen();
      } else {
        video.requestFullscreen().catch(err => {
          console.error('Erro ao entrar em tela cheia:', err);
        });
      }
    },
    testPopup() {
      if (!this.currentVideo) {
        this.showToast('Aviso', 'Selecione um vídeo primeiro', 'warning');
        return;
      }

      const popupFeatures = `
        width=${this.popupWidth},
        height=${this.popupHeight},
        top=${(window.screen.height - this.popupHeight) / 2},
        left=${(window.screen.width - this.popupWidth) / 2},
        ${this.alwaysOnTop ? 'alwaysOnTop=yes,' : ''}
        toolbar=no,
        menubar=no,
        scrollbars=no,
        resizable=yes,
        location=no,
        status=no
      `;

      const popup = window.open(this.currentVideo.url, 'videoPreview', popupFeatures);

      if (!popup) {
        this.showToast('Erro', 'O popup foi bloqueado pelo navegador', 'error');
      }
    },
    async previewOnMonitor() {
      if (!this.currentVideo) {
        this.showToast('Aviso', 'Selecione um vídeo primeiro', 'warning');
        return;
      }

      try {
        const response = await axios.post('/api/videos/preview', {
          video_id: this.currentVideo.id,
          monitor: this.monitor,
          width: this.popupWidth,
          height: this.popupHeight,
          always_on_top: this.alwaysOnTop,
          auto_close: this.autoClose
        });

        this.showToast('Sucesso', response.data.message, 'success');
      } catch (error) {
        console.error('Erro ao enviar para o monitor:', error);
        this.showToast('Erro', 'Falha ao exibir no monitor', 'error');
      }
    },
    formatFileSize(bytes) {
      if (typeof bytes !== 'number') return '0 MB';

      if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
      } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
      } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
      }
      return bytes + ' bytes';
    },
    showToast(title, message, type) {
      // Implemente seu sistema de toast aqui
      console.log(`[${type}] ${title}: ${message}`);
    },
    clearUpdateInterval() {
      const videoElement = this.$refs.videoElement;
      if (videoElement) {
        videoElement.removeEventListener('timeupdate', this.updateCurrentTime);
        videoElement.removeEventListener('play', () => this.isPlaying = true);
        videoElement.removeEventListener('pause', () => this.isPlaying = false);
        videoElement.removeEventListener('ended', () => {
          this.isPlaying = false;
          if (this.autoClose) {
            this.stopVideo();
          }
        });
      }
    },
    async loadVideos() {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await axios.get('/api/videos');

        // Verifica se a resposta tem a estrutura esperada
        if (!response.data || !Array.isArray(response.data.videos)) {
          throw new Error('Formato de resposta inválido da API');
        }

        this.videos = response.data.videos.map(video => ({
          ...video,
          // Garante que todos os vídeos tenham a propriedade 'cached'
          cached: video.cached !== undefined ? video.cached : true
        }));

        console.log('Vídeos carregados:', this.videos);

      } catch (error) {
        console.error('Erro ao carregar vídeos:', error);
        this.error = 'Falha ao carregar vídeos. Tente recarregar a página.';
        this.showToast('Erro', this.error, 'error');
      } finally {
        this.isLoading = false;
      }
    },

    selectVideo(video) {
      if (!video.url) {
        console.error('Vídeo sem URL:', video);
        this.showToast('Erro', 'Este vídeo não possui uma URL válida', 'error');
        return;
      }

      this.currentVideo = video;
      this.isPlaying = false;

      this.$nextTick(() => {
        const videoElement = this.$refs.videoElement;
        if (videoElement) {
          // Força recarregar o vídeo
          videoElement.load();
          videoElement.volume = this.volume / 100;

          // Atualiza o tempo atual
          videoElement.addEventListener('timeupdate', this.updateCurrentTime);

          // Atualiza o estado de reprodução
          videoElement.addEventListener('play', () => {
            this.isPlaying = true;
          });

          videoElement.addEventListener('pause', () => {
            this.isPlaying = false;
          });

          videoElement.addEventListener('ended', () => {
            this.isPlaying = false;
            if (this.autoClose) {
              this.stopVideo();
            }
          });
        }
      });
    },

    updateCurrentTime() {
      const videoElement = this.$refs.videoElement;
      if (videoElement) {
        this.currentTime = videoElement.currentTime;
      }
    },

    togglePlayPause() {
      const video = this.$refs.videoElement;
      if (!video) return;

      if (this.isPlaying) {
        video.pause();
      } else {
        video.play().catch(error => {
          console.error('Erro ao reproduzir vídeo:', error);
          this.showToast('Erro', 'Não foi possível reproduzir o vídeo', 'error');
        });
      }
    },

    stopVideo() {
      const video = this.$refs.videoElement;
      if (video) {
        video.pause();
        video.currentTime = 0;
        this.isPlaying = false;
      }
    },

    setVolume() {
      const video = this.$refs.videoElement;
      if (video) {
        video.volume = this.volume / 100;
      }
    },

    getVideoMimeType(filename) {
      const extension = filename.split('.').pop().toLowerCase();
      switch(extension) {
        case 'mp4': return 'video/mp4';
        case 'webm': return 'video/webm';
        case 'ogg': return 'video/ogg';
        case 'mov': return 'video/quicktime';
        default: return 'video/mp4';
      }
    }
  }
}
</script>

<style scoped>
.video-list-item {
  cursor: pointer;
  transition: background-color 0.2s;
}

.video-list-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.video-list-item.active {
  background-color: rgba(13, 110, 253, 0.1) !important;
  border-left: 3px solid var(--bs-primary);
}

.video-thumbnail {
  width: 60px;
  height: 60px;
  background-color: #f8f9fa;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card {
  border-radius: 0.5rem;
  overflow: hidden;
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.bg-dark {
  background-color: #212529 !important;
}

.progress {
  height: 6px;
  background-color: #e9ecef;
}

.progress-bar {
  background-color: var(--bs-primary);
  transition: width 0.3s ease;
}

.form-range::-webkit-slider-thumb {
    background: var(--bs-primary);
}

.form-range::-moz-range-thumb {
    background: var(--bs-primary);
}

.form-range::-ms-thumb {
    background: var(--bs-primary);
}
</style>
