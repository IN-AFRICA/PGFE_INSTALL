<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { toTypedSchema } from '@vee-validate/zod'
import { useField, useForm } from 'vee-validate'
import z from 'zod'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'

const props = defineProps<{
  open: boolean
}>()

const emit = defineEmits(['update:open', 'created'])

const { loading, error, response, postData, success } = usePostApi()

const schemaForm = z.object({
  name: z
    .string({ required_error: 'Veuillez saisir le nom du parent' })
    .min(2, 'Le nom doit avoir au moins 2 caractères')
    .max(100),
})

const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schemaForm),
})

const { value: name, errorMessage: nameError } = useField<string>('name')

const onSubmit = handleSubmit(async (values) => {
  await postData(API_ROUTES.CREATE_PARENT, values)

  if (success.value) {
    showCustomToast({
      message: response.value?.message || 'Parent créé avec succès',
      type: 'success',
    })
    emit('created', response.value?.data)
    resetForm()
    emit('update:open', false)
    eventBus.emit('parentCreated')
  } else {
    showCustomToast({
      message: error.value || 'Erreur lors de la création du parent',
      type: 'error',
    })
  }
})

const handleCancel = () => {
  resetForm()
  emit('update:open', false)
}
</script>

<template>
  <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Nouveau Parent</DialogTitle>
        <DialogDescription> Enregistrer un nouveau parent dans le système. </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4 py-4">
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium"> Nom complet <SpanRequired /> </Label>
            <Input
              id="name"
              v-model="name"
              placeholder="Ex: Kasongo Mwenda"
              class="h-10 border border-gray-200/40 bg-white transition-all"
            />
            <span v-if="nameError" class="text-xs text-red-500">{{ nameError }}</span>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button
            size="sm"
            class="h-9"
            variant="outline"
            type="button"
            @click="handleCancel"
            :disabled="loading"
          >
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Annuler
          </Button>

          <Button size="sm" class="h-9" type="submit" :disabled="loading">
            <span v-if="!loading" class="flex items-center gap-2">
              <span class="iconify hugeicons--floppy-disk mr-1"></span>
              Enregistrer
            </span>
            <span v-else class="flex items-center gap-2">
              <IconifySpinner size="lg" />
              Enregistrement...
            </span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
