<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { ref } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm, useField } from 'vee-validate'
import { z } from 'zod'
import { eventBus } from '@/utils/eventBus.ts'
import { usePostApi } from '@/composables/usePostApi.ts'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
const open = ref(false)
const { loading, error, response, postData, success } = usePostApi()

const schemaForm = z.object({
  name: z
    .string({ required_error: 'Veuillez saisir le nom du pays' })
    .min(2, 'Le nom doit contenir au moins 2 caractères')
    .max(100),
})

//Validation
const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schemaForm),
})
const { value: name, errorMessage: nameError } = useField<string>('name')

const onSubmit = handleSubmit(async (values) => {
  const playload = { ...values, name: values.name.trim() }
  await postData(API_ROUTES.CREATE_COUNTRY, playload)

  if (error.value) {
    showCustomToast({ message: error.value, type: 'error' })
    return
  }
  if (success.value) {
    eventBus.emit('countryUpdated')
    showCustomToast({
      message: response.value?.message || 'Pays ajouté avec succès',
      type: 'success',
    })
    resetForm()
    open.value = false
  }
})
</script>
<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Ajouter un pays </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[400px]">
      <DialogHeader>
        <DialogTitle>Ajouter un Pays</DialogTitle>
        <DialogDescription> Enregistrer un pays </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4 py-4">
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium"> Nom du pays </Label>
            <Input
              type="text"
              id="name"
              name="name"
              v-model="name"
              placeholder="Entrez le nom du pays"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
              :disabled="loading"
            />
            <span v-if="nameError" class="text-red-500 text-xs mt-1">{{ nameError }}</span>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button
            size="sm"
            class="h-9"
            variant="outline"
            type="button"
            @click="
              () => {
                resetForm()
                open = false
              }
            "
            :disabled="loading"
          >
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Annuler
          </Button>
          <Button size="sm" class="h-9" type="submit" :disabled="loading">
            <span v-if="!loading" class="flex items-center gap-2">
              <span class="iconify hugeicons--floppy-disk mr-1"></span>
              <span>Enregistrer</span>
            </span>
            <span v-else class="flex items-center gap-2">
              <IconifySpinner size="lg" />
              <span>Enregistrement en cours...</span>
            </span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
