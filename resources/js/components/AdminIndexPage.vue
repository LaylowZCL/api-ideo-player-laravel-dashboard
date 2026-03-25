<template>
  <div class="admin-page">
    <div class="mb-4">
      <h1 class="page-title mb-1">Administração</h1>
      <p class="page-subtitle mb-0">Gestão central de grupos, clientes, campanhas e auditoria</p>
    </div>

    <div class="row g-3">
      <div class="col-md-3" v-for="card in cards" :key="card.title">
        <a :href="card.href" class="admin-card">
          <div class="admin-card-icon"><i :class="card.icon"></i></div>
          <div class="admin-card-title">{{ card.title }}</div>
          <div class="admin-card-text">{{ card.text }}</div>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminIndexPage',
  data() {
    return {
      cards: [
        {
          title: 'Grupos',
          text: 'Gerencie grupos AD e segmentações',
          icon: 'bi bi-people',
          href: '/admin/grupos'
        },
        {
          title: 'Clientes',
          text: 'Atribua grupos e chaves por cliente',
          icon: 'bi bi-pc-display',
          href: '/admin/clientes'
        },
        {
          title: 'Campanhas',
          text: 'Organize campanhas e prioridades',
          icon: 'bi bi-flag',
          href: '/admin/campanhas'
        },
        {
          title: 'Logs',
          text: 'Auditoria completa de ações',
          icon: 'bi bi-clipboard-data',
          href: '/admin/logs'
        }
      ]
    };
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Fraunces:wght@600&display=swap');

.admin-page {
  min-height: 60vh;
}

.page-title {
  font-family: "Inter";
  font-size: 2.2rem;
  color: #f2f7ff;
}

.page-subtitle {
  font-family: "Inter";
  color: rgba(210, 225, 255, 0.7);
}

.admin-card {
  display: block;
  padding: 1.5rem;
  border-radius: 18px;
  background: linear-gradient(140deg, rgba(9, 44, 92, 0.9), rgba(14, 64, 130, 0.9));
  border: 1px solid rgba(255, 255, 255, 0.08);
  text-decoration: none;
  color: #f2f7ff;
  min-height: 180px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  box-shadow: 0 14px 24px rgba(3, 18, 36, 0.25);
}

.admin-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 20px 34px rgba(3, 18, 36, 0.35);
}

.admin-card-icon {
  font-size: 2rem;
  opacity: 0.8;
}

.admin-card-title {
  margin-top: 1rem;
  font-family: "Inter";
  font-size: 1.2rem;
  font-weight: 600;
}

.admin-card-text {
  margin-top: 0.5rem;
  font-size: 0.9rem;
  color: rgba(210, 225, 255, 0.7);
}
</style>
