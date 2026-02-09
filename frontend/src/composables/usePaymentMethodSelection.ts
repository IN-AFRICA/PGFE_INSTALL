import { ref, computed } from 'vue'
import type { PaymentMethod } from '@/types/PaymentMethodTypes'

export function usePaymentMethodSelection(filteredMethods: () => PaymentMethod[]) {
  const selectedItems = ref<number[]>([])

  const selectedMethods = computed(() => {
    const methods = filteredMethods() || []
    if (!Array.isArray(methods)) {
      console.warn('filteredMethods is not an array:', methods)
      return []
    }
    return methods.filter((method) => selectedItems.value.includes(method.id))
  })

  const isAllSelected = computed(() => {
    const methods = filteredMethods() || []
    return methods.length > 0 && selectedItems.value.length === methods.length
  })

  const toggleSelection = (methodId: number) => {
    const index = selectedItems.value.indexOf(methodId)
    if (index > -1) {
      selectedItems.value.splice(index, 1)
    } else {
      selectedItems.value.push(methodId)
    }
  }

  const toggleSelectAll = () => {
    if (isAllSelected.value) {
      selectedItems.value = []
    } else {
      selectedItems.value = filteredMethods().map((method) => method.id)
    }
  }

  const clearSelection = () => {
    selectedItems.value = []
  }

  return {
    selectedItems,
    selectedMethods,
    isAllSelected,
    toggleSelection,
    toggleSelectAll,
    clearSelection,
  }
}
