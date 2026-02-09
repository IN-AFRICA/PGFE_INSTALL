<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import CreatePaymentMethodDialog from '@/components/payment-methods/CreatePaymentMethodDialog.vue'
import EditPaymentMethodDialog from '@/components/payment-methods/EditPaymentMethodDialog.vue'
import PaymentMethodsTable from '@/components/payment-methods/PaymentMethodsTable.vue'
import PaymentMethodsActionBar from '@/components/payment-methods/PaymentMethodsActionBar.vue'

import { usePaymentMethodsApi } from '@/composables/usePaymentMethodsApi'
import { usePaymentMethodSelection } from '@/composables/usePaymentMethodSelection'
import type { PaymentMethod, PaymentMethodFormData } from '@/types/PaymentMethodTypes'

// Composables
const apiComposable = usePaymentMethodsApi()
const searchQuery = ref('')

// Dialog state
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingMethod = ref<PaymentMethod | null>(null)

// Filtered methods computed property
const filteredMethods = computed<PaymentMethod[]>(() => {
  let methods: PaymentMethod[] = []

  if (apiComposable.methodsData.value) {
    if (Array.isArray(apiComposable.methodsData.value)) {
      methods = apiComposable.methodsData.value
    } else if (
      'data' in apiComposable.methodsData.value &&
      Array.isArray((apiComposable.methodsData.value as Record<string, unknown>).data)
    ) {
      methods = (apiComposable.methodsData.value as Record<string, unknown>).data as PaymentMethod[]
    } else if (
      'methods' in apiComposable.methodsData.value &&
      Array.isArray((apiComposable.methodsData.value as Record<string, unknown>).methods)
    ) {
      methods = (apiComposable.methodsData.value as Record<string, unknown>)
        .methods as PaymentMethod[]
    }
  }

  if (!searchQuery.value) {
    return methods
  }

  const query = searchQuery.value.toLowerCase()
  return methods.filter(
    (method) =>
      method.name.toLowerCase().includes(query) || method.code.toLowerCase().includes(query),
  )
})

// Selection composable
const selectionComposable = usePaymentMethodSelection(() => filteredMethods.value)

// Handle create form submission
const handleCreateSubmit = async (data: PaymentMethodFormData) => {
  const success = await apiComposable.handleCreateSubmit(data)
  if (success) {
    isCreateDialogOpen.value = false
  }
}

// Handle edit form submission
const handleEditSubmit = async (data: PaymentMethodFormData) => {
  if (!editingMethod.value) return

  const success = await apiComposable.handleUpdateSubmit(editingMethod.value.id, data)
  if (success) {
    isEditDialogOpen.value = false
    editingMethod.value = null
  }
}

// Open edit dialog
const openEditDialog = (method: PaymentMethod) => {
  editingMethod.value = method
  isEditDialogOpen.value = true
}

// Handle single delete
const handleDelete = async (methodId: number) => {
  await apiComposable.handleDeleteSingle(methodId)
}

// Handle multiple delete
const handleDeleteSelected = async () => {
  const success = await apiComposable.handleDeleteMultiple(
    selectionComposable.selectedMethods.value,
  )
  if (success) {
    selectionComposable.clearSelection()
  }
}

// Load data on mount
onMounted(async () => {
  await apiComposable.fetchMethods()
})
</script>

<template>
  <ComptaLayout
    activeBread="Méthodes de Paiement"
    active-tag-name="methodes-paiement"
    group="frais"
  >
    <BoxPanelWrapper>
      <!-- Action Bar -->
      <PaymentMethodsActionBar
        :search-query="searchQuery"
        :selected-count="selectionComposable.selectedMethods.value.length"
        :is-deleting="apiComposable.deleteLoading.value"
        @update:search-query="searchQuery = $event"
        @delete-selected="handleDeleteSelected"
      >
        <template #create>
          <CreatePaymentMethodDialog
            :is-open="isCreateDialogOpen"
            :is-loading="apiComposable.createLoading.value"
            @update:is-open="isCreateDialogOpen = $event"
            @submit="handleCreateSubmit"
          />
        </template>
      </PaymentMethodsActionBar>

      <!-- Payment Methods Table -->
      <PaymentMethodsTable
        :methods="filteredMethods"
        :is-loading="apiComposable.methodsLoading.value"
        :error="apiComposable.methodsError.value"
        :selected-items="selectionComposable.selectedItems.value"
        :is-all-selected="selectionComposable.isAllSelected.value"
        @edit="openEditDialog"
        @delete="handleDelete"
        @toggle-selection="selectionComposable.toggleSelection"
        @toggle-select-all="selectionComposable.toggleSelectAll"
      />

      <!-- Edit Dialog -->
      <EditPaymentMethodDialog
        :is-open="isEditDialogOpen"
        :is-loading="apiComposable.updateLoading.value"
        :method="editingMethod"
        @update:is-open="isEditDialogOpen = $event"
        @submit="handleEditSubmit"
      />
    </BoxPanelWrapper>
  </ComptaLayout>
</template>
