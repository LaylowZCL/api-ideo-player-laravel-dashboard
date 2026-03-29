<template>
  <div class="profile-page">
    <div class="mb-4">
      <h1 class="h2 mb-1">Minha Conta</h1>
      <p class="text-muted mb-0">Actualize os seus dados pessoais e a sua palavra-passe.</p>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Dados da conta</h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="saveProfile">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nome</label>
              <input v-model="form.name" type="text" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input v-model="form.email" type="email" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nome de utilizador</label>
              <input v-model="form.username" type="text" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Perfil</label>
              <input :value="form.role_name" type="text" class="form-control" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Palavra-passe actual</label>
              <input v-model="form.current_password" type="password" class="form-control" placeholder="Obrigatória se mudar a palavra-passe">
            </div>
            <div class="col-md-6">
              <label class="form-label">Nova palavra-passe</label>
              <input v-model="form.password" type="password" class="form-control" placeholder="Mínimo 8 caracteres">
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirmar nova palavra-passe</label>
              <input v-model="form.password_confirmation" type="password" class="form-control">
            </div>
          </div>
          <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit" :disabled="loading">
              {{ loading ? 'A guardar...' : 'Guardar alterações' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="feedback.message" class="alert mt-4" :class="feedback.success ? 'alert-success' : 'alert-danger'">
      {{ feedback.message }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProfilePage',
  data() {
    return {
      loading: false,
      feedback: { success: true, message: '' },
      form: {
        name: '',
        email: '',
        username: '',
        role_name: '',
        current_password: '',
        password: '',
        password_confirmation: ''
      }
    };
  },
  mounted() {
    this.loadProfile();
  },
  methods: {
    async loadProfile() {
      const response = await this.$http.get('/api/profile');
      if (response.data?.success) {
        this.form = {
          ...this.form,
          ...response.data.profile,
          current_password: '',
          password: '',
          password_confirmation: ''
        };
      }
    },
    async saveProfile() {
      this.loading = true;
      this.feedback.message = '';
      try {
        const response = await this.$http.put('/api/profile', this.form);
        this.feedback = {
          success: !!response.data?.success,
          message: response.data?.message || 'Dados actualizados com sucesso.'
        };
        if (response.data?.success) {
          await this.loadProfile();
        }
      } catch (error) {
        const firstError = error.response?.data?.errors
          ? Object.values(error.response.data.errors)[0][0]
          : null;
        this.feedback = {
          success: false,
          message: firstError || error.response?.data?.message || 'Não foi possível actualizar os seus dados.'
        };
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
