import './bootstrap';
import { createApp } from 'vue';

// Import components
import DashboardPage from './components/DashboardPage.vue';
import VideosPage from './components/VideosPage.vue';
import SchedulePage from './components/SchedulePage.vue';
import LogsPage from './components/LogsPage.vue';
import PreviewPage from './components/PreviewPage.vue';
import SettingsPage from './components/SettingsPage.vue';

// Create Vue app
const app = createApp({});

// Register components globally
app.component('dashboardpage', DashboardPage);
app.component('videospage', VideosPage);
app.component('schedulepage', SchedulePage);
app.component('logspage', LogsPage);
app.component('previewpage', PreviewPage);
app.component('settingspage', SettingsPage);

// Mount to your app element
app.mount('#app');
