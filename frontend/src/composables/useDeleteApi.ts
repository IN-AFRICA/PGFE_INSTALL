import { ref } from 'vue'
import api from '@/services/api'

export function useDeleteApi<T = any>() {
  const deleting = ref(false)
  const errorDelete = ref<string | null>(null)
  const success = ref(false)
  const deleteResponse = ref<T | null>(null)

  const deleteItem = async (url: string, data?: any): Promise<T | null> => {
    deleting.value = true
    errorDelete.value = null
    success.value = false
    try {
      const res = data ? await api.delete<T>(url, { data }) : await api.delete<T>(url)
      deleteResponse.value = res.data
      console.log('Delete response:', deleteResponse.value)
      success.value = true
      return deleteResponse.value
    } catch (err: any) {
      console.log('Delete response:', err)
      errorDelete.value =
        err?.response?.data?.message || err.message || 'Erreur lors de la suppression'
      return null
    } finally {
      deleting.value = false
    }
  }

  return {
    deleting,
    errorDelete,
    deleteResponse,
    deleteItem,
    success,
  }
}
