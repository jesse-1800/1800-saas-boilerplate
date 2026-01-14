import App from './App.vue';
import {createApp} from 'vue';
import {createPinia} from 'pinia';
import "animator-css/animator.css";
import {registerPlugins} from '@/plugins';
import {createAuth0} from "@auth0/auth0-vue";
import '@mdi/font/css/materialdesignicons.css';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

const app = createApp(App);
app.use(pinia);
app.use(createAuth0(<any>{
  domain: import.meta.env.VITE_AUTH0_DOMAIN, clientId: import.meta.env.VITE_AUTH0_CLIENT_ID, authorizationParams: {
    redirect_uri: import.meta.env.VITE_AUTH0_REDIRECT, audience: import.meta.env.VITE_AUTH0_AUDIENCE
  }, cacheLocation: 'localstorage',
}));
app.config.errorHandler = (err: any, instance, info) => {
  if (err.error === 'login_required' || err.message?.includes('Login required')) {
    console.log('Auth0: Login required, redirecting...');
    location.href = "/login"
    return;
  }
  console.error('Global error:', err);
};
registerPlugins(app)
app.mount('#app')
