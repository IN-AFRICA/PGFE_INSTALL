import { ref, computed } from 'vue'
import { useGetApi } from './useGetApi'
import { usePostApi } from './usePostApi'
import { usePutApi } from './usePutApi'
import { useDeleteApi } from './useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'

// Types
export interface PaymentMotif {
  id: number
  fee_type_id: number
  name: string
  code: string
  description: string
  created_at?: string
  updated_at?: string
  fee_type?: { name: string }
}

export interface PaymentMotifsApiResponse {
  success: boolean
  message: string
  data?: PaymentMotif[]
  motif?: string
}

export interface PaymentMotifsApiGetResponse {
  success: boolean
  motifs?: PaymentMotif[]
}

export interface PaymentMotifFormData {
  fee_type_id: number
  name: string
  code: string
  description: string
}

export function usePaymentMotifs() {
  // Variables réactives
  const searchQuery = ref('')
  const selectedItems = ref<number[]>([])

  // API composables
  const {
    data: motifsData,
    error: motifsError,
    loading: motifsLoading,
    fetchData: fetchMotifs,
  } = useGetApi<PaymentMotifsApiGetResponse>(API_ROUTES.GET_PAYMENT_MOTIFS)

  const {
    response: createResponse,
    error: createError,
    loading: createLoading,
    postData: createMotif,
  } = usePostApi<PaymentMotifsApiResponse>()

  const {
    response: updateResponse,
    error: updateError,
    loading: updateLoading,
    putData: updateMotif,
  } = usePutApi<PaymentMotifsApiResponse>()

  const {
    deleteResponse,
    errorDelete: deleteError,
    deleting: deleteLoading,
    deleteItem: deleteMotif,
  } = useDeleteApi<PaymentMotifsApiResponse>()

  // Computed
  const filteredMotifs = computed(() => {
    if (!motifsData.value?.motifs) return []

    if (!searchQuery.value) {
      return motifsData.value.motifs
    }

    const query = searchQuery.value.toLowerCase()
    return motifsData.value.motifs.filter(
      (motif) =>
        motif.name.toLowerCase().includes(query) ||
        motif.code.toLowerCase().includes(query) ||
        motif.description.toLowerCase().includes(query) ||
        motif.fee_type?.name?.toLowerCase().includes(query),
    )
  })

  const selectedMotifs = computed(() => {
    if (!filteredMotifs.value || !Array.isArray(filteredMotifs.value)) {
      return []
    }
    return filteredMotifs.value.filter((motif) => selectedItems.value.includes(motif.id))
  })

  const isAllSelected = computed(() => {
    return (
      filteredMotifs.value.length > 0 && selectedItems.value.length === filteredMotifs.value.length
    )
  })

  // Actions
  const createPaymentMotif = async (data: PaymentMotifFormData) => {
    try {
      await createMotif(API_ROUTES.CREATE_PAYMENT_MOTIF, data)

      if (createError.value) {
        showCustomToast({ message: createError.value, type: 'error' })
        return false
      }

      if (createResponse.value?.success) {
        showCustomToast({
          message: createResponse.value.message || 'Motif de paiement créé avec succès.',
          type: 'success',
        })
        await fetchMotifs()
        return true
      } else {
        showCustomToast({
          message:
            createResponse.value?.message || 'Erreur lors de la création du motif de paiement.',
          type: 'error',
        })
        return false
      }
    } catch (error) {
      console.error('Erreur lors de la création:', error)
      showCustomToast({
        message: "Une erreur inattendue s'est produite.",
        type: 'error',
      })
      return false
    }
  }

  const updatePaymentMotif = async (id: number, data: PaymentMotifFormData) => {
    try {
      await updateMotif(API_ROUTES.UPDATE_PAYMENT_MOTIF(id), data)

      if (updateError.value) {
        showCustomToast({ message: updateError.value, type: 'error' })
        return false
      }

      if (updateResponse.value?.success) {
        showCustomToast({
          message: updateResponse.value.message || 'Motif de paiement modifié avec succès.',
          type: 'success',
        })
        await fetchMotifs()
        return true
      } else {
        showCustomToast({
          message:
            updateResponse.value?.message || 'Erreur lors de la modification du motif de paiement.',
          type: 'error',
        })
        return false
      }
    } catch (error) {
      console.error('Erreur lors de la modification:', error)
      showCustomToast({
        message: "Une erreur inattendue s'est produite.",
        type: 'error',
      })
      return false
    }
  }

  const deletePaymentMotif = async (motifId: number) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce motif de paiement ?')) {
      return false
    }

    try {
      await deleteMotif(API_ROUTES.DELETE_PAYMENT_MOTIF(motifId))

      if (deleteError.value) {
        showCustomToast({ message: deleteError.value, type: 'error' })
        return false
      }

      if (deleteResponse.value?.success) {
        showCustomToast({
          message: deleteResponse.value.message || 'Motif de paiement supprimé avec succès.',
          type: 'success',
        })
        await fetchMotifs()
        return true
      } else {
        showCustomToast({
          message:
            deleteResponse.value?.message || 'Erreur lors de la suppression du motif de paiement.',
          type: 'error',
        })
        return false
      }
    } catch (error) {
      console.error('Erreur lors de la suppression:', error)
      showCustomToast({
        message: "Une erreur inattendue s'est produite.",
        type: 'error',
      })
      return false
    }
  }

  const deleteSelectedMotifs = async () => {
    if (selectedMotifs.value.length === 0) return false

    const motifNames = selectedMotifs.value.map((m) => m.name).join(', ')
    if (
      !confirm(
        `Êtes-vous sûr de vouloir supprimer ces ${selectedMotifs.value.length} motif(s) de paiement ?\n\n${motifNames}`,
      )
    ) {
      return false
    }

    try {
      const deletePromises = selectedMotifs.value.map((motif) =>
        deleteMotif(API_ROUTES.DELETE_PAYMENT_MOTIF(motif.id)),
      )

      await Promise.all(deletePromises)

      showCustomToast({
        message: `${selectedMotifs.value.length} motif(s) de paiement supprimé(s) avec succès.`,
        type: 'success',
      })

      selectedItems.value = []
      await fetchMotifs()
      return true
    } catch (error) {
      console.error('Erreur lors de la suppression multiple:', error)
      showCustomToast({
        message: 'Erreur lors de la suppression des motifs de paiement.',
        type: 'error',
      })
      return false
    }
  }

  // Gestion des sélections
  const toggleSelection = (motifId: number) => {
    const index = selectedItems.value.indexOf(motifId)
    if (index > -1) {
      selectedItems.value.splice(index, 1)
    } else {
      selectedItems.value.push(motifId)
    }
  }

  const toggleSelectAll = () => {
    if (isAllSelected.value) {
      selectedItems.value = []
    } else {
      selectedItems.value = filteredMotifs.value.map((motif) => motif.id)
    }
  }

  const clearSelection = () => {
    selectedItems.value = []
  }

  // Utilitaires
  const formatDate = (dateString?: string) => {
    if (!dateString) return '-'
    try {
      return new Date(dateString).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    } catch {
      return '-'
    }
  }

  const getFeeTypeName = (motif: PaymentMotif) => {
    return motif.fee_type?.name || 'Non défini'
  }

  return {
    // State
    searchQuery,
    selectedItems,
    motifsData,
    motifsError,
    motifsLoading,
    createLoading,
    updateLoading,
    deleteLoading,

    // Computed
    filteredMotifs,
    selectedMotifs,
    isAllSelected,

    // Actions
    fetchMotifs,
    createPaymentMotif,
    updatePaymentMotif,
    deletePaymentMotif,
    deleteSelectedMotifs,

    // Selection management
    toggleSelection,
    toggleSelectAll,
    clearSelection,

    // Utilities
    formatDate,
    getFeeTypeName,
  }
}
