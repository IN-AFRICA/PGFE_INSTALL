import { ref, type Ref } from 'vue'
import api from '@/services/api'
import type { AxiosResponse, AxiosError } from 'axios'

export interface PostData {
  [key: string]: any
}

export interface UsePostApiReturn<T> {
  loading: Ref<boolean>
  error: Ref<string | null>
  response: Ref<T | null>
  postData: (url: string, data: PostData) => Promise<void>
  success: Ref<boolean>
  status: Ref<number | null>
  errorDetails: Ref<any | null>
}

export function usePostApi<T = any>(): UsePostApiReturn<T> {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const response = ref<T | null>(null) as Ref<T | null>
  const success = ref(false)
  const status = ref<number | null>(null)
  const errorDetails = ref<any | null>(null)

  const postData = async (url: string, data: PostData): Promise<void> => {
    loading.value = true
    error.value = null
    response.value = null
    success.value = false
    status.value = null
    errorDetails.value = null

    try {
      const res: AxiosResponse<T> = await api.post(url, data)

      // Gestion flexible de la structure de réponse
      if (res.data && typeof res.data === 'object' && 'data' in res.data) {
        response.value = (res.data as any).data
      } else {
        response.value = res.data
      }

      success.value = true
      status.value = res.status
    } catch (err) {
      const axiosError = err as AxiosError

      // Gestion détaillée des erreurs
      if (axiosError.response) {
        const st = axiosError.response.status
        const responseData = axiosError.response.data as any
        status.value = st
        errorDetails.value = responseData

        switch (st) {
          case 400:
            error.value = 'Requête invalide'
            break
          case 401:
            error.value = 'Non autorisé'
            break
          case 403:
            error.value = 'Accès refusé'
            break
          case 422:
            // Erreurs de validation Laravel
            if (responseData?.errors) {
              const validationErrors = Object.entries(responseData.errors)
                .map(([field, msgs]: any) => {
                  const msgArray = Array.isArray(msgs) ? msgs : [msgs]
                  return `${field}: ${msgArray.join(', ')}`
                })
              error.value = validationErrors.join(' | ')
            } else {
              error.value = responseData?.message || 'Données invalides'
            }
            console.error('[usePostApi] Validation errors (422):', responseData?.errors || responseData?.message)
            break
          case 500:
            error.value = 'Erreur serveur interne'
            break
          default:
            error.value =
              responseData?.message ||
              responseData?.error ||
              axiosError.message ||
              "Erreur lors de l'ajout"
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

  return {
    loading,
    error,
    response,
    postData,
    success,
    status,
    errorDetails,
  }
}
