import { ref } from 'vue'
import api from '@/services/api' // adapte le chemin selon ton projet

export function usePutApi<T = any>() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const response = ref<T | null>(null)
  const success = ref(false)

  const putData = async (url: string, data: any, config = {}) => {
    loading.value = true
    error.value = null
    response.value = null
    success.value = false

    try {
      const res = await api.put<T>(url, data, config)
      console.log(res)
      response.value = res.data
      success.value = true
    } catch (err: any) {
      error.value = err?.response?.data?.message || err.message || 'Erreur lors de la modification'
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    response,
    success,
    putData,
  }
}
