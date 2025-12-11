import './bootstrap';
import { createApp } from 'vue';
import axios from 'axios';

// Configurar Axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;



// Import components
import DashboardPage from './components/DashboardPage.vue';
import VideosPage from './components/VideosPage.vue';
import SchedulePage from './components/SchedulePage.vue';
import LogsPage from './components/LogsPage.vue';
import PreviewPage from './components/PreviewPage.vue';
import SettingsPage from './components/SettingsPage.vue';
import UsersPage from './components/UsersPage.vue';

// Create Vue app
const app = createApp({});

// Adicionar Axios ao Vue prototype
app.config.globalProperties.$http = axios;

// Register components globally
app.component('dashboardpage', DashboardPage);
app.component('videospage', VideosPage);
app.component('schedulepage', SchedulePage);
app.component('logspage', LogsPage);
app.component('previewpage', PreviewPage);
app.component('settingspage', SettingsPage);
app.component('usersPage', UsersPage);

// Mount to your app element
app.mount('#app');
