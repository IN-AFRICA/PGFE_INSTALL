import { ref, computed } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import * as z from 'zod'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { usePutApi } from '@/composables/usePutApi'
import { useSearch } from '@/composables/useSearch'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import { normalizeApiResponse, extractSuccessMessage, isApiSuccess } from './utils'
import type { CategorieComptable, ApiResponse } from './types'

// ==================== Validation Schema ====================
export const categorieComptableSchema = z.object({
  name: z
    .string({ required_error: 'Le nom est requis' })
    .min(1, 'Le nom est requis')
    .max(255, 'Le nom ne doit pas dépasser 255 caractères'),
})

// ==================== Composable ====================
export function useCategorieComptable() {
  // API Composables
  const {
    data: categoriesData,
    error: categoriesError,
    loading: categoriesLoading,
    fetchData: fetchCategories,
    meta,
  } = useGetApi<CategorieComptable[]>(API_ROUTES.GET_CATEGORIE_COMPTE)

  const {
    postData: createCategorie,
    response: createCategorieResponse,
    error: createCategorieError,
    loading: creatingCategorie,
  } = usePostApi<ApiResponse>()

  const {
    putData: updateCategorie,
    response: updateCategorieResponse,
    error: updateCategorieError,
    loading: updatingCategorie,
  } = usePutApi<ApiResponse>()

  const {
    deleteItem: deleteCategorie,
    deleteResponse: deleteCategorieResponse,
    errorDelete: deleteCategorieError,
    deleting: deletingCategorie,
  } = useDeleteApi<ApiResponse>()

  // Local State
  const isCreateDialogOpen = ref(false)
  const isEditDialogOpen = ref(false)
  const editingCategorie = ref<CategorieComptable | null>(null)

  // Pagination State
  const page = ref(1)
  const perPageCount = ref(15)

  // Search with server-side fetching
  const { query: searchQuery } = useSearch((params: { search: string }) => {
    page.value = 1
    fetchCategories({ page: page.value, per_page: perPageCount.value, search: params.search })
  }, 500)

  // Form
  const { handleSubmit, resetForm, setValues } = useForm({
    validationSchema: toTypedSchema(categorieComptableSchema),
    name: 'categorieForm',
  })

  // Computed
  const categories = computed(() =>
    normalizeApiResponse<CategorieComptable>(categoriesData.value, 'categories'),
  )

  const filteredCategories = computed(() => categories.value)

  const total = computed(() => meta?.value?.total ?? categories.value.length)

  // Helper to refetch with current params
  const refetchWithParams = () => {
    return fetchCategories({
      page: page.value,
      per_page: perPageCount.value,
      search: searchQuery.value,
    })
  }

  // CRUD Operations
  const onSubmitCreate = async (values: Record<string, unknown>) => {
    try {
      await createCategorie(API_ROUTES.CREATE_CATEGORIE_COMPTE, values)

      if (createCategorieError.value) {
        showCustomToast({ message: createCategorieError.value, type: 'error' })
        return
      }

      if (isApiSuccess(createCategorieResponse.value, createCategorieError.value)) {
        const message = extractSuccessMessage(
          createCategorieResponse.value,
          'Catégorie créée avec succès',
        )
        showCustomToast({ message, type: 'success' })
        closeCreateDialog()
        await refetchWithParams()
        eventBus.emit('categorieComptableUpdated' as never)
      }
    } catch (err) {
      console.error('Erreur lors de la création:', err)
      showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
    }
  }

  const onSubmitEdit = handleSubmit(async (values) => {
    if (!editingCategorie.value) return

    try {
      await updateCategorie(API_ROUTES.UPDATE_CATEGORIE_COMPTE(editingCategorie.value.id), values)

      if (updateCategorieError.value) {
        showCustomToast({ message: updateCategorieError.value, type: 'error' })
        return
      }

      if (isApiSuccess(updateCategorieResponse.value, updateCategorieError.value)) {
        const message = extractSuccessMessage(
          updateCategorieResponse.value,
          'Catégorie modifiée avec succès',
        )
        showCustomToast({ message, type: 'success' })
        closeEditDialog()
        await refetchWithParams()
        eventBus.emit('categorieComptableUpdated' as never)
      }
    } catch (err) {
      console.error('Erreur lors de la modification:', err)
      showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
    }
  })

  const handleDelete = async (id: number) => {
    try {
      await deleteCategorie(API_ROUTES.DELETE_CATEGORIE_COMPTE(id))

      if (deleteCategorieError.value) {
        showCustomToast({ message: deleteCategorieError.value, type: 'error' })
        return
      }

      if (isApiSuccess(deleteCategorieResponse.value, deleteCategorieError.value)) {
        const message = extractSuccessMessage(
          deleteCategorieResponse.value,
          'Catégorie supprimée avec succès',
        )
        showCustomToast({ message, type: 'success' })
        await refetchWithParams()
        eventBus.emit('categorieComptableUpdated' as never)
      }
    } catch (err) {
      console.error('Erreur lors de la suppression:', err)
      showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
    }
  }

  // Dialog Management
  function openEditDialog(item: CategorieComptable) {
    editingCategorie.value = item
    setValues({ name: item.name })
    isEditDialogOpen.value = true
  }

  function closeCreateDialog() {
    resetForm()
    isCreateDialogOpen.value = false
  }

  function closeEditDialog() {
    resetForm()
    editingCategorie.value = null
    isEditDialogOpen.value = false
  }

  return {
    // State
    searchQuery,
    isCreateDialogOpen,
    isEditDialogOpen,
    editingCategorie,

    // Pagination
    page,
    perPageCount,
    total,
    meta,

    // Data
    categories,
    filteredCategories,
    categoriesError,
    categoriesLoading,

    // Loading states
    creatingCategorie,
    updatingCategorie,
    deletingCategorie,

    // Methods
    fetchCategories: refetchWithParams,
    onSubmitCreate,
    onSubmitEdit,
    handleDelete,
    openEditDialog,
    closeCreateDialog,
    closeEditDialog,
  }
}
