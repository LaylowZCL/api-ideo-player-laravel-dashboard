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
import AdminIndexPage from './components/AdminIndexPage.vue';
import AdGroupsPage from './components/AdGroupsPage.vue';
import AdminClientsPage from './components/AdminClientsPage.vue';
import AdminCampaignsPage from './components/AdminCampaignsPage.vue';
import AdminLogsPage from './components/AdminLogsPage.vue';
import ReportsPage from './components/ReportsPage.vue';
import AdTargetsPage from './components/AdTargetsPage.vue';

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
app.component('adminindexpage', AdminIndexPage);
app.component('adgroupspage', AdGroupsPage);
app.component('adminclientspage', AdminClientsPage);
app.component('admincampaignspage', AdminCampaignsPage);
app.component('adminlogspage', AdminLogsPage);
app.component('reportspage', ReportsPage);
app.component('adtargetspage', AdTargetsPage);

// Mount to your app element
app.mount('#app');
