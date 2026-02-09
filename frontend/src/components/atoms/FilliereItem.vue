<template>
  <li
    class="group flex items-center justify-between px-4 py-3 border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition-colors"
  >
    <div class="flex-1">
      <h3 class="text-sm font-medium text-foreground-muted">{{ title }}</h3>
    </div>

    <button
      class="flex ml-2 group-hover:hidden rounded-full size-8 items-center justify-center hover:bg-gray-100 transition"
      @click="toggleActions"
      aria-label="Plus d'actions"
    >
      <span class="iconify hugeicons--more-vertical-circle-01" aria-hidden="true"></span>
    </button>

    <div class="hidden group-hover:flex items-center gap-2 ml-2">
      <Button
        variant="ghost"
        size="icon"
        @click="handleEdit"
        class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
        aria-label="Modifier"
      >
        <span class="iconify hugeicons--edit-02" aria-hidden="true"></span>
      </Button>
      <AlertMessage
        action="danger"
        title="Supprimer une filière"
        :message="` Vous etes sur le point de supprimer la filière '${title}'!! Etes-vous sûr de continuer???`"
      >
        <template #trigger>
          <Button
            variant="ghost"
            size="icon"
            class="size-8 justify-center text-gray-500 hover:text-red-600 hover:bg-red-50"
            aria-label="Supprimer"
          >
            <span class="iconify hugeicons--delete-02" aria-hidden="true"></span>
          </Button>
        </template>
        <template #confirm-action-button>
          <Button
            variant="destructive"
            size="sm"
            @click="handleDelete"
            aria-label="Supprimer"
            class="h-10 px-4"
          >
            Oui, Supprimer
          </Button>
        </template>
      </AlertMessage>
    </div>
  </li>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import AlertMessage from '../modals/AlertMessage.vue'

const props = defineProps<{
  title: string
  id: string | number
}>()

const emit = defineEmits<{
  edit: [id: string | number]
  delete: [id: string | number]
}>()

const showActions = ref(false)

const toggleActions = () => {
  showActions.value = !showActions.value
}

const handleEdit = () => {
  emit('edit', props.id)
  showActions.value = false
}

const handleDelete = () => {
  emit('delete', props.id)
  showActions.value = false
}
</script>
