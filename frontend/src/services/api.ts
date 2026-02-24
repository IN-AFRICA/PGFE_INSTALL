import { useAuthStore } from './../stores/auth'
import { useAppStore } from './../stores/app'
import { BASE_URL, SANCTUM_BASE_URL, API_ROUTES } from '@/utils/constants/api_route'
import router from '@/router'
import axios, {
  type AxiosInstance,
  type AxiosResponse,
  type InternalAxiosRequestConfig,
  type AxiosRequestConfig,
} from 'axios'
console.log('API_ROUTE - BASE_URL initial:', BASE_URL)

// Configuration de base pour Axios
const axiosConfig: AxiosRequestConfig = {
  timeout: 30000, // Augmenté à 30s à cause de la lenteur du backend sur certaines requêtes
  withCredentials: true, // Désactiver pour éviter CORS sur cPanel
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
}

// Instance principale pour les appels API
export const api: AxiosInstance = axios.create({
  ...axiosConfig,
  baseURL: BASE_URL,
  headers: {
    ...axiosConfig.headers,
    'Content-Type': 'application/json',
  },
})

// Instance séparée pour les appels Sanctum (CSRF)
const sanctumApi: AxiosInstance = axios.create({
  ...axiosConfig,
  baseURL: SANCTUM_BASE_URL,
})

// Utilitaires pour la gestion des tokens
class TokenManager {
  static getCSRFToken(): string | null {
    if (typeof document === 'undefined') return null

    const cookies = document.cookie.split(';')
    for (const cookie of cookies) {
      const [name, value] = cookie.trim().split('=')
      if (name === 'XSRF-TOKEN') {
        return decodeURIComponent(value)
      }
    }
    return null
  }

  static getBearerToken(): string | null {
    try {
      const authStore = useAuthStore()
      return authStore.token || null
    } catch {
      return null
    }
  }

  static isAuthRoute(url: string): boolean {
    const authRoutes = ['/login', '/register', '/forgot-password', '/reset-password']
    return authRoutes.some((route) => url.includes(route))
  }
}

// Intercepteur de requêtes pour l'API principale
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    if (!config.headers) {
      config.headers = {} as any
    }

    // Ajouter le token CSRF si disponible (optionnel)
    const csrfToken = TokenManager.getCSRFToken()
    if (csrfToken) {
      config.headers['X-XSRF-TOKEN'] = csrfToken
    }

    // Ajouter le token d'authentification si ce n'est pas une route d'auth
    if (config.url && !TokenManager.isAuthRoute(config.url)) {
      const bearerToken = TokenManager.getBearerToken()
      if (bearerToken) {
        config.headers.Authorization = `Bearer ${bearerToken}`
      }
    }

    // Debug logs
    console.log('AXIOS REQUEST:', {
      baseURL: config.baseURL,
      url: config.url,
      fullPath: (config.baseURL || '') + (config.url || ''),
      hasToken: !!config.headers.Authorization,
    })

    return config
  },
  (error) => {
    return Promise.reject(error)
  },
)

// Intercepteur de réponses avec gestion d'erreurs améliorée
api.interceptors.response.use(
  (response: AxiosResponse) => {
    return response
  },
  (error) => {
    const status = error?.response?.status
    const url = error?.config?.url

    // Gestion spécifique des erreurs
    switch (status) {
      case 401:
        // Token expiré / non valide
        try {
          const authStore = useAuthStore()
          authStore.clearSession()
          if (typeof window !== 'undefined' && !window.location.pathname.includes('/login')) {
            window.location.href = '/login'
          }
        } catch (e) {
          console.error('Erreur lors de la déconnexion:', e)
        }
        break

      case 403:
        break

      case 419:
        break

      case 422:
        break

      case 500:
        break

      case 503: {
        // Global Sync Lock — Backend signals that a sync is in progress
        const syncCode = error?.response?.data?.code
        const isSyncStatusPoll = url?.includes(API_ROUTES.SYNC_STATUS)

        if (syncCode === 'SYNC_IN_PROGRESS' && !isSyncStatusPoll) {
          try {
            const appStore = useAppStore()
            if (!appStore.isGlobalSyncing) {
              const currentRoute = router.currentRoute.value
              appStore.enterSyncLock(currentRoute)
              router.push({ path: '/' })
            }
          } catch (e) {
            console.error('[SyncLock] Erreur lors de la redirection:', e)
          }
        }
        break
      }

      default:
        break
    }

    return Promise.reject(error)
  },
)
sanctumApi.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    return config
  },
  (error) => Promise.reject(error),
)

sanctumApi.interceptors.response.use(
  (response: AxiosResponse) => {
    return response
  },
  (error) => {
    return Promise.reject(error)
  },
)

// Export des instances et utilitaires
export default api
export { sanctumApi, TokenManager }
export type { AxiosInstance, AxiosResponse }
