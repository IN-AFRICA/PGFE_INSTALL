<script setup lang="ts">
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogClose,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm, useField } from 'vee-validate'
import { z } from 'zod'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { eventBus } from '@/utils/eventBus.ts'
import { usePostApi } from '@/composables/usePostApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import { useGetApi } from '@/composables/useGetApi.ts'
import { ref, watch, onMounted, computed } from 'vue'

const { loading, error, response, postData, success } = usePostApi()
const {
  loading: loadingPut,
  error: errorPut,
  response: responsePut,
  putData,
  success: successPut,
} = usePutApi()

const open = ref(false)

const isEditing = ref(false)
const editingItem = ref(null)

// Écouter l'événement d'édition
eventBus.on('editFilliere', (item: any) => {
  console.log('[NewFilliere] Edit event received:', item)
  isEditing.value = true
  editingItem.value = item

  // Pré-remplir le formulaire
  name.value = item.name || ''
  code.value = item.code ? String(item.code) : ''

  open.value = true
})

const schemaForm = z.object({
  name: z.string({ required_error: 'Veuillez saisir le nom de la fillière' }).min(2).max(100),
  code: z.string({ required_error: 'Le code est requis' }).min(1, 'Le code est requis').max(50),
})

//Validation
const { handleSubmit, resetForm } = useForm({
  validationSchema: toTypedSchema(schemaForm),
})
const { value: name, errorMessage: nameError } = useField<string>('name')

const { value: code, errorMessage: codeError } = useField<string>('code')

const onSubmit = handleSubmit(async (values) => {
  console.log('[NewFilliere] onSubmit called with values:', values)

  try {
    if (isEditing.value && editingItem.value) {
      // Mode édition
      console.log('[NewFilliere] Editing mode - calling putData')
      const url = API_ROUTES.UPDATE_FILLIERE.replace(':filiaire', String(editingItem.value.id))
      await putData(url, values)

      if (successPut.value) {
        console.log('[NewFilliere] Put success - showing toast and closing modal')
        showCustomToast({
          message: responsePut.value?.message || 'Section modifiée avec succès',
          type: 'success',
        })
        resetForm()
        resetEditState()
        open.value = false
        eventBus.emit('filiereUpdated')
      }
    } else {
      // Mode création
      console.log('[NewFilliere] Creation mode - calling postData')
      await postData(API_ROUTES.CREATE_FILLIERE, values)

      if (success.value) {
        console.log('[NewFilliere] Post success - showing toast and closing modal')
        showCustomToast({
          message: response.value?.message || 'Section ajoutée avec succès',
          type: 'success',
        })
        resetForm()
        open.value = false
        eventBus.emit('filiereUpdated')
      }
    }
  } catch (err) {
    console.error('[NewFilliere] Error in onSubmit:', err)
  }
})

const resetEditState = () => {
  isEditing.value = false
  editingItem.value = null
}

const handleCancel = () => {
  resetForm()
  resetEditState()
  open.value = false
}

// Surveiller la fermeture du modal pour reset l'état
watch(open, (newValue) => {
  if (!newValue) {
    resetEditState()
  }
})
</script>
<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Nouvelle section </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[400px]">
      <DialogHeader>
        <DialogTitle v-if="!isEditing">Nouvelle section</DialogTitle>
        <DialogTitle v-else>Édition de la section</DialogTitle>
        <DialogDescription> Enregistrer une nouvelle section </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4 py-4">
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium"> Nom de la section </Label>
            <Input
              type="text"
              id="name"
              name="name"
              v-model="name"
              placeholder="Entrez le nom de la section"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
              :disabled="loading || loadingPut"
            />
            <span v-if="nameError" class="text-red-500 text-xs mt-1">{{ nameError }}</span>
          </div>

          <div class="flex flex-col space-y-1.5">
            <Label for="code" class="text-sm font-medium"> Code </Label>
            <Input
              type="text"
              id="code"
              name="code"
              v-model="code"
              placeholder="Entrez le code de la section"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
              :disabled="loading || loadingPut"
            />
            <span v-if="codeError" class="text-red-500 text-xs mt-1">{{ codeError }}</span>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <DialogClose as-child>
            <Button
              size="sm"
              class="h-9"
              variant="outline"
              type="button"
              @click="handleCancel"
              :disabled="loading || loadingPut"
            >
              <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
              Annuler
            </Button>
          </DialogClose>
          <Button size="sm" class="h-9" type="submit" :disabled="loading || loadingPut">
            <span v-if="!loading && !loadingPut" class="flex items-center gap-2">
              <span class="iconify hugeicons--floppy-disk mr-1"></span>
              <span v-if="!isEditing">Enregistrer</span>
              <span v-else>Modifier</span>
            </span>
            <span v-else class="flex items-center gap-2">
              <IconifySpinner size="lg" />
              <span v-if="!isEditing">Enregistrement en cours...</span>
              <span v-else>Modification en cours...</span>
            </span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
