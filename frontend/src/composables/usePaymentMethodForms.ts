import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { paymentMethodSchema } from '@/types/PaymentMethodTypes'
import type { PaymentMethod } from '@/types/PaymentMethodTypes'

export function usePaymentMethodForms() {
  const isCreateDialogOpen = ref(false)
  const isEditDialogOpen = ref(false)
  const editingMethod = ref<PaymentMethod | null>(null)

  const { handleSubmit: handleCreateSubmit, resetForm: resetCreateForm } = useForm({
    validationSchema: toTypedSchema(paymentMethodSchema),
    initialValues: {
      name: '',
      code: '',
    },
  })

  const {
    handleSubmit: handleEditSubmit,
    resetForm: resetEditForm,
    setFieldValue: setEditFieldValue,
  } = useForm({
    validationSchema: toTypedSchema(paymentMethodSchema),
  })

  const openCreateDialog = () => {
    resetCreateForm()
    isCreateDialogOpen.value = true
  }

  const openEditDialog = (method: PaymentMethod) => {
    editingMethod.value = method
    setEditFieldValue('name', method.name)
    setEditFieldValue('code', method.code)
    isEditDialogOpen.value = true
  }

  const closeCreateDialog = () => {
    isCreateDialogOpen.value = false
  }

  const closeEditDialog = () => {
    isEditDialogOpen.value = false
    resetEditForm()
    editingMethod.value = null
  }

  return {
    isCreateDialogOpen,
    isEditDialogOpen,
    editingMethod,
    handleCreateSubmit,
    resetCreateForm,
    handleEditSubmit,
    resetEditForm,
    setEditFieldValue,
    openCreateDialog,
    openEditDialog,
    closeCreateDialog,
    closeEditDialog,
  }
}
