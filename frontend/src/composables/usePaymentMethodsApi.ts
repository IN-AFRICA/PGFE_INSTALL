import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import type {
  PaymentMethod,
  PaymentMethodsApiResponse,
  PaymentMethodsGetResponse,
  PaymentMethodFormData,
} from '@/types/PaymentMethodTypes'

export function usePaymentMethodsApi() {
  const {
    data: methodsData,
    error: methodsError,
    loading: methodsLoading,
    fetchData: fetchMethods,
  } = useGetApi<PaymentMethodsGetResponse>(API_ROUTES.GET_PAYMENT_METHODE)

  const {
    response: createResponse,
    error: createError,
    loading: createLoading,
    postData: createMethod,
  } = usePostApi<PaymentMethodsApiResponse>()

  const {
    response: updateResponse,
    error: updateError,
    loading: updateLoading,
    putData: updateMethod,
  } = usePutApi<PaymentMethodsApiResponse>()

  const {
    deleteResponse,
    errorDelete: deleteError,
    deleting: deleteLoading,
    deleteItem: deleteMethod,
  } = useDeleteApi<PaymentMethodsApiResponse>()

  const handleCreateSubmit = async (values: PaymentMethodFormData) => {
    if (!values?.name || !values?.code) {
      showCustomToast({ message: 'Veuillez remplir tous les champs requis.', type: 'error' })
      return false
    }

    try {
      await createMethod(API_ROUTES.CREATE_PAYMENT_METHODE, values)

      if (createError.value) {
        showCustomToast({ message: createError.value, type: 'error' })
        return false
      }

      if (createResponse.value?.success) {
        showCustomToast({
          message: createResponse.value.message || 'Méthode de paiement créée avec succès.',
          type: 'success',
        })
        await fetchMethods()
        return true
      } else {
        showCustomToast({
          message:
            createResponse.value?.message ||
            'Erreur lors de la création de la méthode de paiement.',
          type: 'error',
        })
        return false
      }
    } catch (error: unknown) {
      let errorMessage = "Une erreur inattendue s'est produite."
      if (error instanceof Object && 'response' in error) {
        const axiosError = error as {
          response?: { data?: { message?: string; errors?: Record<string, string[]> } }
        }
        if (axiosError.response?.data?.message) {
          errorMessage = axiosError.response.data.message
        } else if (axiosError.response?.data?.errors) {
          const errors = axiosError.response.data.errors
          const errorList = Object.values(errors).flat()
          errorMessage = `Erreurs de validation: ${errorList.join(', ')}`
        }
      }
      showCustomToast({ message: errorMessage, type: 'error' })
      return false
    }
  }

  const handleUpdateSubmit = async (methodId: number, values: PaymentMethodFormData) => {
    try {
      await updateMethod(API_ROUTES.UPDATE_PAYMENT_METHODE(methodId), values)

      if (updateError.value) {
        showCustomToast({ message: updateError.value, type: 'error' })
        return false
      }

      if (updateResponse.value?.success) {
        showCustomToast({
          message: updateResponse.value.message || 'Méthode de paiement modifiée avec succès.',
          type: 'success',
        })
        await fetchMethods()
        return true
      } else {
        showCustomToast({
          message:
            updateResponse.value?.message ||
            'Erreur lors de la modification de la méthode de paiement.',
          type: 'error',
        })
        return false
      }
    } catch {
      showCustomToast({ message: "Une erreur inattendue s'est produite.", type: 'error' })
      return false
    }
  }

  const handleDeleteSingle = async (methodId: number) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette méthode de paiement ?')) {
      return false
    }

    try {
      await deleteMethod(API_ROUTES.DELETE_PAYMENT_METHODE(methodId))

      if (deleteError.value) {
        showCustomToast({ message: deleteError.value, type: 'error' })
        return false
      }

      if (deleteResponse.value?.success) {
        showCustomToast({
          message: deleteResponse.value.message || 'Méthode de paiement supprimée avec succès.',
          type: 'success',
        })
        await fetchMethods()
        return true
      } else {
        showCustomToast({
          message:
            deleteResponse.value?.message ||
            'Erreur lors de la suppression de la méthode de paiement.',
          type: 'error',
        })
        return false
      }
    } catch {
      showCustomToast({ message: "Une erreur inattendue s'est produite.", type: 'error' })
      return false
    }
  }

  const handleDeleteMultiple = async (methods: PaymentMethod[]) => {
    if (methods.length === 0) return false

    const methodNames = methods.map((m) => m.name).join(', ')
    if (
      !confirm(
        `Êtes-vous sûr de vouloir supprimer ces ${methods.length} méthode(s) de paiement ?\n\n${methodNames}`,
      )
    ) {
      return false
    }

    try {
      const deletePromises = methods.map((method) =>
        deleteMethod(API_ROUTES.DELETE_PAYMENT_METHODE(method.id)),
      )

      await Promise.all(deletePromises)

      showCustomToast({
        message: `${methods.length} méthode(s) de paiement supprimée(s) avec succès.`,
        type: 'success',
      })
      await fetchMethods()
      return true
    } catch {
      showCustomToast({
        message: 'Erreur lors de la suppression des méthodes de paiement.',
        type: 'error',
      })
      return false
    }
  }

  return {
    methodsData,
    methodsError,
    methodsLoading,
    fetchMethods,
    createLoading,
    updateLoading,
    deleteLoading,
    handleCreateSubmit,
    handleUpdateSubmit,
    handleDeleteSingle,
    handleDeleteMultiple,
  }
}
