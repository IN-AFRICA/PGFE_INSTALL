import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { authRoutes } from './auth'
import { studentRoutes } from './student.ts'
import ModuleSelector from '@/app/ModuleSelector.vue'
import { rhModuleRoutes } from './rh-module'
import { moduleComptaRoutes } from './compta-module'
import { adminRoutes } from '@/router/admin.ts'
import { infraRoutes } from '@/router/infra.ts'
import { stockRoutes } from '@/router/stock.ts'
// import { useAuthStore } from '@/stores/auth' // Keeping original comment for context if needed, but adding real import
import { useAuthStore } from '@/stores/auth'
import { locationRoutes } from './location.ts'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'root',
    component: ModuleSelector,
  },
  ...adminRoutes,
  ...authRoutes,
  ...studentRoutes,
  ...rhModuleRoutes,
  ...moduleComptaRoutes,
  ...infraRoutes,
  ...stockRoutes,
  ...locationRoutes,
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

/* Global auth guard: block access if not authenticated */
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const publicPaths = ['/login', '/register']

  // Allow explicitly public paths
  if (publicPaths.includes(to.path)) {
    return next()
  }

  // Otherwise require authentication
  if (!authStore.isAuthenticated) {
    return next({ path: '/login', query: { redirect: to.fullPath } })
  }

  // Check for permission requirements
  if (to.meta.permission) {
    const permission = to.meta.permission as string
    if (!authStore.can(permission)) {
      // User is authenticated but doesn't have the required permission
      console.warn(`Access denied. Missing permission: ${permission}`)
      return next({ path: '/' })
    }
  }

  next()
})

export default router
