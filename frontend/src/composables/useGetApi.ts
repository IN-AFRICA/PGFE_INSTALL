import { ref, type Ref } from 'vue'
import api from '@/services/api'
import type { AxiosResponse, AxiosError } from 'axios'

export interface UseGetApiReturn<T> {
  data: Ref<T | null>
  loading: Ref<boolean>
  error: Ref<string | null>
  fetchData: (params?: Record<string, any>) => Promise<void>
  refresh: () => Promise<void>
  meta?: Ref<any | null>
  lastResponseRaw?: Ref<any | null>
}

export function useGetApi<T = any>(endpoint: string): UseGetApiReturn<T> {
  const data = ref<T | null>(null) as Ref<T | null>
  const loading = ref<boolean>(false)
  const error = ref<string | null>(null)
  const lastParams = ref<Record<string, any>>({})
  const meta = ref<any | null>(null)
  const lastResponseRaw = ref<any | null>(null)

  async function fetchData(params: Record<string, any> = {}): Promise<void> {
    loading.value = true
    error.value = null
    lastParams.value = params

    try {
      const response: AxiosResponse<T> = await api.get(endpoint, { params })
      lastResponseRaw.value = response.data as any

      // Gestion flexible de la structure de réponse
      if (response.data && typeof response.data === 'object' && 'data' in response.data) {
        const payload: any = response.data as any

        // Vérifier si payload.data contient aussi un champ 'data' (pagination Laravel)
        if (payload.data && typeof payload.data === 'object' && 'data' in payload.data) {
          // Cas de double imbrication: response.data.data.data
          const paginatedData: any = payload.data
          data.value = paginatedData.data

          // Extraire les métadonnées de pagination
          meta.value = {
            current_page: paginatedData.current_page,
            last_page: paginatedData.last_page,
            per_page: paginatedData.per_page,
            total: paginatedData.total,
            from: paginatedData.from,
            to: paginatedData.to,
            first_page_url: paginatedData.first_page_url,
            last_page_url: paginatedData.last_page_url,
            next_page_url: paginatedData.next_page_url,
            prev_page_url: paginatedData.prev_page_url,
            links: paginatedData.links,
          }
        } else {
          // Cas simple: response.data.data
          data.value = payload.data
          meta.value = payload.meta ?? payload.pagination ?? null
        }
      } else {
        data.value = response.data
        meta.value = null
      }
    } catch (err) {
      const axiosError = err as AxiosError

      // Gestion détaillée des erreurs
      if (axiosError.response) {
        const status = axiosError.response.status
        const responseData = axiosError.response.data as any

        switch (status) {
          case 404:
            error.value = 'Ressource non trouvée'
            break
          case 403:
            error.value = 'Accès refusé'
            break
          case 422:
            error.value = responseData?.message || 'Données invalides'
            break
          case 500:
            error.value = 'Erreur serveur interne'
            break
          default:
            error.value = responseData?.message || axiosError.message || 'Erreur API'
        }
      } else if (axiosError.request) {
        error.value = 'Erreur de connexion'
      } else {
        error.value = axiosError.message || 'Erreur inconnue'
      }
    } finally {
      loading.value = false
    }
  }

  // Fonction pour rafraîchir avec les derniers paramètres
  const refresh = async (): Promise<void> => {
    await fetchData(lastParams.value)
  }

  return {
    data,
    loading,
    error,
    fetchData,
    refresh,
    meta,
    lastResponseRaw,
  }
}
