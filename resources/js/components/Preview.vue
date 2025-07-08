<template>
    <div>
        <div class="mb-4">
            <h1 class="h2 mb-1">Pré-visualização</h1>
            <p class="text-muted mb-0">Visualize um vídeo antes de agendá-lo</p>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="video-url" class="form-label">URL do Vídeo</label>
                    <input type="url" class="form-control" v-model="videoUrl" placeholder="Digite a URL do vídeo">
                </div>
                <button class="btn btn-primary" @click="loadPreview">
                    <i class="bi bi-play me-1"></i>
                    Carregar Visualização
                </button>
                <div class="mt-4" v-if="videoUrl">
                    <video controls class="w-100" :src="videoUrl"></video>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            videoUrl: ''
        };
    },
    methods: {
        loadPreview() {
            if (!this.videoUrl) {
                this.showToast('Erro', 'Por favor, insira uma URL válida', 'error');
                return;
            }
            this.showToast('Sucesso', 'Pré-visualização carregada', 'success');
        },
        showToast(title, message, type) {
            alert(`${title}: ${message} (${type})`);
        }
    }
}
</script>
