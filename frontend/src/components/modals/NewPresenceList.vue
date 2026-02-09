<script setup lang="ts">
import { Button } from '@/components/ui/button'
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
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useField, useForm } from 'vee-validate'
import z from 'zod'
import { ref } from 'vue'
// Récupère les filières du serveur
const { loading, error, response, postData, success } = usePostApi()
const open = ref(false)
//Create form schema
const schemaForm = z.object({
  classroom_id: z.number().min(1, 'la classe est réquise'),
  date: z.string().min(1, 'la date est réquise'),
})

//Validation
const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schemaForm),
  initialValues: {
    date: new Date().toISOString().split('T')[0], // Date d'aujourd'hui par défaut
  },
})
const { value: classroom_id, errorMessage: classroomIdError } = useField<number>('classroom_id')
const { value: date, errorMessage: dateError } = useField<string>('date')

const onSubmit = handleSubmit(async (values) => {
  await postData(API_ROUTES.CREATE_STUDENT_PRESENCE, values)
  if (error.value) {
    showCustomToast({
      message: error.value,
      type: 'error',
    })
    return
  } else if (success.value) {
    showCustomToast({
      message: response.value?.message || 'Présence créée avec succès',
      type: 'success',
    })

    // Émettre les paramètres pour synchroniser StudentsPresence
    eventBus.emit('presenceCreated', {
      classroom_id: values.classroom_id,
      date: values.date,
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
        <span class="hidden sm:flex"> Nouvelle presence </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[540px]">
      <DialogHeader>
        <DialogTitle>Nouvelle presence</DialogTitle>
        <DialogDescription> Enregistrer une nouvelle presence </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4 py-4">
          <div class="flex flex-col space-y-1.5">
            <Label class="text-foreground-muted font-light" for="classroom">Classe</Label>
            <div class="space-y-1.5">
              <ListClassRoom v-model="classroom_id" />
            </div>
            <span v-if="classroomIdError" class="text-xs text-red-500">{{ classroomIdError }}</span>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label class="text-foreground-muted font-light" for="date">Date</Label>
            <Input id="date" type="date" class="h-10 bg-white" v-model="date" />
            <span v-if="dateError" class="text-xs text-red-500">{{ dateError }}</span>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button @click="open = false" size="sm" class="h-9" variant="outline">
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
