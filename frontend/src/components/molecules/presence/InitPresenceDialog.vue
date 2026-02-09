<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
} from '@/components/ui/alert-dialog'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const props = defineProps<{
  open: boolean
  loading?: boolean
  defaultDate?: string
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
  confirm: [date: string]
}>()

// Helper pour obtenir la date du jour
const getTodayDate = () => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const modalDate = ref(props.defaultDate || getTodayDate())

const handleConfirm = () => {
  emit('confirm', modalDate.value)
}

const handleClose = () => {
  emit('update:open', false)
}
</script>

<template>
  <AlertDialog :open="open" @update:open="(v) => emit('update:open', v)">
    <AlertDialogContent class="max-w-md">
      <AlertDialogHeader>
        <AlertDialogTitle>Initialiser la fiche de présence</AlertDialogTitle>
        <AlertDialogDescription>
          Sélectionnez la date pour initialiser la fiche de présence des personnels académiques.
        </AlertDialogDescription>
      </AlertDialogHeader>

      <div class="space-y-4 py-4">
        <div class="space-y-2">
          <Label>Date</Label>
          <Input type="date" v-model="modalDate" class="h-10 bg-white" />
          <p class="text-sm text-gray-500">
            Si aucune date n'est sélectionnée, la date du jour sera utilisée.
          </p>
        </div>
      </div>

      <AlertDialogFooter>
        <AlertDialogCancel @click="handleClose">Annuler</AlertDialogCancel>
        <Button @click="handleConfirm" :disabled="loading">
          <span v-if="!loading" class="flex items-center gap-2">Initialiser</span>
          <span v-else class="flex items-center gap-2">
            <IconifySpinner size="sm" />
            <span>Initialisation...</span>
          </span>
        </Button>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
