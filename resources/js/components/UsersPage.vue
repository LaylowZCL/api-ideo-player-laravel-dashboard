<template>
    <!-- Cabeçalho -->
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h1 class="page-title mb-1">Gestão de Usuários</h1>
          <p class="page-subtitle mb-0">Controle de acessos, permissões e perfis com rastreabilidade total</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" @click="openCreateUserModal" v-if="canCreateUser">
            <i class="bi bi-plus me-1"></i>
            Novo Usuário
          </button>
        </div>
      </div>
    </div>

    <!-- Barra de ferramentas -->
    <div class="card toolbar-card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-center">
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text bg-transparent border-0 text-muted">
                <i class="bi bi-search"></i>
              </span>
              <input type="text" class="form-control" placeholder="Pesquisar por nome, email ou perfil"
                     v-model="searchTerm">
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="roleFilter">
              <option value="">Todos os perfis</option>
              <option value="admin">Administradores</option>
              <option value="manager">Gestores</option>
              <option value="user">Usuários</option>
            </select>
          </div>
          <div class="col-md-4 d-flex gap-2">
            <select class="form-select" v-model="sortBy">
              <option value="name">Ordenar por Nome</option>
              <option value="created_at">Ordenar por Data</option>
              <option value="user_type">Ordenar por Perfil</option>
            </select>
            <button class="btn btn-outline-light" @click="toggleSortDirection">
              <i class="bi" :class="sortDirection === 'asc' ? 'bi-sort-down' : 'bi-sort-up'"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Usuário -->
    <div class="modal fade" id="userModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingUser ? 'Editar Usuário' : 'Criar Novo Usuário' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" @click="cancelForm"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="editingUser ? updateUser() : createUser()">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="user-name" class="form-label">Nome *</label>
              <input type="text" class="form-control" id="user-name" 
                     v-model="formData.name" 
                     placeholder="Nome completo" 
                     required>
            </div>
            <div class="col-md-6">
              <label for="user-email" class="form-label">Email *</label>
              <input type="email" class="form-control" id="user-email" 
                     v-model="formData.email" 
                     placeholder="email@exemplo.com" 
                     required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="user-password" class="form-label">
                {{ editingUser ? 'Nova Senha (opcional)' : 'Senha *' }}
              </label>
              <input type="password" class="form-control" id="user-password" 
                     v-model="formData.password" 
                     :required="!editingUser"
                     placeholder="Mínimo 8 caracteres">
            </div>
            <div class="col-md-6">
              <label for="user-password-confirm" class="form-label">Confirmar Senha</label>
              <input type="password" class="form-control" id="user-password-confirm" 
                     v-model="formData.password_confirmation" 
                     :required="!editingUser || formData.password"
                     placeholder="Digite novamente">
            </div>
          </div>

          <div class="mb-3" v-if="canChangeRole">
            <label for="user-type" class="form-label">Tipo de Usuário *</label>
            <select class="form-select" id="user-type" 
                    v-model="formData.user_type" 
                    :disabled="isCurrentUser"
                    required>
              <option value="user">Usuário</option>
              <option value="manager">Gestor</option>
              <option value="admin" v-if="currentUser.user_type === 'admin'">Administrador</option>
            </select>
            <div class="form-text small">
              <span v-if="isCurrentUser">
                <i class="bi bi-info-circle me-1"></i>
                Você não pode alterar seu próprio tipo
              </span>
              <span v-else-if="currentUser.user_type === 'manager' && editingUser?.user_type === 'admin'">
                <i class="bi bi-shield-lock me-1"></i>
                Gestores não podem editar administradores
              </span>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <div class="text-muted small">
              <i class="bi bi-info-circle me-1"></i>
              Senha deve ter no mínimo 8 caracteres
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary" @click="cancelForm">
                Cancelar
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                {{ editingUser ? 'Atualizar' : 'Criar' }}
              </button>
            </div>
          </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="row mb-4 g-3">
      <div class="col-md-3">
        <div class="stat-card stat-total">
          <div class="stat-label">Total Usuários</div>
          <div class="stat-value">{{ stats.total }}</div>
          <div class="stat-icon"><i class="bi bi-people"></i></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card stat-admins">
          <div class="stat-label">Administradores</div>
          <div class="stat-value">{{ stats.admins }}</div>
          <div class="stat-icon"><i class="bi bi-shield-lock"></i></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card stat-managers">
          <div class="stat-label">Gestores</div>
          <div class="stat-value">{{ stats.managers }}</div>
          <div class="stat-icon"><i class="bi bi-briefcase"></i></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card stat-users">
          <div class="stat-label">Usuários Comuns</div>
          <div class="stat-value">{{ stats.users }}</div>
          <div class="stat-icon"><i class="bi bi-person"></i></div>
        </div>
      </div>
    </div>

    <!-- Lista de Usuários -->
    <div id="users-list">
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-3 text-muted">Carregando usuários...</p>
      </div>

      <div v-else-if="filteredUsers.length === 0" class="text-center py-5">
        <i class="bi bi-people text-muted fs-1"></i>
        <h4 class="mt-3 text-muted">Nenhum usuário encontrado</h4>
        <p class="text-muted" v-if="canCreateUser">Clique em "Novo Usuário" para começar</p>
      </div>

      <div v-else>
        <div class="user-card" v-for="user in filteredUsers" :key="user.id">
          <div class="user-card-main">
            <div class="user-avatar-lg" :class="getAvatarClass(user.user_type)">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div class="user-meta">
              <div class="user-name">
                {{ user.name }}
                <span v-if="user.id === currentUser.id" class="chip chip-self">
                  <i class="bi bi-person-check me-1"></i>Você
                </span>
              </div>
              <div class="user-email">{{ user.email }}</div>
              <div class="user-tags">
                <span class="chip" :class="getRoleBadgeClass(user.user_type)">
                  {{ user.role_name }}
                </span>
                <span class="chip chip-muted">
                  <i class="bi bi-calendar3 me-1"></i> {{ user.created_at }}
                </span>
              </div>
            </div>
          </div>
          <div class="user-actions">
            <button class="btn btn-outline-light btn-sm" 
                    @click="editUser(user)"
                    :disabled="!canEditUser(user)"
                    :title="canEditUser(user) ? 'Editar' : 'Sem permissão para editar'">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-outline-danger btn-sm" 
                    v-if="user.id !== currentUser.id"
                    @click="deleteUser(user)"
                    :disabled="!canDeleteUser(user)"
                    :title="canDeleteUser(user) ? 'Excluir' : 'Sem permissão para excluir'">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmação</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>{{ confirmMessage }}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" @click="confirmAction" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast de Notificação -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
      <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" :class="toastClass">
          <i class="bi me-2" :class="toastIcon"></i>
          <strong class="me-auto">{{ toastTitle }}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
          {{ toastMessage }}
        </div>
      </div>
    </div>
</template>

<script>
import { Modal, Toast } from 'bootstrap';

export default {
  name: 'UsersPage',
  data() {
    return {
      editingUser: null,
      loading: false,
      users: [],
      searchTerm: '',
      roleFilter: '',
      sortBy: 'name',
      sortDirection: 'asc',
      currentUser: {
        id: null,
        user_type: 'user'
      },
      formData: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        user_type: 'user'
      },
      stats: {
        total: 0,
        admins: 0,
        managers: 0,
        users: 0
      },
      
      // Modal
      userModal: null,
      confirmMessage: '',
      confirmCallback: null,
      confirmModal: null,
      
      // Toast
      toast: null,
      toastTitle: '',
      toastMessage: '',
      toastClass: '',
      toastIcon: ''
    };
  },
  computed: {
    canCreateUser() {
      return this.currentUser.user_type === 'admin' || this.currentUser.user_type === 'manager';
    },
    
    canChangeRole() {
      return this.currentUser.user_type === 'admin' || this.currentUser.user_type === 'manager';
    },
    
    isCurrentUser() {
      return this.editingUser && this.editingUser.id === this.currentUser.id;
    },

    filteredUsers() {
      const term = this.searchTerm.trim().toLowerCase();
      let list = [...this.users];

      if (this.roleFilter) {
        list = list.filter(user => user.user_type === this.roleFilter);
      }

      if (term) {
        list = list.filter(user =>
          user.name.toLowerCase().includes(term) ||
          user.email.toLowerCase().includes(term) ||
          user.role_name.toLowerCase().includes(term)
        );
      }

      list.sort((a, b) => {
        const direction = this.sortDirection === 'asc' ? 1 : -1;
        if (this.sortBy === 'created_at') {
          return (this.parseDateBr(a.created_at) - this.parseDateBr(b.created_at)) * direction;
        }
        if (this.sortBy === 'user_type') {
          return a.user_type.localeCompare(b.user_type) * direction;
        }
        return a.name.localeCompare(b.name) * direction;
      });

      return list;
    }
  },
  mounted() {
    this.userModal = new Modal(document.getElementById('userModal'));
    this.confirmModal = new Modal(document.getElementById('confirmModal'));
    this.toast = new Toast(document.getElementById('toast'));
    this.loadCurrentUser();
    this.loadUsers();
  },
  methods: {
    async loadCurrentUser() {
      try {
        // Carrega informações do usuário atual
        const response = await axios.get('/api/current-user');
        this.currentUser = {
          id: response.data.id,
          user_type: response.data.user_type || 'user'
        };
      } catch (error) {
        console.error('Erro ao carregar usuário atual:', error);
        this.showToast('Erro', 'Não foi possível carregar o usuário atual', 'error', 'bi-exclamation-triangle');
      }
    },
    
    async loadUsers() {
      try {
        this.loading = true;
        const response = await axios.get('/api/users');
        this.users = response.data;
        this.calculateStats();
      } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        if (error.response?.status === 403) {
          this.showToast('Permissão Negada', 'Você não tem permissão para acessar esta área.', 'error', 'bi-shield-slash');
        } else {
          this.showToast('Erro', 'Falha ao carregar usuários', 'error', 'bi-exclamation-triangle');
        }
      } finally {
        this.loading = false;
      }
    },
    
    calculateStats() {
      this.stats.total = this.users.length;
      this.stats.admins = this.users.filter(u => u.user_type === 'admin').length;
      this.stats.managers = this.users.filter(u => u.user_type === 'manager').length;
      this.stats.users = this.users.filter(u => u.user_type === 'user').length;
    },

    toggleSortDirection() {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    },

    parseDateBr(value) {
      if (!value) return new Date(0);
      const parts = value.split('/');
      if (parts.length !== 3) return new Date(value);
      const [day, month, year] = parts.map(Number);
      return new Date(year, month - 1, day);
    },
    
    canEditUser(user) {
      // Pode editar a si mesmo (sem trocar tipo)
      if (user.id === this.currentUser.id) return true;
      
      // Admin pode editar todos
      if (this.currentUser.user_type === 'admin') return true;
      
      // Manager não pode editar admin
      if (user.user_type === 'admin') return false;
      
      // Manager pode editar user
      if (this.currentUser.user_type === 'manager' && user.user_type === 'user') return true;
      
      // Manager não pode editar outro manager
      if (this.currentUser.user_type === 'manager' && user.user_type === 'manager') return false;
      
      return false;
    },
    
    canDeleteUser(user) {
      // Não pode excluir a si mesmo
      if (user.id === this.currentUser.id) return false;
      
      // Admin pode excluir todos
      if (this.currentUser.user_type === 'admin') return true;
      
      // Manager não pode excluir admin nem manager
      if (this.currentUser.user_type === 'manager') {
        return user.user_type === 'user'; // Apenas user
      }
      
      return false;
    },
    
    openCreateUserModal() {
      this.editingUser = null;
      this.resetForm();
      this.userModal.show();
    },
    
    resetForm() {
      this.formData = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        user_type: 'user'
      };
      this.editingUser = null;
    },
    
    cancelForm() {
      this.userModal.hide();
      this.resetForm();
    },
    
    editUser(user) {
      if (!this.canEditUser(user)) {
        this.showToast('Permissão Negada', 'Você não tem permissão para editar este usuário.', 'warning', 'bi-shield-exclamation');
        return;
      }
      
      this.editingUser = user;
      this.formData = {
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        user_type: user.user_type
      };
      this.userModal.show();
    },
    
    async createUser() {
      if (!this.validateForm()) {
        return;
      }

      this.loading = true;
      try {
        const response = await axios.post('/api/users', this.formData);
        
        if (response.data.success) {
          this.users.push(response.data.user);
          this.calculateStats();
          this.userModal.hide();
          this.resetForm();
          this.showToast('Sucesso', 'Usuário criado com sucesso', 'success', 'bi-check-circle');
        } else {
          throw new Error(response.data.message);
        }
      } catch (error) {
        console.error('Erro ao criar usuário:', error);
        const message = error.response?.data?.message || 'Falha ao criar usuário';
        const errors = error.response?.data?.errors;
        
        if (errors) {
          // Mostra o primeiro erro de validação
          const firstError = Object.values(errors)[0][0];
          this.showToast('Erro de Validação', firstError, 'error', 'bi-exclamation-triangle');
        } else {
          this.showToast('Erro', message, 'error', 'bi-exclamation-triangle');
        }
      } finally {
        this.loading = false;
      }
    },
    
    async updateUser() {
      if (!this.validateForm(true)) {
        return;
      }

      this.loading = true;
      try {
        const response = await axios.put(`/api/users/${this.editingUser.id}`, this.formData);
        
        if (response.data.success) {
          const index = this.users.findIndex(u => u.id === this.editingUser.id);
          if (index !== -1) {
            this.users[index] = response.data.user;
          }
          this.calculateStats();
          this.userModal.hide();
          this.resetForm();
          this.showToast('Sucesso', 'Usuário atualizado com sucesso', 'success', 'bi-check-circle');
        } else {
          throw new Error(response.data.message);
        }
      } catch (error) {
        console.error('Erro ao atualizar usuário:', error);
        const message = error.response?.data?.message || 'Falha ao atualizar usuário';
        const errors = error.response?.data?.errors;
        
        if (errors) {
          // Mostra o primeiro erro de validação
          const firstError = Object.values(errors)[0][0];
          this.showToast('Erro de Validação', firstError, 'error', 'bi-exclamation-triangle');
        } else {
          this.showToast('Erro', message, 'error', 'bi-exclamation-triangle');
        }
      } finally {
        this.loading = false;
      }
    },
    
    deleteUser(user) {
      if (!this.canDeleteUser(user)) {
        this.showToast('Permissão Negada', 'Você não tem permissão para excluir este usuário.', 'warning', 'bi-shield-exclamation');
        return;
      }
      
      this.showConfirmModal(
        `Tem certeza que deseja excluir o usuário "${user.name}" (${user.email})?`,
        async () => {
          this.loading = true;
          try {
            const response = await axios.delete(`/api/users/${user.id}`);
            
            if (response.data.success) {
              this.users = this.users.filter(u => u.id !== user.id);
              this.calculateStats();
              this.showToast('Usuário Removido', 'O usuário foi excluído com sucesso', 'success', 'bi-check-circle');
            }
          } catch (error) {
            console.error('Erro ao excluir usuário:', error);
            const message = error.response?.data?.message || 'Falha ao excluir usuário';
            this.showToast('Erro', message, 'error', 'bi-exclamation-triangle');
          } finally {
            this.loading = false;
          }
        }
      );
    },
    
    validateForm(isUpdate = false) {
      // Limpa mensagens antigas
      this.toast.hide();
      
      if (!this.formData.name.trim()) {
        this.showToast('Atenção', 'Informe o nome do usuário', 'warning', 'bi-exclamation-triangle');
        return false;
      }
      
      if (!this.formData.email.trim()) {
        this.showToast('Atenção', 'Informe o email do usuário', 'warning', 'bi-exclamation-triangle');
        return false;
      }
      
      // Valida email básico
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(this.formData.email)) {
        this.showToast('Atenção', 'Informe um email válido', 'warning', 'bi-exclamation-triangle');
        return false;
      }
      
      // Para criação, senha é obrigatória
      if (!isUpdate && !this.formData.password) {
        this.showToast('Atenção', 'Informe uma senha', 'warning', 'bi-exclamation-triangle');
        return false;
      }
      
      // Se informou senha (em criação ou atualização)
      if (this.formData.password) {
        if (this.formData.password.length < 8) {
          this.showToast('Atenção', 'A senha deve ter no mínimo 8 caracteres', 'warning', 'bi-exclamation-triangle');
          return false;
        }
        
        if (this.formData.password !== this.formData.password_confirmation) {
          this.showToast('Atenção', 'As senhas não coincidem', 'warning', 'bi-exclamation-triangle');
          return false;
        }
      }
      
      return true;
    },
    
    getRoleBadgeClass(userType) {
      const classes = {
        'admin': 'chip-admin',
        'manager': 'chip-manager',
        'user': 'chip-user'
      };
      return classes[userType] || 'bg-secondary';
    },
    
    getAvatarClass(userType) {
      const classes = {
        'admin': 'avatar-admin',
        'manager': 'avatar-manager',
        'user': 'avatar-user'
      };
      return classes[userType] || 'avatar-user';
    },
    
    showConfirmModal(message, callback) {
      this.confirmMessage = message;
      this.confirmCallback = callback;
      this.confirmModal.show();
    },
    
    confirmAction() {
      if (this.confirmCallback) {
        this.confirmCallback();
      }
      this.confirmModal.hide();
    },
    
    showToast(title, message, type = 'info', icon = 'bi-info-circle') {
      const toastClasses = {
        'success': 'bg-success text-white',
        'error': 'bg-danger text-white',
        'warning': 'bg-warning text-dark',
        'info': 'bg-info text-white'
      };
      
      const toastIcons = {
        'success': 'bi-check-circle',
        'error': 'bi-exclamation-triangle',
        'warning': 'bi-exclamation-triangle',
        'info': 'bi-info-circle'
      };
      
      this.toastTitle = title;
      this.toastMessage = message;
      this.toastClass = toastClasses[type] || 'bg-info text-white';
      this.toastIcon = icon || toastIcons[type] || 'bi-info-circle';
      
      this.toast.show();
      
      // Auto-hide após 5 segundos
      setTimeout(() => {
        this.toast.hide();
      }, 5000);
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Fraunces:wght@600&display=swap');

.page-title {
  font-family: "Inter";
  font-size: 2.1rem;
  font-weight: 600;
  color: #f2f7ff;
}

.page-subtitle {
  font-family: "Inter";
  color: rgba(210, 225, 255, 0.75);
}

.toolbar-card {
  background: linear-gradient(135deg, rgba(8, 35, 72, 0.9), rgba(11, 51, 100, 0.9));
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  box-shadow: 0 15px 30px rgba(4, 20, 40, 0.25);
}

.toolbar-card .form-control,
.toolbar-card .form-select {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #f5f7ff;
}

.toolbar-card .form-control::placeholder {
  color: rgba(220, 235, 255, 0.6);
}

.toolbar-card .input-group-text {
  color: rgba(220, 235, 255, 0.6);
}

.stat-card {
  position: relative;
  padding: 1.2rem 1.4rem;
  border-radius: 18px;
  color: #f7f9ff;
  overflow: hidden;
  min-height: 120px;
  box-shadow: 0 18px 28px rgba(4, 20, 40, 0.3);
}

.stat-label {
  font-size: 0.9rem;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  opacity: 0.7;
}

.stat-value {
  font-family: "Inter";
  font-size: 2.2rem;
  margin-top: 0.4rem;
}

.stat-icon {
  position: absolute;
  right: 16px;
  bottom: 12px;
  font-size: 2rem;
  opacity: 0.3;
}

.stat-total {
  background: linear-gradient(130deg, #0f3b7a, #1456b4);
}

.stat-admins {
  background: linear-gradient(130deg, #0f2f5a, #17498e);
}

.stat-managers {
  background: linear-gradient(130deg, #0f4a5f, #1f7aa1);
}

.stat-users {
  background: linear-gradient(130deg, #254065, #385f92);
}

.user-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.2rem 1.4rem;
  border-radius: 18px;
  background: rgba(7, 38, 78, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 12px 26px rgba(3, 18, 36, 0.25);
  margin-bottom: 1rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.user-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 36px rgba(3, 18, 36, 0.35);
}

.user-card-main {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar-lg {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Inter";
  font-size: 1.5rem;
  color: #ffffff;
  background: rgba(255, 255, 255, 0.1);
}

.user-meta {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.user-name {
  font-family: "Inter";
  font-weight: 600;
  font-size: 1.05rem;
  color: #f6f9ff;
}

.user-email {
  color: rgba(210, 225, 255, 0.7);
  font-size: 0.9rem;
}

.user-tags {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.25rem 0.7rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  color: #fff;
}

.chip-muted {
  background: rgba(255, 255, 255, 0.12);
}

.chip-self {
  background: rgba(255, 255, 255, 0.18);
}

.chip-admin {
  background: linear-gradient(135deg, #1c4fa3, #2b76d4);
}

.chip-manager {
  background: linear-gradient(135deg, #1f7aa1, #2db9c9);
}

.chip-user {
  background: linear-gradient(135deg, #5c6e8f, #738bb5);
}

.avatar-admin {
  background: rgba(35, 100, 200, 0.5);
}

.avatar-manager {
  background: rgba(35, 150, 170, 0.5);
}

.avatar-user {
  background: rgba(130, 150, 180, 0.5);
}

.user-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-outline-light {
  border-color: rgba(255, 255, 255, 0.35);
  color: #f7f9ff;
}

.btn-outline-light:hover {
  background: rgba(255, 255, 255, 0.12);
}
</style>
<style scoped>
.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: rgba(186, 154, 106, 0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
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

.badge {
  font-weight: 500;
  letter-spacing: 0.3px;
  text-transform: capitalize;
}

.btn-outline-secondary:hover {
  background-color: var(--primary-color);
  color: var(--text-primary);
}

.btn-outline-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  color: white;
}

.btn-outline-danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.toast {
  min-width: 300px;
  z-index: 1055;
}

.modal-backdrop {
  z-index: 1050;
}

.modal {
  z-index: 1060;
}

/* Animações suaves */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
}

/* Responsividade */
@media (max-width: 768px) {
  .card-body {
    padding: 1rem;
  }
  
  .d-flex.gap-3 {
    gap: 1rem !important;
  }
  
  .user-avatar {
    width: 35px;
    height: 35px;
  }
  
  .badge {
    font-size: 0.75rem;
  }
}
</style>
