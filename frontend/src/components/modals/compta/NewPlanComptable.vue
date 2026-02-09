<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
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
import { Form, Field } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import * as z from 'zod'
import { usePostApi } from '@/composables/usePostApi'
import { useGetApi } from '@/composables/useGetApi'
import { usePutApi } from '@/composables/usePutApi'
import api from '@/services/api'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import { API_ROUTES } from '@/utils/constants/api_route'

// Schéma de validation
const planSchema = toTypedSchema(
  z.object({
    name: z
      .string({ required_error: 'Le nom est requis' })
      .min(1, 'Le nom est requis')
      .max(255, 'Max 255 caractères'),
    code: z
      .string({ required_error: 'Le code est requis' })
      .min(1, 'Le code est requis')
      .max(50, 'Max 50 caractères'),
    class_comptability_id: z
      .string({ required_error: 'La classe est requise' })
      .min(1, 'La classe est requise'),
    category_comptability_id: z
      .string({ required_error: 'La catégorie est requise' })
      .min(1, 'La catégorie est requise'),
  }),
)

// API: création de compte
const {
  postData: createAccount,
  loading: creating,
  error: createError,
  response: createResponse,
} = usePostApi<any>()

// API: mise à jour de compte
const {
  putData: updateAccount,
  loading: updating,
  error: updateError,
  response: updateResponse,
} = usePutApi<any>()

// API: listes pour sélecteurs
const {
  data: classesData,
  loading: classesLoading,
  fetchData: fetchClasses,
} = useGetApi<any[]>(API_ROUTES.GET_CLASS_COMPTE)
const {
  data: categoriesData,
  loading: categoriesLoading,
  fetchData: fetchCategories,
} = useGetApi<any[]>(API_ROUTES.GET_CATEGORIE_COMPTE)

const classes = computed(() => {
  const raw: any = classesData.value
  let list: any[] = []
  if (Array.isArray(raw)) list = raw
  else if (raw?.data && Array.isArray(raw.data)) list = raw.data
  else if (raw && typeof raw === 'object')
    list = Object.values(raw).filter((v: any) => v && typeof v === 'object')
  return list.map((c: any) => ({
    id: String(c.id ?? c.value ?? ''),
    name: c.name ?? c.label ?? c.libelle ?? `Classe #${c.id ?? c.value}`,
    code: c.code ?? c.code_class ?? c.codeClasse ?? c.codeValue ?? '',
  }))
})

const categories = computed(() => {
  const raw: any = categoriesData.value
  let list: any[] = []
  if (Array.isArray(raw)) list = raw
  else if (raw?.data && Array.isArray(raw.data)) list = raw.data
  else if (raw && typeof raw === 'object')
    list = Object.values(raw).filter((v: any) => v && typeof v === 'object')
  return list.map((c: any) => ({
    id: String(c.id ?? c.value ?? ''),
    name: c.name ?? c.label ?? c.libelle ?? `Catégorie #${c.id ?? c.value}`,
  }))
})

const isOpen = ref(false)
const isEditMode = ref(false)
const editId = ref<number | null>(null)

// Valeurs initiales du formulaire (pour édition)
const initialValues = ref({
  name: '',
  code: '',
  class_comptability_id: '',
  category_comptability_id: '',
})

function handleClassChange(value: string, onChange: (val: any) => void) {
  // Met à jour la valeur du champ "class_comptability_id"
  onChange(value)
}

onMounted(async () => {
  // Pré-charger si le composant est monté longtemps avant l'ouverture
  await Promise.all([fetchClasses(), fetchCategories()])

  // Ouvrir en mode édition via bus d'événements
  eventBus.on('editAccountNumber', (payload: any) => {
    const id = typeof payload === 'number' ? payload : payload?.id
    if (id) {
      isEditMode.value = true
      editId.value = Number(id)
      isOpen.value = true
    }
  })
})

// Recharger à l'ouverture pour garantir des données fraîches et éviter un sélecteur vide
watch(isOpen, async (open) => {
  if (open) {
    await Promise.all([fetchClasses(), fetchCategories()])

    // Si édition, récupérer les détails du compte et préremplir
    if (isEditMode.value && editId.value !== null) {
      try {
        const res = await api.get(API_ROUTES.GET_ONE_COMPTE(editId.value))
        const d =
          res?.data && typeof res.data === 'object' && 'data' in res.data
            ? (res.data as any).data
            : res.data
        initialValues.value = {
          name: d?.name ?? '',
          code: d?.code ?? '',
          class_comptability_id: String(d?.class_comptability_id ?? ''),
          category_comptability_id: String(d?.category_comptability_id ?? ''),
        }
      } catch (e) {
        showCustomToast({ message: 'Impossible de charger le compte', type: 'error' })
      }
    } else {
      // Réinitialiser pour le mode ajout
      initialValues.value = {
        name: '',
        code: '',
        class_comptability_id: '',
        category_comptability_id: '',
      }
    }
  }
})

async function onSubmit(values: any) {
  try {
    const payload = {
      name: values.name,
      code: values.code,
      class_comptability_id: Number(values.class_comptability_id),
      category_comptability_id: Number(values.category_comptability_id),
    }

    if (isEditMode.value && editId.value !== null) {
      await updateAccount(API_ROUTES.UPDATE_COMPTE(editId.value), payload)
      if (updateError.value) {
        showCustomToast({ message: updateError.value, type: 'error' })
        return
      }
      const msg = (updateResponse.value as any)?.message || 'Plan comptable mis à jour avec succès.'
      showCustomToast({ message: msg, type: 'success' })
    } else {
      await createAccount(API_ROUTES.CREATE_COMPTE, payload)
      if (createError.value) {
        showCustomToast({ message: createError.value, type: 'error' })
        return
      }
      const msg = (createResponse.value as any)?.message || 'Plan comptable créé avec succès.'
      showCustomToast({ message: msg, type: 'success' })
    }

    isOpen.value = false
    eventBus.emit('planComptableUpdated')
  } catch (err) {
    showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
  }
}

const openAddMode = () => {
  isEditMode.value = false
  editId.value = null
  isOpen.value = true
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button size="md" class="rounded-md" @click="openAddMode">
        <span class="iconify hugeicons--plus-sign"></span>
        <span class="hidden sm:flex"> Ajouter </span>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[540px]">
      <DialogHeader>
        <DialogTitle>{{ isEditMode ? 'Modifier un compte' : 'Ajouter un compte' }}</DialogTitle>
      </DialogHeader>
      <Form @submit="onSubmit" :validation-schema="planSchema" :initial-values="initialValues">
        <div class="grid gap-4 py-4">
          <div class="flex flex-col space-y-1.5 flex-1">
            <Label for="code" class="text-sm font-medium">Code <SpanRequired /></Label>
            <Field name="code" v-slot="{ field, errorMessage }">
              <Input id="code" v-bind="field" placeholder="Ex: ACC-001" />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="name" class="text-sm font-medium">Nom <SpanRequired /></Label>
            <Field name="name" v-slot="{ field, errorMessage }">
              <Input id="name" v-bind="field" placeholder="Ex: Comptes de capitaux" />
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>
          <div class="flex flex-col space-y-1.5 flex-1">
            <Label class="text-sm font-medium">Classe <SpanRequired /></Label>
            <Field name="class_comptability_id" v-slot="{ field, errorMessage }">
              <select
                :key="isOpen ? 'open' : 'closed'"
                class="w-full h-10 border border-gray-200/40 bg-white rounded-md px-2"
                :value="field.value"
                @change="
                  handleClassChange(($event.target as HTMLSelectElement).value, field.onChange)
                "
                :disabled="classesLoading"
              >
                <option value="" disabled v-if="classesLoading">Chargement des classes...</option>
                <option value="" v-else>Sélectionner une classe</option>
                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>
          <div class="flex flex-col space-y-1.5 flex-1">
            <Label class="text-sm font-medium">Catégorie <SpanRequired /></Label>
            <Field name="category_comptability_id" v-slot="{ field, errorMessage }">
              <select
                :key="isOpen ? 'open-cat' : 'closed-cat'"
                class="w-full h-10 border border-gray-200/40 bg-white rounded-md px-2"
                :value="field.value"
                @change="field.onChange(($event.target as HTMLSelectElement).value)"
                :disabled="categoriesLoading"
              >
                <option value="" disabled v-if="categoriesLoading">
                  Chargement des catégories...
                </option>
                <option value="" v-else>Sélectionner une catégorie</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
            </Field>
          </div>
          <div class="mt-1 pb-2">
            <p class="text-sm text-foreground-muted">Les champs marqués sont obligatoires</p>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 items-center">
          <Button size="sm" class="h-9" variant="outline" type="button" @click="isOpen = false">
            <span class="iconify flex hugeicons--cancel-01 mr-1"></span>
            Annuler
          </Button>
          <Button size="sm" class="h-9" type="submit" :disabled="creating || updating">
            <span class="iconify flex hugeicons--floppy-disk mr-1"></span>
            {{
              creating || updating
                ? 'Enregistrement...'
                : isEditMode
                  ? 'Mettre à jour'
                  : 'Enregistrer'
            }}
          </Button>
        </DialogFooter>
      </Form>
    </DialogContent>
  </Dialog>
</template>
