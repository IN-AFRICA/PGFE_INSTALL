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
import type { ClassComptable, ApiResponse } from './types'

// ==================== Validation Schema ====================
export const classComptableSchema = z.object({
  name: z
    .string({ required_error: 'Le nom est requis' })
    .min(1, 'Le nom est requis')
    .max(255, 'Le nom ne doit pas dépasser 255 caractères'),
  code: z
    .string({ required_error: 'Le code est requis' })
    .min(1, 'Le code est requis')
    .max(50, 'Le code ne doit pas dépasser 50 caractères'),
  user_id: z.number().nullable().optional(),
})

// ==================== Composable ====================
export function useClassComptable() {
  // API Composables
  const {
    data: classesData,
    error: classesError,
    loading: classesLoading,
    fetchData: fetchClasses,
    meta,
  } = useGetApi<ClassComptable[]>(API_ROUTES.GET_CLASS_COMPTE)

  const {
    postData: createClass,
    response: createClassResponse,
    error: createClassError,
    loading: creatingClass,
  } = usePostApi<ApiResponse>()

  const {
    putData: updateClass,
    response: updateClassResponse,
    error: updateClassError,
    loading: updatingClass,
  } = usePutApi<ApiResponse>()

  const {
    deleteItem: deleteClass,
    deleteResponse: deleteClassResponse,
    errorDelete: deleteClassError,
    deleting: deletingClass,
  } = useDeleteApi<ApiResponse>()

  // Local State
  const isCreateDialogOpen = ref(false)
  const isEditDialogOpen = ref(false)
  const editingClass = ref<ClassComptable | null>(null)

  // Pagination State
  const page = ref(1)
  const perPageCount = ref(15)

  // Search with server-side fetching
  const { query: searchQuery } = useSearch((params: { search: string }) => {
    page.value = 1
    fetchClasses({ page: page.value, per_page: perPageCount.value, search: params.search })
  }, 500)

  // Form
  const { handleSubmit, resetForm, setValues } = useForm({
    validationSchema: toTypedSchema(classComptableSchema),
    name: 'classForm',
  })

  // Computed
  const classes = computed(() => normalizeApiResponse<ClassComptable>(classesData.value, 'classes'))

  const filteredClasses = computed(() => classes.value)

  const total = computed(() => meta?.value?.total ?? classes.value.length)

  // Helper to refetch with current params
  const refetchWithParams = () => {
    return fetchClasses({
      page: page.value,
      per_page: perPageCount.value,
      search: searchQuery.value,
    })
  }

  // CRUD Operations
  const onSubmitCreate = async (values: Record<string, unknown>) => {
    try {
      await createClass(API_ROUTES.CREATE_CLASS_COMPTE, values)

      if (createClassError.value) {
        showCustomToast({ message: createClassError.value, type: 'error' })
        return
      }

      if (isApiSuccess(createClassResponse.value, createClassError.value)) {
        const message = extractSuccessMessage(
          createClassResponse.value,
          'Classe comptable créée avec succès',
        )
        showCustomToast({ message, type: 'success' })
        closeCreateDialog()
        await refetchWithParams()
        eventBus.emit('classComptableUpdated' as never)
      } else {
        const errorMessage =
          createClassError.value ||
          (typeof createClassResponse.value === 'object' && createClassResponse.value
            ? (createClassResponse.value as unknown as Record<string, unknown>)?.message
            : null) ||
          'Erreur lors de la création de la classe comptable'
        showCustomToast({ message: String(errorMessage), type: 'error' })
      }
    } catch (err) {
      console.error('Erreur lors de la création:', err)
      showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
    }
  }

  const onSubmitEdit = handleSubmit(async (values) => {
    if (!editingClass.value) return

    try {
      await updateClass(API_ROUTES.UPDATE_CLASS_COMPTE(editingClass.value.id), values)

      if (updateClassError.value) {
        showCustomToast({ message: updateClassError.value, type: 'error' })
        return
      }

      if (isApiSuccess(updateClassResponse.value, updateClassError.value)) {
        const message = extractSuccessMessage(
          updateClassResponse.value,
          'Classe comptable modifiée avec succès',
        )
        showCustomToast({ message, type: 'success' })
        closeEditDialog()
        await refetchWithParams()
        eventBus.emit('classComptableUpdated' as never)
      }
    } catch (err) {
      console.error('Erreur lors de la modification:', err)
      showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
    }
  })

  const handleDelete = async (id: number) => {
    try {
      await deleteClass(API_ROUTES.DELETE_CLASS_COMPTE(id))

      if (deleteClassError.value) {
        showCustomToast({ message: deleteClassError.value, type: 'error' })
        return
      }

      if (isApiSuccess(deleteClassResponse.value, deleteClassError.value)) {
        const message = extractSuccessMessage(
          deleteClassResponse.value,
          'Classe comptable supprimée avec succès',
        )
        showCustomToast({ message, type: 'success' })
        await refetchWithParams()
        eventBus.emit('classComptableUpdated' as never)
      }
    } catch (err) {
      console.error('Erreur lors de la suppression:', err)
      showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
    }
  }

  // Dialog Management
  function openEditDialog(item: ClassComptable) {
    // set editing item; the dialog will use this to populate its own form
    editingClass.value = item
    isEditDialogOpen.value = true
  }

  function closeCreateDialog() {
    resetForm()
    isCreateDialogOpen.value = false
  }

  function closeEditDialog() {
    resetForm()
    editingClass.value = null
    isEditDialogOpen.value = false
  }

  return {
    // State
    searchQuery,
    isCreateDialogOpen,
    isEditDialogOpen,
    editingClass,

    // Pagination
    page,
    perPageCount,
    total,
    meta,

    // Data
    classes,
    filteredClasses,
    classesError,
    classesLoading,

    // Loading states
    creatingClass,
    updatingClass,
    deletingClass,

    // Methods
    fetchClasses: refetchWithParams,
    onSubmitCreate,
    onSubmitEdit,
    handleDelete,
    openEditDialog,
    closeCreateDialog,
    closeEditDialog,
  }
}
