<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import { useForm, useField } from 'vee-validate'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { classComptableSchema } from './useClassComptable'

import { watch } from 'vue'
import type { ClassComptable } from './types'

interface Props {
  open: boolean
  loading: boolean
  editingClass?: ClassComptable | null
}

const emit = defineEmits(['update:open', 'submit', 'close'])

const props = defineProps<Props>()

const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(classComptableSchema),
  initialValues: {
    code: '',
    name: '',
    user_id: null as number | null,
  },
})

const { value: code, errorMessage: codeError } = useField<string>('code')
const { value: name, errorMessage: nameError } = useField<string>('name')

watch(
  () => props.editingClass,
  (value) => {
    resetForm({
      values: {
        code: value?.code ?? '',
        name: value?.name ?? '',
        user_id: value?.user_id ?? null,
      },
    })
  },
  { immediate: true },
)

const onSubmit = handleSubmit((values) => {
  emit('submit', values)
})
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent
      class="sm:max-w-[540px]"
      @pointer-down-outside="emit('close')"
      @escape-key-down="emit('close')"
    >
      <DialogHeader>
        <DialogTitle>Modifier la classe comptable</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="onSubmit" class="space-y-4">
        <div class="flex flex-col space-y-1.5">
          <Label for="edit_code">Code <span class="text-red-500">*</span></Label>
          <Input v-model="code" id="edit_code" placeholder="Ex: CL001" maxlength="50" />
          <span v-if="codeError" class="text-sm text-red-500">{{ codeError }}</span>
        </div>
        <div class="flex flex-col space-y-1.5">
          <Label for="edit_name">Nom <span class="text-red-500">*</span></Label>
          <Input
            v-model="name"
            id="edit_name"
            placeholder="Ex: Comptes de capitaux"
            maxlength="255"
          />
          <span v-if="nameError" class="text-sm text-red-500">{{ nameError }}</span>
        </div>
        <div class="mt-1 pb-2">
          <p class="text-sm text-foreground-muted">* Les champs marqués sont obligatoires</p>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button type="button" size="sm" variant="outline" @click="emit('close')">
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Annuler
          </Button>
          <Button type="submit" size="sm" :disabled="loading">
            <span class="iconify flex hugeicons--floppy-disk mr-1"></span>
            {{ loading ? 'Modification...' : 'Modifier' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
