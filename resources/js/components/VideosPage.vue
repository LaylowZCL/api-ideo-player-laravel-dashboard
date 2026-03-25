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
          <span>{{ isRefreshing ? 'Sincronizando...' : 'Sincronizar API' }}</span>
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
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="small text-muted mb-1">Total de Vídeos</p>
              <h3 class="h4 mb-0">{{ stats.total_videos }}</h3>
            </div>
            <i class="bi bi-camera-video fs-2 text-primary"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="small text-muted mb-1">Em Cache</p>
              <h3 class="h4 mb-0">{{ stats.cached_videos }}</h3>
            </div>
            <i class="bi bi-hdd fs-2 text-success"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="small text-muted mb-1">Espaço Utilizado</p>
              <h3 class="h4 mb-0">{{ stats.total_size }}</h3>
            </div>
            <i class="bi bi-hdd-stack fs-2 text-info"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="small text-muted mb-1">Status da API</p>
              <h3 class="h4 mb-0" :class="stats.api_status === 'online' ? 'text-success' : 'text-danger'">
                {{ stats.api_status === 'online' ? 'Online' : 'Offline' }}
              </h3>
            </div>
            <i class="bi bi-wifi fs-2" :class="stats.api_status === 'online' ? 'text-success' : 'text-danger'"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Busca e Filtros -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="position-relative">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
        <input
          type="text"
          class="form-control ps-5"
          placeholder="Buscar por título ou nome..."
          v-model="searchTerm"
          @input="filterVideos"
        >
      </div>
    </div>
    <div class="col-md-3">
      <select class="form-select" v-model="statusFilter" @change="filterVideos">
        <option value="">Todos os status</option>
        <option value="cached">Em cache</option>
        <option value="available">Disponível</option>
        <option value="downloading">Baixando</option>
        <option value="error">Erro</option>
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-outline-secondary w-100" @click="clearFilters">
        <i class="bi bi-x-circle me-1"></i>
        Limpar Filtros
      </button>
    </div>
  </div>

  <!-- Tamanho e Localização do Popup -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-0">Tamanho e Localização do Popup</h5>
            <small class="text-muted">Configuração aplicada aos clientes.</small>
          </div>
          <button class="btn btn-primary btn-sm" @click="savePopupSettings" :disabled="isSavingPopup">
            {{ isSavingPopup ? 'Salvando...' : 'Aplicar' }}
          </button>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small text-muted">Resolução</label>
              <select class="form-select" v-model="popupSettings.preset" @change="handlePopupPresetChange">
                <option value="custom">Personalizado</option>
                <option value="256x144">144p • 256×144 (Internet muito lenta)</option>
                <option value="426x240">240p • 426×240 (Telemóveis antigos)</option>
                <option value="320x240">240p • 320×240 (Telemóveis antigos)</option>
                <option value="640x360">360p • 640×360 (YouTube antigo)</option>
                <option value="640x480">SD • 640×480</option>
                <option value="720x480">SD • 720×480</option>
                <option value="854x480">480p • 854×480 (DVD / transição)</option>
                <option value="854x480">480p • 854×480 (Básico)</option>
                <option value="1280x720">720p • 1280×720 (HD leve)</option>
                <option value="1920x1080">1080p • 1920×1080 (Padrão atual)</option>
                <option value="2560x1440">1440p • 2560×1440 (Gaming / monitores)</option>
                <option value="3840x2160">4K • 3840×2160 (Alta qualidade)</option>
                <option value="7680x4320">8K • 7680×4320 (Futuro)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small text-muted">Dimensão (px)</label>
              <input type="text" class="form-control" :value="`${popupSettings.width} × ${popupSettings.height}`" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label small text-muted">Posição</label>
              <select class="form-select" v-model="popupSettings.position">
                <option value="center">Centro</option>
                <option value="top_left">Superior esquerdo</option>
                <option value="top_right">Superior direito</option>
                <option value="bottom_left">Inferior esquerdo</option>
                <option value="bottom_right">Inferior direito</option>
              </select>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">{{ popupSaveStatus }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Lista de Vídeos -->
  <div id="videos-list">
    <div v-if="loading" class="text-center py-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
      <p class="text-muted mt-2">Carregando vídeos...</p>
    </div>

    <div v-else-if="filteredVideos.length === 0" class="text-center py-5">
      <i class="bi bi-camera-video-off display-4 text-muted mb-3"></i>
      <h5 class="text-muted">Nenhum vídeo encontrado</h5>
      <p class="text-muted">Tente ajustar os filtros ou sincronizar com a API.</p>
    </div>

    <div
      class="card mb-3 video-item"
      v-for="video in filteredVideos"
      :key="video.id"
    >
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          <!-- Thumbnail -->
          <div class="video-thumbnail position-relative">
            <i class="bi bi-camera-video fs-4 text-muted"></i>
            <div v-if="video.thumbnail_url" class="thumbnail-image">
              <img :src="video.thumbnail_url" :alt="video.title" class="img-fluid rounded">
            </div>
            <div v-if="video.status === 'downloading'" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 rounded">
              <div class="spinner-border spinner-border-sm text-white" role="status"></div>
            </div>
          </div>

          <!-- Informações do Vídeo -->
          <div class="flex-fill">
            <div class="d-flex align-items-center gap-3 mb-1">
              <h6 class="mb-0">{{ video.title }}</h6>
              <span class="badge" :class="getStatusBadgeClass(video)">
                {{ getStatusText(video) }}
              </span>
              <span v-if="video.is_active === false" class="badge bg-secondary">Inativo</span>
            </div>
            
            <p class="small text-muted mb-1" v-if="video.description">{{ video.description }}</p>
            <p class="small text-muted mb-2">{{ video.name }}</p>
            
            <div class="d-flex align-items-center gap-4 small text-muted">
              <span>
                <i class="bi bi-clock me-1"></i>
                {{ video.duration || 'N/A' }}
              </span>
              <span>
                <i class="bi bi-file-earmark me-1"></i>
                {{ video.size }}
              </span>
              <span v-if="video.lastSync">
                <i class="bi bi-arrow-repeat me-1"></i>
                {{ video.lastSync }}
              </span>
              <span v-if="video.api_id">
                <i class="bi bi-cloud me-1"></i>
                ID: {{ video.api_id }}
              </span>
            </div>

            <!-- Progresso de Download -->
            <div class="mt-2" v-if="video.status === 'downloading'">
              <div class="progress" style="height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 45%"></div>
              </div>
              <small class="text-muted">Baixando... 45%</small>
            </div>
          </div>

          <!-- Ações -->
          <div class="d-flex align-items-center gap-2">
            <button 
              class="btn btn-outline-primary btn-sm" 
              @click="previewVideo(video)"
              :disabled="!video.cached && !video.url"
              :title="!video.cached && !video.url ? 'Vídeo não disponível para preview' : 'Preview do vídeo'"
            >
              <i class="bi bi-play"></i>
            </button>

            <button
              v-if="video.cached"
              class="btn btn-outline-danger btn-sm"
              @click="deleteVideo(video)"
              title="Remover do cache local"
            >
              <i class="bi bi-trash"></i>
            </button>

            <button
              v-else-if="video.status !== 'downloading'"
              class="btn btn-outline-success btn-sm"
              @click="downloadVideo(video.id)"
              :disabled="!video.url"
              :title="!video.url ? 'Vídeo não disponível para download' : 'Baixar para cache local'"
            >
              <i class="bi bi-download"></i>
            </button>

            <button
              v-else
              class="btn btn-outline-secondary btn-sm"
              disabled
            >
              <i class="bi bi-hourglass-split"></i>
            </button>

            <button
              class="btn btn-outline-warning btn-sm"
              @click="viewVideoDetails(video)"
              title="Ver detalhes"
            >
              <i class="bi bi-info-circle"></i>
            </button>
            <button
              class="btn btn-outline-light btn-sm"
              @click="openEditVideoModal(video)"
              title="Editar vídeo"
            >
              <i class="bi bi-pencil"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Upload -->
  <div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content bg-dark text-light">
        <div class="modal-header border-secondary">
          <h5 class="modal-title">Upload de Vídeo para API</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" @click="closeUploadModal"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="uploadVideo">
            <div class="row g-3">
              <div class="col-md-12">
                <label for="videoTitle" class="form-label">Título do Vídeo *</label>
                <input type="text" class="form-control" id="videoTitle" v-model="uploadData.title" required>
              </div>
              
              <div class="col-md-12">
                <label for="videoDescription" class="form-label">Descrição</label>
                <textarea class="form-control" id="videoDescription" v-model="uploadData.description" rows="3" placeholder="Descrição opcional do vídeo..."></textarea>
              </div>
              
              <div class="col-md-12">
                <label for="videoFile" class="form-label">Arquivo de Vídeo *</label>
                <input type="file" class="form-control" id="videoFile" @change="handleFileUpload" accept="video/mp4,video/avi,video/mov,video/wmv,video/webm,video/x-matroska" required>
                <div class="form-text">
                  Formatos suportados: MP4, AVI, MOV, WMV. Tamanho máximo: 100MB
                </div>
              </div>

              <div class="col-md-12">
                <label for="subtitleFiles" class="form-label">Legendas (.srt)</label>
                <input type="file" class="form-control" id="subtitleFiles" @change="handleSubtitleUpload" accept=".srt" multiple>
                <div class="form-text">
                  Pode selecionar múltiplas legendas para o vídeo.
                </div>
              </div>

              <div class="col-md-12" v-if="uploadData.file">
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>
                  Arquivo selecionado: <strong>{{ uploadData.file.name }}</strong>
                  ({{ formatFileSize(uploadData.file.size) }})<br>
                  <span v-if="isReadingDuration">Lendo duração do vídeo...</span>
                  <span v-else>Duração detectada: <strong>{{ uploadData.duration || 'Não identificada' }}</strong></span>
                </div>
              </div>

              <div class="col-md-12" v-if="uploadData.subtitles && uploadData.subtitles.length">
                <div class="alert alert-secondary">
                  <i class="bi bi-subtitles me-2"></i>
                  Legendas selecionadas:
                  <ul class="mb-0 mt-2">
                    <li v-for="(sub, index) in uploadData.subtitles" :key="index">
                      {{ sub.name }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-4">
              <button type="button" class="btn btn-secondary" @click="closeUploadModal">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="isUploading || !uploadData.file || isReadingDuration || !uploadData.durationSeconds">
                <span v-if="isUploading">
                  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  Enviando...
                </span>
                <span v-else>
                  <i class="bi bi-upload me-1"></i>
                  Enviar para API
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Detalhes -->
  <div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content bg-dark text-light">
        <div class="modal-header border-secondary">
          <h5 class="modal-title">Detalhes do Vídeo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div v-if="selectedVideo" class="row g-3">
            <div class="col-md-4">
              <div class="text-center">
                <div class="video-thumbnail-large mb-3">
                  <i v-if="!selectedVideo.thumbnail_url" class="bi bi-camera-video fs-1 text-muted"></i>
                  <img v-else :src="selectedVideo.thumbnail_url" :alt="selectedVideo.title" class="img-fluid rounded">
                </div>
              </div>
            </div>
            <div class="col-md-8">
              <h6>{{ selectedVideo.title }}</h6>
              <p class="text-muted small mb-3" v-if="selectedVideo.description">{{ selectedVideo.description }}</p>
              
              <div class="row small text-muted">
                <div class="col-6 mb-2">
                  <strong>Nome do arquivo:</strong><br>
                  {{ selectedVideo.name }}
                </div>
                <div class="col-6 mb-2">
                  <strong>Duração:</strong><br>
                  {{ selectedVideo.duration || 'N/A' }}
                </div>
                <div class="col-6 mb-2">
                  <strong>Tamanho:</strong><br>
                  {{ selectedVideo.size }}
                </div>
                <div class="col-6 mb-2">
                  <strong>Status:</strong><br>
                  <span :class="'badge ' + getStatusBadgeClass(selectedVideo)">
                    {{ getStatusText(selectedVideo) }}
                  </span>
                </div>
                <div class="col-6 mb-2">
                  <strong>ID da API:</strong><br>
                  {{ selectedVideo.api_id || 'N/A' }}
                </div>
                <div class="col-6 mb-2">
                  <strong>Última sincronização:</strong><br>
                  {{ selectedVideo.lastSync || 'Nunca' }}
                </div>
                <div class="col-12 mb-2">
                  <strong>URL:</strong><br>
                  <a :href="selectedVideo.url" target="_blank" class="text-info small text-break">
                    {{ selectedVideo.url || 'N/A' }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Edição -->
  <div class="modal fade" id="editVideoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content bg-dark text-light">
        <div class="modal-header border-secondary">
          <h5 class="modal-title">Editar Vídeo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" @click="closeEditVideoModal"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveVideoEdit">
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label">Título *</label>
                <input type="text" class="form-control" v-model="editVideoData.title" required>
              </div>
              <div class="col-md-12">
                <label class="form-label">Descrição</label>
                <textarea class="form-control" rows="3" v-model="editVideoData.description"></textarea>
              </div>
              <div class="col-md-12">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="video-active" v-model="editVideoData.is_active">
                  <label class="form-check-label" for="video-active">Vídeo ativo</label>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-4">
              <button type="button" class="btn btn-secondary" @click="closeEditVideoModal">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="isSavingVideoEdit">
                <span v-if="isSavingVideoEdit" class="spinner-border spinner-border-sm me-1"></span>
                Salvar alterações
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Preview -->
  <div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content bg-dark text-light">
        <div class="modal-header border-secondary">
          <h5 class="modal-title">Preview do Vídeo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" @click="closePreviewModal"></button>
        </div>
        <div class="modal-body">
          <div v-if="previewVideoSource" class="ratio ratio-16x9">
            <video ref="previewPlayer" controls autoplay :src="previewVideoSource"></video>
          </div>
          <div v-else class="text-center py-4 text-muted">
            URL do vídeo indisponível para preview.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
name: 'VideosPage',
data() {
  return {
    videos: [],
    filteredVideos: [],
    stats: {
      total_videos: 0,
      cached_videos: 0,
      total_size: '0 GB',
      api_status: 'offline'
    },
    searchTerm: '',
    statusFilter: '',
    loading: false,
    isRefreshing: false,
    uploadModal: null,
    detailsModal: null,
    editVideoModal: null,
    previewModal: null,
    uploadData: {
      title: '',
      description: '',
      file: null,
      subtitles: [],
      duration: '',
      durationSeconds: null
    },
    isUploading: false,
    isReadingDuration: false,
    isSavingVideoEdit: false,
    selectedVideo: null,
    previewVideoSource: null,
    editVideoData: {
      id: null,
      title: '',
      description: '',
      is_active: true
    },
    popupSettings: {
      width: 960,
      height: 540,
      position: 'center',
      preset: 'custom'
    },
    systemSettingsPayload: null,
    popupSaveStatus: 'Carregando configurações...',
    isSavingPopup: false
  };
},
mounted() {
  this.uploadModal = new Modal(document.getElementById('uploadModal'));
  this.detailsModal = new Modal(document.getElementById('detailsModal'));
  this.editVideoModal = new Modal(document.getElementById('editVideoModal'));
  this.previewModal = new Modal(document.getElementById('previewModal'));
  this.loadVideos();
  this.loadPopupSettings();
},
  methods: {
  handlePopupPresetChange() {
    if (this.popupSettings.preset === 'custom') {
      return;
    }
    const [width, height] = this.popupSettings.preset.split('x').map(Number);
    if (Number.isFinite(width) && Number.isFinite(height)) {
      this.popupSettings.width = width;
      this.popupSettings.height = height;
    }
  },
  async loadVideos() {
    this.loading = true;
    try {
      const response = await axios.get('/api/videos');
      this.videos = response.data.videos;
      this.filteredVideos = [...this.videos];
      this.stats = response.data.stats;
    } catch (error) {
      console.error('Erro ao carregar vídeos:', error);
      this.showToast('Erro', 'Falha ao carregar vídeos da API', 'error');
    } finally {
      this.loading = false;
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
      this.showToast('Erro', 'Falha ao sincronizar com a API', 'error');
    } finally {
      this.isRefreshing = false;
    }
  },
  async loadPopupSettings() {
    try {
      const response = await axios.get('/api/system-settings');
      if (response.data && response.data.success) {
        this.systemSettingsPayload = response.data.settings;
        this.popupSettings = {
          width: response.data.settings.popupWidth || 960,
          height: response.data.settings.popupHeight || 540,
          position: response.data.settings.popupPosition || 'center',
          preset: this.matchResolutionPreset(
            response.data.settings.popupWidth || 960,
            response.data.settings.popupHeight || 540
          )
        };
        this.popupSaveStatus = 'Configurações carregadas.';
      } else {
        this.popupSaveStatus = 'Não foi possível carregar as configurações.';
      }
    } catch (error) {
      this.popupSaveStatus = 'Erro ao carregar configurações.';
    }
  },
  async savePopupSettings() {
    if (!this.systemSettingsPayload) {
      this.popupSaveStatus = 'Configurações indisponíveis.';
      return;
    }

    this.isSavingPopup = true;
    try {
      const payload = {
        ...this.systemSettingsPayload,
        popupWidth: this.popupSettings.width,
        popupHeight: this.popupSettings.height,
        popupPosition: this.popupSettings.position
      };
      const response = await axios.post('/api/system-settings', payload);
      if (response.data && response.data.success) {
        this.systemSettingsPayload = response.data.settings;
        this.popupSaveStatus = 'Configurações aplicadas com sucesso.';
      } else {
        this.popupSaveStatus = 'Falha ao salvar configurações.';
      }
    } catch (error) {
      this.popupSaveStatus = 'Erro ao salvar configurações.';
    } finally {
      this.isSavingPopup = false;
    }
  },
  matchResolutionPreset(width, height) {
    const key = `${width}x${height}`;
    const allowed = [
      '256x144',
      '426x240',
      '320x240',
      '640x360',
      '640x480',
      '720x480',
      '854x480',
      '1280x720',
      '1920x1080',
      '2560x1440',
      '3840x2160',
      '7680x4320'
    ];
    return allowed.includes(key) ? key : 'custom';
  },

  filterVideos() {
    let filtered = this.videos;

    // Filtro por busca
    if (this.searchTerm) {
      const term = this.searchTerm.toLowerCase();
      filtered = filtered.filter(video =>
        video.name.toLowerCase().includes(term) ||
        video.title.toLowerCase().includes(term) ||
        (video.description && video.description.toLowerCase().includes(term))
      );
    }

    // Filtro por status
    if (this.statusFilter) {
      filtered = filtered.filter(video => {
        switch (this.statusFilter) {
          case 'cached': return video.cached;
          case 'available': return !video.cached && video.url;
          case 'downloading': return video.status === 'downloading';
          case 'error': return video.status === 'error';
          default: return true;
        }
      });
    }

    this.filteredVideos = filtered;
  },

  clearFilters() {
    this.searchTerm = '';
    this.statusFilter = '';
    this.filteredVideos = [...this.videos];
  },

  getStatusBadgeClass(video) {
    if (video.status === 'downloading') return 'bg-info';
    if (video.status === 'error') return 'bg-danger';
    if (video.cached) return 'bg-success';
    if (video.url) return 'bg-warning';
    return 'bg-secondary';
  },

  getStatusText(video) {
    if (video.status === 'downloading') return 'Baixando';
    if (video.status === 'error') return 'Erro';
    if (video.cached) return 'Em cache';
    if (video.url) return 'Disponível';
    return 'Indisponível';
  },

  async downloadVideo(id) {
    try {
      const video = this.videos.find(v => v.id === id);
      console.log(video)
      if (video) {
        video.status = 'downloading';
        
        /*const response = await axios.post(`/api/videos/${id}/download`);
        this.showToast('Sucesso', response.data.message, 'success');
        await this.loadVideos();*/

        // Construa o link de download
        const downloadLink = `${video.url}`;
            
        // Inicie o download
        window.open(downloadLink, '_blank'); // Abre o link em uma nova aba
        video.status = 'available'; // Atualiza o status para indicar que o download foi iniciado

        this.showToast('Sucesso', 'Download iniciado!', 'success');
        await this.loadVideos(); // Recarregue a lista de vídeos, se necessário
      }
    } catch (error) {
      console.error('Erro ao baixar vídeo:', error);
      const video = this.videos.find(v => v.id === id);
      if (video) video.status = 'error';
      this.showToast('Erro', 'Falha ao baixar vídeo', 'error');
    }
  },

  deleteVideo(video) {
    const isLocalVideo = String(video.api_id || '').startsWith('local_');
    const message = isLocalVideo
      ? 'Tem certeza que deseja excluir este vídeo local? Esta ação remove o ficheiro e o registo do sistema.'
      : 'Tem certeza que deseja remover este vídeo do cache local? O vídeo permanecerá disponível na API.';

    this.showConfirmModal(message, async () => {
      try {
        if (isLocalVideo) {
          await axios.delete(`/api/videos/${video.id}`);
          this.showToast('Sucesso', 'Vídeo excluído com sucesso', 'success');
        } else {
          await axios.delete(`/api/videos/${video.id}/cache`);
          this.showToast('Sucesso', 'Vídeo removido do cache local', 'success');
        }

        await this.loadVideos();
      } catch (error) {
        console.error('Erro ao remover vídeo:', error);
        this.showToast('Erro', error.response?.data?.message || 'Falha ao remover vídeo', 'error');
      }
    });
  },

  previewVideo(video) {
    if (!video.url) {
      this.showToast('Aviso', 'Vídeo não disponível para preview', 'warning');
      return;
    }

    this.previewVideoSource = video.url;
    this.previewModal.show();
  },

  closePreviewModal() {
    const player = this.$refs.previewPlayer;
    if (player) {
      player.pause();
      player.currentTime = 0;
    }
    this.previewModal.hide();
    this.previewVideoSource = null;
  },

  viewVideoDetails(video) {
    this.selectedVideo = video;
    this.detailsModal.show();
  },

  openEditVideoModal(video) {
    this.editVideoData = {
      id: video.id,
      title: video.title || '',
      description: video.description || '',
      is_active: video.is_active !== false
    };
    this.editVideoModal.show();
  },

  closeEditVideoModal() {
    this.editVideoModal.hide();
    this.editVideoData = {
      id: null,
      title: '',
      description: '',
      is_active: true
    };
    this.isSavingVideoEdit = false;
  },

  async saveVideoEdit() {
    if (!this.editVideoData.id || !this.editVideoData.title.trim()) {
      this.showToast('Erro', 'Informe o título do vídeo', 'error');
      return;
    }

    this.isSavingVideoEdit = true;

    try {
      const payload = {
        title: this.editVideoData.title.trim(),
        description: this.editVideoData.description || '',
        is_active: this.editVideoData.is_active
      };

      const response = await axios.put(`/api/videos/${this.editVideoData.id}`, payload);

      if (response.data.success) {
        this.showToast('Sucesso', response.data.message || 'Vídeo atualizado com sucesso', 'success');
        this.closeEditVideoModal();
        await this.loadVideos();
      } else {
        throw new Error(response.data.message || 'Falha ao atualizar vídeo');
      }
    } catch (error) {
      console.error('Erro ao atualizar vídeo:', error);
      this.showToast('Erro', error.response?.data?.message || 'Falha ao atualizar vídeo', 'error');
    } finally {
      this.isSavingVideoEdit = false;
    }
  },

  showUploadModal() {
    this.uploadData = { title: '', description: '', file: null, subtitles: [], duration: '', durationSeconds: null };
    this.isReadingDuration = false;
    this.uploadModal.show();
  },

  closeUploadModal() {
    this.uploadModal.hide();
    this.uploadData = { title: '', description: '', file: null, subtitles: [], duration: '', durationSeconds: null };
    this.isReadingDuration = false;
  },

  async handleFileUpload(event) {
    this.uploadData.file = event.target.files[0] || null;
    this.uploadData.duration = '';
    this.uploadData.durationSeconds = null;

    if (!this.uploadData.file) {
      return;
    }

    this.isReadingDuration = true;

    try {
      const durationSeconds = await this.extractVideoDuration(this.uploadData.file);
      this.uploadData.durationSeconds = durationSeconds;
      this.uploadData.duration = this.formatDuration(durationSeconds);
    } catch (error) {
      console.error('Erro ao ler duração do vídeo:', error);
      this.showToast('Erro', 'Não foi possível identificar a duração do vídeo selecionado', 'error');
    } finally {
      this.isReadingDuration = false;
    }
  },

  handleSubtitleUpload(event) {
    const files = Array.from(event.target.files || []);
    this.uploadData.subtitles = files.filter(file => file.name.toLowerCase().endsWith('.srt'));
  },

  async uploadVideo() {
    if (!this.uploadData.file || !this.uploadData.title) {
      this.showToast('Erro', 'Preencha todos os campos obrigatórios', 'error');
      return;
    }

    if (!this.uploadData.durationSeconds) {
      this.showToast('Erro', 'A duração do vídeo ainda não foi identificada', 'error');
      return;
    }

    // Validação do tamanho do arquivo (100MB)
    if (this.uploadData.file.size > 100 * 1024 * 1024) {
      this.showToast('Erro', 'O arquivo deve ter no máximo 100MB', 'error');
      return;
    }

    this.isUploading = true;

    try {
      const formData = new FormData();
      formData.append('title', this.uploadData.title);
      formData.append('description', this.uploadData.description || '');
      formData.append('video', this.uploadData.file);
      formData.append('duration_seconds', this.uploadData.durationSeconds);

      if (this.uploadData.subtitles && this.uploadData.subtitles.length) {
        this.uploadData.subtitles.forEach(file => {
          formData.append('subtitles[]', file);
        });
      }

      console.log(this.uploadData.file);

      const response = await axios.post('/api/videos/upload', formData, {
        // Deixe o browser/axios definir o boundary do multipart automaticamente
        // timeout: 60000 // 60 segundos para upload
      });

      this.showToast('Sucesso', response.data.message, 'success');
      this.closeUploadModal();
      await this.loadVideos(); // Recarrega a lista
    } catch (error) {
      console.error('Erro ao enviar vídeo:', error);
      let errorMessage = 'Falha ao enviar vídeo';
      if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error.code === 'ECONNABORTED') {
        errorMessage = 'Timeout - o upload está demorando muito';
      }
      this.showToast('Erro', errorMessage, 'error');
    } finally {
      this.isUploading = false;
    }
  },

  formatFileSize(bytes) {
    if (!bytes) return '0 B';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(2048));
    return Math.round(bytes / Math.pow(2048, i) * 100) / 100 + ' ' + sizes[i]; 
  },

  extractVideoDuration(file) {
    return new Promise((resolve, reject) => {
      const objectUrl = URL.createObjectURL(file);
      const videoElement = document.createElement('video');

      videoElement.preload = 'metadata';
      videoElement.onloadedmetadata = () => {
        const durationSeconds = Math.round(videoElement.duration);
        URL.revokeObjectURL(objectUrl);

        if (!Number.isFinite(durationSeconds) || durationSeconds <= 0) {
          reject(new Error('Duração inválida'));
          return;
        }

        resolve(durationSeconds);
      };

      videoElement.onerror = () => {
        URL.revokeObjectURL(objectUrl);
        reject(new Error('Falha ao ler metadata do vídeo'));
      };

      videoElement.src = objectUrl;
    });
  },

  formatDuration(totalSeconds) {
    const seconds = Number(totalSeconds);
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    if (hours > 0) {
      return `${hours}:${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
    }

    return `${minutes}:${String(remainingSeconds).padStart(2, '0')}`;
  },

  showConfirmModal(message, callback) {
    if (confirm(message)) {
      callback();
    }
  },

  showToast(title, message, type) {
    // Implementação básica - você pode integrar com uma biblioteca de toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '1060';
    
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          <strong>${title}:</strong> ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
      document.body.removeChild(toast);
    });
  }
}
};
</script>

<style scoped>
.video-thumbnail {
width: 80px;
height: 60px;
background-color: #f8f9fa;
border-radius: 4px;
display: flex;
align-items: center;
justify-content: center;
flex-shrink: 0;
overflow: hidden;
}

.thumbnail-image img {
width: 100%;
height: 100%;
object-fit: cover;
}

.video-thumbnail-large {
width: 200px;
height: 150px;
background-color: #f8f9fa;
border-radius: 8px;
display: flex;
align-items: center;
justify-content: center;
overflow: hidden;
}

.spin {
animation: spin 1s linear infinite;
}

@keyframes spin {
from { transform: rotate(0deg); }
to { transform: rotate(360deg); }
}

.progress-bar-striped {
background-image: linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);
background-size: 1rem 1rem;
}

.progress-bar-animated {
animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
0% { background-position: 1rem 0; }
100% { background-position: 0 0; }
}

.toast {
min-width: 300px;
}
</style>
