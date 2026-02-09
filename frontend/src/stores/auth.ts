import api from '@/services/api'
import { API_ROUTES } from '@/utils/constants/api_route'
import { defineStore } from 'pinia'

interface User {
  id: number
  name: string
  email: string
  email_verified_at: string
  created_at: string
  updated_at: string
  last_login_at: string | null
  school_id?: number | string | null
}

interface AuthState {
  token: string | null
  user: User | null
  permissions: string[]
  roles: string[]
}

const LOCAL_STORAGE_KEY = 'auth'

export function loadAuthState(): AuthState {
  const data = localStorage.getItem(LOCAL_STORAGE_KEY)
  if (data) {
    try {
      const parsed = JSON.parse(data)
      // Ensure specific fields exist for backward compatibility
      return {
        token: parsed.token || null,
        user: parsed.user || null,
        permissions: Array.isArray(parsed.permissions) ? parsed.permissions : [],
        roles: Array.isArray(parsed.roles) ? parsed.roles : [],
      }
    } catch {
      return { token: null, user: null, permissions: [], roles: [] }
    }
  }
  return { token: null, user: null, permissions: [], roles: [] }
}

function saveAuthState(state: AuthState) {
  localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(state))
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => loadAuthState(),
  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    userName: (state) => state.user?.name ?? '',
    userEmail: (state) => state.user?.email ?? '',
    userSchoolId: (state) => state.user?.school_id ?? null,
    // RBAC Getters
    can: (state) => (permission: string) => {
      // Super admin bypass or explicit permission check
      return (
        state.roles.includes('super-admin') ||
        state.roles.includes('admin') ||
        state.permissions.includes(permission)
      )
    },
    hasRole: (state) => (role: string) => state.roles.includes(role),
  },
  actions: {
    setSession(payload: {
      token: string
      user: User
      permissions?: string[]
      roles?: string[]
    }) {
      this.token = payload.token
      this.user = payload.user
      this.permissions = payload.permissions || []
      this.roles = payload.roles || []
      saveAuthState(this.$state)
    },
    clearSession() {
      this.token = null
      this.user = null
      this.permissions = []
      this.roles = []
      saveAuthState({ token: null, user: null, permissions: [], roles: [] })
    },
    async verifyToken() {
      if (!this.token) return false
      try {
        const { data } = await api.get(API_ROUTES.USER_INFOS)
        if (!data || !data.user) throw new Error('Token invalid')

        // Mettre à jour la session avec les dernières données du serveur
        this.setSession({
          token: this.token,
          user: data.user,
          permissions: data.permissions,
          roles: data.roles,
        })

        return true
      } catch {
        this.clearSession()
        return false
      }
    },
    async logout() {
      try {
        await api.post(API_ROUTES.LOGOUT)
      } catch (e) {
        // On ignore les erreurs côté serveur pour garantir la déconnexion locale
      } finally {
        this.clearSession()
      }
    },
  },
})
