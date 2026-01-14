import App from './App.vue';
import {createApp} from 'vue';
import {createPinia} from 'pinia';
import "animator-css/animator.css";
import {registerPlugins} from '@/plugins';
import '@mdi/font/css/materialdesignicons.css';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

const app = createApp(App);
app.use(pinia);
registerPlugins(app)
app.mount('#app')
