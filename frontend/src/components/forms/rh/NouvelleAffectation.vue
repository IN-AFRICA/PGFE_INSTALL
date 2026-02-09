<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import { Button } from '@/components/ui/button'
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
import { Textarea } from '@/components/ui/textarea'
import { API_ROUTES } from '@/utils/constants/api_route'
import { usePostApi } from '@/composables/usePostApi'
import { useGetApi } from '@/composables/useGetApi'
import { usePutApi } from '@/composables/usePutApi'
import api from '@/services/api'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// Props pour le mode et l'ID
const props = defineProps<{
  mode?: 'create' | 'edit' | 'view'
  id?: number | string
}>()

// Déterminer le mode à partir des props ou de la route
const currentMode = computed(
  () => props.mode || (route.query.mode as 'create' | 'edit' | 'view') || 'create',
)
const affectationId = computed(() => {
  const id = props.id || route.params.id || route.query.id
  if (Array.isArray(id)) return id[0]
  return id
})

// Computed pour les états du mode
const isViewMode = computed(() => currentMode.value === 'view')
const isEditMode = computed(() => currentMode.value === 'edit')

// Titre dynamique selon le mode
const pageTitle = computed(() => {
  switch (currentMode.value) {
    case 'view':
      return "Détails de l'Affectation"
    case 'edit':
      return "Modifier l'Affectation"
    default:
      return 'Nouvelle Affectation de Personnel'
  }
})

// Breadcrumb dynamique
const breadcrumbLabel = computed(() => {
  switch (currentMode.value) {
    case 'view':
      return "Détails de l'Affectation"
    case 'edit':
      return "Modifier l'Affectation"
    default:
      return 'Nouvelle Affectation'
  }
})

// Données du formulaire
const formData = reactive({
  academic_personal_id: undefined as number | undefined,
  lieu_affectation: '',
  durree_jours: undefined as number | undefined,
  description: '',
  school_year_id: undefined as number | undefined,
})

// Chargement des données de l'affectation existante
const loadingAffectation = ref(false)

// Chargement des personnels
const {
  data: personnelsData,
  fetchData: fetchPersonnels,
  loading: loadingPersonnels,
} = useGetApi(API_ROUTES.GET_PERSONALS)

// Chargement des fonctions
const { data: fonctionsData, fetchData: fetchFonctions } = useGetApi(API_ROUTES.GET_FONCTIONS)

// Chargement des années scolaires
const {
  data: schoolYearsData,
  fetchData: fetchSchoolYears,
  loading: loadingSchoolYears,
} = useGetApi(API_ROUTES.GET_SCHOOL_YEARS)

// Fonction pour récupérer le nom de la fonction à partir de l'ID
const getFonctionName = (fonctionId: number | null | undefined) => {
  if (!fonctionId || !fonctionsData.value) return 'N/A'

  const fonctions = Array.isArray(fonctionsData.value)
    ? fonctionsData.value
    : (fonctionsData.value as any)?.data || []

  const fonction = fonctions.find((f: any) => f.id == fonctionId)
  return fonction?.name || fonction?.title || fonction?.libelle || 'N/A'
}

// Personnels formatés pour le select
const personnels = computed(() => {
  const data = Array.isArray(personnelsData.value)
    ? personnelsData.value
    : (personnelsData.value as any)?.data || []

  return data.map((p: any) => ({
    id: p.id,
    name: `${p.name || p.nom || ''} ${p.postnom || ''} ${p.firstname || p.prenom || ''}`.trim(),
    matricule: p.matricule,
    fonction_id: p.fonction_id,
    fonction: getFonctionName(p.fonction_id),
  }))
})

// Années scolaires formatées
const schoolYears = computed(() => {
  const v: any = schoolYearsData.value
  const list = Array.isArray(v) ? v : (v?.years ?? [])
  return list.map((y: any) => ({
    id: y.id,
    label: y.name || 'N/A',
  }))
})

// Personnel sélectionné
const selectedPersonnel = computed(() => {
  if (!formData.academic_personal_id) return null
  return personnels.value.find((p: any) => Number(p.id) === Number(formData.academic_personal_id))
})

// Soumission du formulaire
const { loading: submitting, error: submitError, postData } = usePostApi()
const { loading: updating, error: updateError, putData } = usePutApi()

// Chargement de l'affectation existante
async function fetchAffectation(id: number | string) {
  loadingAffectation.value = true
  try {
    const response = await api.get(API_ROUTES.GET_ONE_PERSONNEL_AFFECTATION(Number(id)))
    const result = response.data as any

    if (result.success && result.data) {
      const data = result.data
      formData.academic_personal_id = Number(data.academic_personal_id)
      formData.lieu_affectation = data.lieu_affectation || ''
      formData.durree_jours = data.durree_jours ? Number(data.durree_jours) : undefined
      formData.description = data.description || ''
      formData.school_year_id = data.school_year_id ? Number(data.school_year_id) : undefined
    } else {
      showCustomToast({
        message: "Impossible de récupérer les données de l'affectation.",
        type: 'error',
      })
    }
  } catch (error) {
    console.error("Erreur lors du chargement de l'affectation:", error)
    showCustomToast({
      message: "Erreur lors du chargement de l'affectation.",
      type: 'error',
    })
  } finally {
    loadingAffectation.value = false
  }
}

// Validation du formulaire
const isFormValid = computed(() => {
  return (
    formData.academic_personal_id !== undefined &&
    formData.lieu_affectation.trim() !== '' &&
    formData.lieu_affectation.length <= 255 &&
    formData.durree_jours !== undefined &&
    formData.durree_jours >= 1 &&
    formData.school_year_id !== undefined
  )
})

// Soumettre le formulaire
async function handleSubmit() {
  if (isViewMode.value) return

  if (!isFormValid.value) {
    showCustomToast({
      message: 'Veuillez remplir tous les champs obligatoires correctement.',
      type: 'error',
    })
    return
  }

  const currentUserId = authStore.user?.id
  if (!currentUserId) {
    showCustomToast({
      message: "Impossible d'identifier l'utilisateur connecté.",
      type: 'error',
    })
    return
  }

  const payload = {
    academic_personal_id: Number(formData.academic_personal_id),
    lieu_affectation: formData.lieu_affectation.trim(),
    durree_jours: Number(formData.durree_jours),
    description: formData.description.trim() || null,
    school_year_id: Number(formData.school_year_id),
    author_id: Number(currentUserId),
  }

  try {
    if (isEditMode.value && affectationId.value) {
      // Mode édition
      await putData(API_ROUTES.UPDATE_PERSONNEL_AFFECTATION(Number(affectationId.value)), payload)

      if (updateError.value) {
        console.error('❌ Erreur API:', updateError.value)
        showCustomToast({
          message: updateError.value,
          type: 'error',
        })
        return
      }

      showCustomToast({
        message: 'Affectation modifiée avec succès !',
        type: 'success',
      })
    } else {
      // Mode création
      await postData(API_ROUTES.CREATE_PERSONNEL_AFFECTATION, payload)

      if (submitError.value) {
        console.error('❌ Erreur API:', submitError.value)
        showCustomToast({
          message: submitError.value,
          type: 'error',
        })
        return
      }

      showCustomToast({
        message: 'Affectation créée avec succès !',
        type: 'success',
      })

      // Réinitialiser le formulaire seulement en mode création
      resetForm()
    }

    // Rediriger vers la liste
    setTimeout(() => {
      router.push('/rh/saisie/mise-en-place-personnel')
    }, 1000)
  } catch (error) {
    console.error('Erreur lors de la soumission:', error)
    showCustomToast({
      message: "Une erreur inattendue s'est produite.",
      type: 'error',
    })
  }
}

// Réinitialiser le formulaire
function resetForm() {
  formData.academic_personal_id = undefined
  formData.lieu_affectation = ''
  formData.durree_jours = undefined
  formData.description = ''
  formData.school_year_id = undefined
}

// Annuler et retourner
function handleCancel() {
  router.push('/rh/saisie/mise-en-place-personnel')
}

// Chargement initial
onMounted(async () => {
  // Charger les données si mode edit ou view
  if ((isEditMode.value || isViewMode.value) && affectationId.value) {
    await fetchAffectation(affectationId.value)
  }
  await Promise.all([fetchPersonnels(), fetchFonctions(), fetchSchoolYears()])
})

// Computed pour le loading global
const isLoading = computed(
  () => loadingAffectation.value || loadingPersonnels.value || loadingSchoolYears.value,
)

// Computed pour le bouton submit
const submitButtonText = computed(() => {
  if (submitting.value || updating.value) return 'Enregistrement...'
  return isEditMode.value ? "Modifier l'affectation" : "Enregistrer l'affectation"
})

const isSubmitting = computed(() => submitting.value || updating.value)
</script>

<template>
  <DashFormLayout
    :title="pageTitle"
    link-back="/rh/saisie/mise-en-place-personnel"
    group-route="/rh/saisie/personnel"
    module="rh"
    :breadcrumb="[
      { label: 'Mise en place', href: '/rh/saisie/mise-en-place-personnel' },
      { label: breadcrumbLabel, isActive: true },
    ]"
  >
    <!-- Loader pendant le chargement des données -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <span class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary"></span>
      <span class="ml-3 text-gray-600">Chargement des données...</span>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <!-- Section Personnel -->
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Sélection du personnel"
      >
        <InputWrapper>
          <Label class="text-sm">
            Personnel
            <SpanRequired v-if="!isViewMode" />
          </Label>
          <Select
            v-model="formData.academic_personal_id"
            :disabled="loadingPersonnels || isViewMode"
          >
            <SelectTrigger class="!h-10 bg-white w-full" :class="{ 'bg-gray-100': isViewMode }">
              <SelectValue placeholder="Sélectionner un personnel" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem
                  v-for="personnel in personnels"
                  :key="personnel.id"
                  :value="personnel.id"
                >
                  {{ personnel.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">Matricule</Label>
          <Input
            :key="`matricule-${selectedPersonnel?.id}`"
            :model-value="selectedPersonnel?.matricule || ''"
            class="bg-gray-100 transition-all h-10 rounded-md"
            readonly
            placeholder="Sélectionner d'abord un personnel"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">Fonction actuelle</Label>
          <Input
            :key="`fonction-${selectedPersonnel?.id}-${selectedPersonnel?.fonction}`"
            :model-value="selectedPersonnel?.fonction || ''"
            class="bg-gray-100 transition-all h-10 rounded-md"
            readonly
            placeholder="Sélectionner d'abord un personnel"
          />
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <!-- Section Détails de l'affectation -->
      <FormSection class="grid sm:grid-cols-2 gap-y-6 gap-x-10" title="Détails de l'affectation">
        <InputWrapper>
          <Label class="text-sm">
            Lieu d'affectation
            <SpanRequired v-if="!isViewMode" />
          </Label>
          <Input
            v-model="formData.lieu_affectation"
            class="transition-all h-10 rounded-md"
            :class="isViewMode ? 'bg-gray-100' : 'bg-white'"
            placeholder="Ex: Campus principal, Annexe A, Bureau central..."
            maxlength="255"
            :readonly="isViewMode"
          />
          <p v-if="!isViewMode" class="text-xs text-gray-500 mt-1">
            {{ formData.lieu_affectation.length }}/255 caractères
          </p>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Durée (en jours)
            <SpanRequired v-if="!isViewMode" />
          </Label>
          <Input
            v-model.number="formData.durree_jours"
            type="number"
            min="1"
            class="transition-all h-10 rounded-md"
            :class="isViewMode ? 'bg-gray-100' : 'bg-white'"
            placeholder="Nombre de jours"
            :readonly="isViewMode"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
            Année scolaire
            <SpanRequired v-if="!isViewMode" />
          </Label>
          <Select v-model="formData.school_year_id" :disabled="loadingSchoolYears || isViewMode">
            <SelectTrigger class="!h-10 w-full" :class="isViewMode ? 'bg-gray-100' : 'bg-white'">
              <SelectValue placeholder="Sélectionner l'année scolaire" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem v-for="year in schoolYears" :key="year.id" :value="year.id">
                  {{ year.label }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper class="sm:col-span-2">
          <Label class="text-sm">Description</Label>
          <Textarea
            v-model="formData.description"
            class="transition-all rounded-md min-h-[100px]"
            :class="isViewMode ? 'bg-gray-100' : 'bg-white'"
            :placeholder="
              !formData.description && isViewMode
                ? ''
                : 'Décrivez les détails de l\'affectation (optionnel)...'
            "
            :readonly="isViewMode"
          />
        </InputWrapper>
      </FormSection>

      <!-- Boutons d'action -->
      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel" :disabled="isSubmitting">
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          {{ isViewMode ? 'Retour' : 'Annuler' }}
        </Button>
        <Button v-if="!isViewMode" type="submit" :disabled="!isFormValid || isSubmitting">
          <span
            v-if="isSubmitting"
            class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-white mr-1.5"
          ></span>
          <span v-else class="flex iconify hugeicons--floppy-disk mr-1.5"></span>
          {{ submitButtonText }}
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
