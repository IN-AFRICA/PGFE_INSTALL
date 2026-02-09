import { sanctumApi, TokenManager } from '@/services/api'
import { API_ROUTES } from '@/utils/constants/api_route'

export const useAuth = () => {
  const getCsrfToken = async (): Promise<void> => {
    // Désactiver complètement CSRF pour cPanel
    // Les requêtes directes évitent les problèmes CORS
    return Promise.resolve()
  }

  const checkAuthStatus = () => {
    const csrfToken = TokenManager.getCSRFToken()
    const bearerToken = TokenManager.getBearerToken()

    return {
      hasCSRF: !!csrfToken,
      hasBearer: !!bearerToken,
      isAuthenticated: !!bearerToken,
    }
  }

  return {
    getCsrfToken,
    checkAuthStatus,
  }
}
