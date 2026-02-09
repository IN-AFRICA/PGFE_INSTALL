<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from 'vue'
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
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { toTypedSchema } from '@vee-validate/zod'
import { useField, useForm } from 'vee-validate'
import z from 'zod'

import InputWrapper from '../atoms/InputWrapper.vue'
import ListSchool from '@/utils/widgets/vues/ListSchool.vue'
import IconifySpinner from '../ui/spinner/IconifySpinner.vue'

import { useGetApi } from '@/composables/useGetApi'
import SpanRequired from '@/components/atoms/SpanRequired.vue'

const { loading, error, response, postData, success } = usePostApi()
const {
  loading: loadingPut,
  error: errorPut,
  response: responsePut,
  putData,
  success: successPut,
} = usePutApi()

const { data: personnels, fetchData: fetchPersonnels } = useGetApi<any[]>(
  API_ROUTES.GET_ACADEMIC_PERSONALS,
)
const { data: filieresData, fetchData: fetchFilieres } = useGetApi(API_ROUTES.GET_FILLIERES)
import { useAcademicLevels } from '@/composables/useAcademicLevels'
const selectedFiliereId = ref<string | null>(null)
const { levelOptions, loadLevels } = useAcademicLevels(selectedFiliereId, ref(null))

const open = ref(false)
const isEditing = ref(false)
const editingItem = ref(null)

eventBus.on('editClassRoom', async (item: any) => {
  try {
    const rawItem = JSON.parse(JSON.stringify(item))
    isEditing.value = true
    editingItem.value = rawItem
    open.value = true

    await nextTick()
    await nextTick()

    const formData = {
      name: rawItem.name || '',
      academic_level_id: Number(rawItem.academic_level_id || 0),
      titulaire_id: rawItem.titulaire_id ? String(rawItem.titulaire_id) : '',
      indicator: rawItem.indicator || '',
    }
    setValues(formData as any)
  } catch (error) {
    console.error('[NewClasse] ERROR in editClassRoom handler:', error)
  }
})

onMounted(() => {
  fetchPersonnels()
  fetchFilieres()
  loadLevels()
})

const filieres = computed(() => {
  const v = filieresData.value
  if (Array.isArray(v)) return v
  if ((v as any)?.data && Array.isArray((v as any).data)) return (v as any).data
  return []
})

// Liste des personnels unique et formatée pour le Select Titulaire
const uniquePersonnels = computed(() => {
  // Normalise la réponse: peut être un tableau direct ou un objet { data: [...] }
  const raw = personnels?.value as any
  const list: any[] = Array.isArray(raw) ? raw : Array.isArray(raw?.data) ? raw.data : []

  const map = new Map<string, { id: string; label: string }>()
  for (const p of list) {
    // Certains enregistrements utilisent `id`, d'autres `user_id`.
    const id = String(p?.id ?? p?.user_id ?? '').trim()
    if (!id) continue

    // Construire un label lisible: privilégier pre_name/post_name puis name
    const pre = String(p?.pre_name || p?.post_name || '').trim()
    const name = String(p?.name || '').trim()
    const label = [pre, name].filter(Boolean).join(' ').trim() || `ID ${id}`

    if (!map.has(id)) map.set(id, { id, label })
  }
  return Array.from(map.values())
})

const alphabetOptions = computed(() =>
  Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i)),
)

const schemaForm = z.object({
  name: z.string({ required_error: 'Veuillez saisir le nom de la classe' }).min(2).max(100),

  academic_level_id: z.coerce.number({ required_error: 'Veuillez sélectionner un niveau' }),
  titulaire_id: z
    .union([z.string().min(1), z.literal('')])
    .optional()
    .default(''),
  indicator: z
    .union([
      z.string().regex(/^[A-Z]$/, "L'indicateur doit être une seule lettre majuscule (A–Z)"),
      z.literal(''),
    ])
    .optional()
    .default(''),
})

const { handleSubmit, resetForm, setValues } = useForm({
  validationSchema: toTypedSchema(schemaForm),
})
const { value: name, errorMessage: nameError } = useField<string>('name')

const { value: academic_level_id, errorMessage: academicLevelError } =
  useField<string>('academic_level_id')
const { value: titulaire_id, errorMessage: titulaireError } = useField<string>('titulaire_id')
const { value: indicator, errorMessage: indicatorError } = useField<string>('indicator')

const onSubmit = handleSubmit(async (values) => {
  try {
    const normalizedIndicator = String(values.indicator || '').toUpperCase()
    const payload = { ...values, indicator: normalizedIndicator }
    if (isEditing.value && editingItem.value) {
      // Mode édition
      const url = API_ROUTES.UPDATE_CLASSROOM.replace(':classroom', String(editingItem.value.id))
      await putData(url, payload)

      if (successPut.value) {
        showCustomToast({
          message: responsePut.value?.message || 'Classe modifiée avec succès',
          type: 'success',
        })
        resetForm()
        resetEditState()
        open.value = false
        eventBus.emit('classRoomUpdated')
      } else {
        // Gestion des erreurs d'édition
        const errorMsg =
          errorPut.value ||
          responsePut.value?.message ||
          'Erreur lors de la modification de la classe'
        showCustomToast({
          message: errorMsg,
          type: 'error',
        })
      }
    } else {
      // Mode création
      await postData(API_ROUTES.CREATE_CLASSROOM, payload)

      if (success.value) {
        showCustomToast({
          message: response.value?.message || 'Classe ajoutée avec succès',
          type: 'success',
        })
        resetForm()
        open.value = false
        eventBus.emit('classRoomUpdated')
      } else {
        // Gestion des erreurs de création
        const errorMsg =
          error.value || response.value?.message || 'Erreur lors de la création de la classe'
        showCustomToast({
          message: errorMsg,
          type: 'error',
        })
      }
    }
  } catch (err) {
    console.error('[NewClasse] Error in onSubmit:', err)
    showCustomToast({
      message: 'Une erreur inattendue est survenue',
      type: 'error',
    })
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

const handleOpenChange = (isOpen: boolean) => {
  if (!isOpen) {
    // Modal fermé
    resetForm()
    resetEditState()
  } else if (!isEditing.value) {
    // Modal ouvert en mode création (via bouton Nouvelle classe)
    resetForm()
    resetEditState()
  }
}
</script>

<template>
  <Dialog v-model:open="open" @update:open="handleOpenChange">
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Nouvelle classe </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[540px]">
      <DialogHeader>
        <DialogTitle v-if="!isEditing">Nouvelle classe</DialogTitle>
        <DialogTitle v-else>Édition de la classe</DialogTitle>
        <DialogDescription> Enregistrer une nouvelle classe </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4 py-4">
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium"> Désignation </Label>
            <Input
              type="text"
              id="name"
              name="name"
              v-model="name"
              placeholder="Désignation de la classe"
              class="w-full h-10 border border-gray-200/40 bg-white transition-all"
            />
            <span v-if="nameError" class="text-xs text-red-500">{{ nameError }}</span>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="indicator" class="text-sm font-medium"> Indicateur </Label>
            <Select id="indicator" name="indicator" v-model="indicator">
              <SelectTrigger id="indicator" class="h-10 w-full">
                <SelectValue placeholder="Sélectionnez une lettre (A–Z)" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-for="letter in alphabetOptions" :key="letter" :value="letter">
                    {{ letter }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <span v-if="indicatorError" class="text-xs text-red-500">{{ indicatorError }}</span>
          </div>

          <div class="flex flex-col space-y-1.5 flex-1">
            <Label class="text-sm font-medium"> Section </Label>
            <Select
              :model-value="selectedFiliereId || ''"
              @update:model-value="(val: any) => (selectedFiliereId = String(val))"
            >
              <SelectTrigger class="h-10 w-full">
                <SelectValue placeholder="Sélectionnez une section" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-for="f in filieres" :key="f.id" :value="String(f.id)">
                    {{ f.name }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <div class="flex flex-col space-y-1.5 flex-1">
            <Label for="academic_level_id" class="text-sm font-medium"> Niveau </Label>
            <Select
              id="academic_level_id"
              name="academic_level_id"
              v-model="academic_level_id"
              :disabled="!selectedFiliereId"
            >
              <SelectTrigger id="academic_level_id" class="h-10 w-full">
                <SelectValue
                  :placeholder="
                    selectedFiliereId
                      ? 'Sélectionnez un niveau'
                      : 'Sélectionnez d\'abord une section'
                  "
                />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem
                    v-for="level in levelOptions"
                    :key="level.id"
                    :value="String(level.id)"
                  >
                    {{ level.label }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <span v-if="academicLevelError" class="text-xs text-red-500">{{
              academicLevelError
            }}</span>
          </div>

          <div class="flex flex-col space-y-1.5 flex-1">
            <Label for="titulaire_id" class="text-sm font-medium">
              Titulaire
              <SpanRequired />
            </Label>
            <Select id="titulaire_id" name="titulaire_id" v-model="titulaire_id">
              <SelectTrigger id="titulaire_id" class="h-10 w-full">
                <SelectValue placeholder="Sélectionnez un titulaire" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-for="p in uniquePersonnels" :key="p.id" :value="String(p.id)">
                    {{ p.label }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <span v-if="titulaireError" class="text-xs text-red-500">{{ titulaireError }}</span>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
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
