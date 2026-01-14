/**
 * router/index.ts
 *
 * Automatic routes for `./src/pages/*.vue`
 */

// Composables
import { useAuth0 } from "@auth0/auth0-vue";
import { GlobalStore } from "@/stores/globals";
import { routes } from 'vue-router/auto-routes';
import type { ProfileType } from "@/types/StoreTypes";
// @ts-expect-error vue-router/auto types are provided by unplugin-vue-router
import { createRouter, createWebHistory } from "vue-router/auto"
import {storeToRefs} from "pinia";

// List of routes to protect
const protected_routes = [
  '/',
  '/dashboard',
  '/assignees',
  '/customers',
  '/contacts',
  '/equipments',
  '/ucc-filings',
  '/providers',
  '/files',
  '/partners',
  '/users',
  '/import',
  '/import',
  '/settings',
]

// Patch the routes that are to be protected
routes.forEach(route => {
  if (protected_routes.includes(route.path.toLowerCase())) {
    route.meta = { ...(route.meta || {}), requiresAuth: true }
  }
})

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

// Validate with Auth0 and set "profile" in pinia
router.beforeEach(async (to:any, from:any, next:any) => {
  const store = GlobalStore()
  const { isAuthenticated, user, isLoading } = useAuth0()
  const { SetProfile } = store;
  const {idp_partner_users} = storeToRefs(store);

  while (isLoading.value) {
    await new Promise(resolve => setTimeout(resolve, 50))
  }
  if (isAuthenticated.value && user.value) {
    // We don't want extra xhr here, we initially set them as user
    // then inside App.vue, we find their actual role
    let role = 'user';

    // When soft routing to a new page, the role gets reset
    // but we can now read the idp_partner_users.value
    if (idp_partner_users.value.length > 0) {
      const partner_user = idp_partner_users.value.find((u:any)=>u.user_id==user.value?.sub);
      if (partner_user) {
        role = partner_user.role ?? "User";
      }
    }

    SetProfile({...user.value,role} as ProfileType);
  } else {
    SetProfile(null)
  }
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return next('/login')
  }
  next()
})

// Workaround for https://github.com/vitejs/vite/issues/11804
router.onError((err:any, to:any) => {
  if (err?.message?.includes?.('Failed to fetch dynamically imported module')) {
    if (localStorage.getItem('vuetify:dynamic-reload')) {
      console.error('Dynamic import error, reloading page did not fix it', err)
    } else {
      console.log('Reloading page to fix dynamic import error')
      localStorage.setItem('vuetify:dynamic-reload', 'true')
      location.assign(to.fullPath)
    }
  } else {
    console.error(err)
  }
})

router.isReady().then(() => {
  localStorage.removeItem('vuetify:dynamic-reload')
})

export default router;
