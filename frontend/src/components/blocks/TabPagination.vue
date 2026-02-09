<script setup lang="ts">
import { ref, watch, computed, defineProps, defineEmits } from 'vue'

const props = defineProps<{
  totalItems: number
  perPageOptions?: number[]
  modelValue: number
  perPage?: number
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: number): void
  (e: 'update:perPage', value: number): void
}>()

const currentPage = ref(props.modelValue)
const itemsPerPage = ref(props.perPage ?? 15)

const totalPages = computed(() => Math.ceil(props.totalItems / itemsPerPage.value))

watch(
  () => props.modelValue,
  (val) => (currentPage.value = val),
)
watch(
  () => props.perPage,
  (val) => {
    if (val) itemsPerPage.value = val
  },
)

watch(currentPage, (val) => emit('update:modelValue', val))
watch(itemsPerPage, (val) => {
  emit('update:perPage', val)
  currentPage.value = 1
})

// Computed pagination with ellipsis logic
const pages = computed(() => {
  const pagesToShow: (number | string)[] = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 6) {
    for (let i = 1; i <= total; i++) {
      pagesToShow.push(i)
    }
    return pagesToShow
  }

  const showLeftDots = current > 4
  const showRightDots = current < total - 3

  pagesToShow.push(1)

  if (showLeftDots) {
    pagesToShow.push('...')
  }

  const start = Math.max(2, current - 1)
  const end = Math.min(total - 1, current + 1)

  for (let i = start; i <= end; i++) {
    pagesToShow.push(i)
  }

  if (showRightDots) {
    pagesToShow.push('...')
  }

  pagesToShow.push(total)

  return pagesToShow
})

function goToPage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}
</script>

<template>
  <div
    class="flex flex-col sm:flex-row gap-6 sm:items-center sm:justify-between border-t border-border pt-3 mt-3"
  >
    <div class="max-sm:flex-1 gap-2 flex-wrap flex items-center">
      <div
        class="relative overflow-hidden flex max-sm:w-full items-center gap-2 px-2 rounded-lg border border-border bg-white"
      >
        <label
          for="per_page"
          class="max-sm:flex-1 flex text-sm text-foreground-muted py-1.5 pr-3 border-r border-border"
        >
          Par page
        </label>
        <select
          id="per_page"
          class="bg-transparent py-1.5 border-none outline-none"
          v-model.number="itemsPerPage"
        >
          <option v-for="option in perPageOptions ?? [15, 25, 50]" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
      </div>
    </div>

    <!-- Pagination Navigation -->
    <div class="max-sm:flex-1">
      <nav aria-label="Page navigation example">
        <ul class="inline-flex gap-1 text-sm">
          <li>
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="flex items-center justify-center px-3 h-9 ms-0 leading-tight text-fg-muted bg-white border border-e-0 border-border rounded-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50"
            >
              Précédent
            </button>
          </li>
          <li v-for="page in pages" :key="page">
            <button
              v-if="page !== '...'"
              @click="goToPage(page as number)"
              :class="[
                'flex items-center justify-center px-3 size-9 leading-tight border border-border rounded-lg',
                currentPage === page
                  ? 'text-primary-50 bg-primary hover:bg-primary-700 hover:text-white'
                  : 'text-fg-muted bg-white hover:bg-gray-100 hover:text-gray-700',
              ]"
            >
              {{ page }}
            </button>
            <span v-else class="flex items-center justify-center px-3 size-9 text-fg-muted">
              ...
            </span>
          </li>
          <li>
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="flex items-center justify-center px-3 h-9 leading-tight text-fg-muted bg-white border border-border rounded-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50"
            >
              Suivant
            </button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>
