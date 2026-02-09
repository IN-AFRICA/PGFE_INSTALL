<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import CustomDatePicker from '@/components/ui/CustomDatePicker.vue'

const isOpen = ref(false)
const code = ref('')
// Calendar dates (yyyy-mm-dd from native date input)
const startDate = ref('')
const endDate = ref('')
const submitting = ref(false)

const emit = defineEmits<{
  (e: 'created'): void
}>()

const { postData, response, error } = usePostApi<any>()

// Extract YYYY-MM-DD from possible inputs (either 'YYYY-MM-DD' or 'YYYY-MM-DDTHH:mm[:ss]')
function toDateOnly(d: string) {
  const raw = (d || '').trim()
  if (!raw) return ''
  // If includes time, take only the date part before 'T'
  const datePart = raw.includes('T') ? raw.split('T')[0] : raw
  const reDate = /^\d{4}-\d{2}-\d{2}$/
  return reDate.test(datePart) ? datePart : ''
}

function parseYmdToNumber(d: string): number {
  // Convert YYYY-MM-DD into comparable number YYYYMMDD
  const parts = d.split('-')
  if (parts.length !== 3) return NaN
  return Number(parts.join(''))
}

async function onSubmit() {
  if (!code.value.trim()) {
    showCustomToast({ message: 'Le code est requis', type: 'error' })
    return
  }
  const startDateOnly = toDateOnly(startDate.value)
  if (!/^\d{4}-\d{2}-\d{2}$/.test(startDateOnly)) {
    showCustomToast({ message: 'La date de début est invalide (YYYY-MM-DD)', type: 'error' })
    return
  }
  const endDateOnly = toDateOnly(endDate.value)
  if (!/^\d{4}-\d{2}-\d{2}$/.test(endDateOnly)) {
    showCustomToast({ message: 'La date de fin est invalide (YYYY-MM-DD)', type: 'error' })
    return
  }
  // Compare dates (YYYYMMDD numeric comparison)
  const sNum = parseYmdToNumber(startDateOnly)
  const eNum = parseYmdToNumber(endDateOnly)
  if (isNaN(sNum) || isNaN(eNum) || eNum < sNum) {
    showCustomToast({
      message: 'La fin doit être supérieure ou égale au début (date)',
      type: 'error',
    })
    return
  }

  const payload = {
    code: code.value.trim(),
    start_date: startDateOnly,
    end_date: endDateOnly,
  }

  try {
    submitting.value = true
    await postData(API_ROUTES.CREATE_BUDGET, payload)

    if (error.value) {
      showCustomToast({ message: String(error.value), type: 'error' })
      return
    }

    const msg = (response.value?.message as string) || 'Budget créé avec succès'
    showCustomToast({ message: msg, type: 'success' })
    // Reset and close
    code.value = ''
    startDate.value = ''
    endDate.value = ''
    isOpen.value = false
    emit('created')
  } catch (e: any) {
    showCustomToast({ message: 'Erreur lors de la création du budget', type: 'error' })
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Créer un budget </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[440px]">
      <DialogHeader>
        <DialogTitle>Ajouter un budget</DialogTitle>
      </DialogHeader>
      <form class="grid grid-cols-1 gap-4 py-4" @submit.prevent="onSubmit">
        <div class="flex flex-col space-y-1.5">
          <Label for="budget_code" class="text-sm font-medium">
            Code
            <SpanRequired />
          </Label>
          <Input
            v-model="code"
            type="text"
            id="budget_code"
            name="budget_code"
            placeholder="Ex: BUD2025"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all"
          />
        </div>
        <div class="flex flex-col space-y-1.5">
          <Label for="start_date" class="text-sm font-medium">
            Début budgétaire
            <SpanRequired />
          </Label>
          <CustomDatePicker v-model="startDate" :enable-time="false" />
        </div>
        <div class="flex flex-col space-y-1.5">
          <Label for="end_date" class="text-sm font-medium">
            Fin du budget
            <SpanRequired />
          </Label>
          <CustomDatePicker v-model="endDate" :enable-time="false" />
        </div>
        <div class="mt-1 pb-2">
          <p class="text-sm text-foreground-muted">* Les champs marqués sont obligatoires</p>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button size="sm" class="h-9" variant="outline" type="button" @click="isOpen = false">
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Annuler
          </Button>
          <Button size="sm" class="h-9" type="submit" :disabled="submitting">
            <span class="iconify flex hugeicons--floppy-disk mr-1"></span>
            <span v-if="!submitting">Enregistrer</span>
            <span v-else>Enregistrement...</span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
