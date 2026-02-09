<script setup lang="ts">
import { ref, computed, onMounted, reactive, watch } from 'vue'
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
import { usePutApi } from '@/composables/usePutApi'
import { useGetApi } from '@/composables/useGetApi'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useAuthStore } from '@/stores/auth'
import ListSchool from '@/utils/widgets/vues/ListSchool.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// Mode édition
const formationId = computed(() => route.params.id as string | undefined)
const isEditMode = computed(() => !!formationId.value)
const loadingFormation = ref(false)

// Données du formulaire
const formData = reactive({
  academic_personal_id: undefined as number | undefined,
  title: '',
  start_date: new Date().toISOString().split('T')[0],
  end_date: undefined as string | undefined,
  location: '',
  school_year_id: undefined as number | undefined,
  description: '',
  duration_days: undefined as number | undefined,
})

// Calcul automatique de la durée
watch([() => formData.start_date, () => formData.end_date], ([newStart, newEnd]) => {
  if (newStart && newEnd) {
    const start = new Date(newStart)
    const end = new Date(newEnd)

    // Calcul de la différence en temps
    const diffTime = end.getTime() - start.getTime()

    // Conversion en jours (1000ms * 60s * 60m * 24h)
    // Math.ceil pour arrondir au jour supérieur si nécessaire
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

    // Si la date de fin est après ou égale à la date de début
    if (diffDays >= 0) {
      // On ajoute 1 pour inclure le jour de début (ex: du 1er au 1er = 1 jour)
      formData.duration_days = diffDays + 1
    } else {
      formData.duration_days = undefined // Date invalide
    }
  }
})

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

// Fonction helper pour le nom de la fonction
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
    fonction: getFonctionName(p.fonction_id),
  }))
})

// Années scolaires formatées
const schoolYears = computed(() => {
  const v: any = schoolYearsData.value
  const list = Array.isArray(v) ? v : (v?.years ?? v?.data ?? [])
  return list.map((y: any) => ({
    id: y.id,
    label: y.name || y.year || y.libelle || `${y.start_year}-${y.end_year}` || 'N/A',
  }))
})

// Personnel sélectionné
const selectedPersonnel = computed(() => {
  if (!formData.academic_personal_id) return null
  return personnels.value.find((p: any) => p.id === formData.academic_personal_id)
})

// Soumission du formulaire
const { loading: submitting, error: submitError, postData } = usePostApi()
const { loading: updating, error: updateError, putData } = usePutApi()

// État de chargement combiné
const isSubmitting = computed(() => submitting.value || updating.value)

// Validation du formulaire
const isFormValid = computed(() => {
  return (
    formData.academic_personal_id !== undefined &&
    formData.title.trim() !== '' &&
    formData.start_date !== '' &&
    formData.school_year_id !== undefined
  )
})

// Formater la date pour l'API (DD-MM-YYYY)
const formatDateForApi = (dateString: string) => {
  if (!dateString) return null
  const dateObj = new Date(dateString)
  const day = String(dateObj.getDate()).padStart(2, '0')
  const month = String(dateObj.getMonth() + 1).padStart(2, '0')
  const year = dateObj.getFullYear()
  return `${day}-${month}-${year}`
}

// Formater la date de l'API vers le format input (YYYY-MM-DD)
const formatDateFromApi = (dateString: string | null) => {
  if (!dateString) return undefined
  const dateObj = new Date(dateString)
  return dateObj.toISOString().split('T')[0]
}

// Charger les données de la formation en mode édition
async function fetchFormationData() {
  if (!formationId.value) return

  loadingFormation.value = true
  try {
    const response = await fetch(
      `${import.meta.env.VITE_API_BASE_URL || 'https://pgfe-back.inafrica.tech/api/v1/'}${API_ROUTES.GET_ONE_FORMATION_CONTINUE(Number(formationId.value))}`,
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
          Accept: 'application/json',
        },
      },
    )

    if (!response.ok) {
      throw new Error('Erreur lors du chargement de la formation')
    }

    const result = await response.json()
    const data = result.data

    if (data) {
      formData.academic_personal_id = Number(data.academic_personal_id)
      formData.title = data.title || ''
      formData.start_date =
        formatDateFromApi(data.start_date) || new Date().toISOString().split('T')[0]
      formData.end_date = formatDateFromApi(data.end_date)
      formData.location = data.location || ''
      formData.school_year_id = data.school_year_id ? Number(data.school_year_id) : undefined
      formData.description = data.description || ''
      formData.duration_days = data.duration_days ? Number(data.duration_days) : undefined
    }
  } catch (error) {
    console.error('Erreur lors du chargement de la formation:', error)
    showCustomToast({
      message: 'Erreur lors du chargement de la formation.',
      type: 'error',
    })
  } finally {
    loadingFormation.value = false
  }
}

// Soumettre le formulaire
async function handleSubmit() {
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
    title: formData.title.trim(),
    start_date: formatDateForApi(formData.start_date),
    end_date: formData.end_date ? formatDateForApi(formData.end_date) : null,
    location: formData.location.trim() || null,
    school_year_id: Number(formData.school_year_id),
    description: formData.description.trim() || null,
    duration_days: formData.duration_days ? Number(formData.duration_days) : null,
    author_id: Number(currentUserId),
  }

  try {
    if (isEditMode.value) {
      // Mode édition
      await putData(API_ROUTES.UPDATE_FORMATION_CONTINUE(Number(formationId.value)), payload)

      if (updateError.value) {
        console.error('❌ Erreur API:', updateError.value)
        showCustomToast({
          message: updateError.value,
          type: 'error',
        })
        return
      }

      showCustomToast({
        message: 'Formation modifiée avec succès !',
        type: 'success',
      })
    } else {
      // Mode création
      await postData(API_ROUTES.CREATE_FORMATION_CONTINUE, payload)

      if (submitError.value) {
        console.error('❌ Erreur API:', submitError.value)
        showCustomToast({
          message: submitError.value,
          type: 'error',
        })
        return
      }

      showCustomToast({
        message: 'Formation enregistrée avec succès !',
        type: 'success',
      })

      // Réinitialiser le formulaire seulement en mode création
      resetForm()
    }

    // Rediriger vers la liste
    setTimeout(() => {
      router.push('/rh/saisie/formation-continue')
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
  formData.title = ''
  formData.start_date = new Date().toISOString().split('T')[0]
  formData.end_date = undefined
  formData.location = ''
  formData.school_year_id = undefined
  formData.description = ''
  formData.duration_days = undefined
}

// Annuler et retourner
function handleCancel() {
  router.push('/rh/saisie/formation-continue')
}

// Chargement initial
onMounted(async () => {
  // Charger les données de la formation si en mode édition
  if (isEditMode.value) {
    await fetchFormationData()
  }
  await Promise.all([fetchPersonnels(), fetchFonctions(), fetchSchoolYears()])
})

// Textes dynamiques selon le mode
const pageTitle = computed(() =>
  isEditMode.value ? 'Modifier la Formation Continue' : 'Nouvelle Formation Continue',
)
const breadcrumbLabel = computed(() =>
  isEditMode.value ? 'Modifier Formation' : 'Nouvelle Formation',
)
const submitButtonText = computed(() =>
  isEditMode.value ? 'Modifier la formation' : 'Enregistrer la formation',
)
const submittingText = computed(() => (isEditMode.value ? 'Modification...' : 'Enregistrement...'))
</script>

<template>
  <DashFormLayout
    :title="pageTitle"
    link-back="/rh/saisie/formation-continue"
    group-route="/rh/saisie/personnel"
    module="rh"
    :breadcrumb="[
      { label: 'GRH', href: '/rh' },
      { label: 'Formation', href: '/rh/saisie/formation-continue' },
      { label: breadcrumbLabel, isActive: true },
    ]"
  >
    <!-- Loader pendant le chargement des données en mode édition -->
    <div v-if="loadingFormation" class="flex items-center justify-center py-20">
      <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary"></div>
      <span class="ml-3 text-gray-600">Chargement des données...</span>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations générales"
      >
        <InputWrapper>
          <Label class="text-sm font-medium">
            Personnel
            <SpanRequired />
          </Label>
          <Select v-model="formData.academic_personal_id" :disabled="loadingPersonnels">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez le personnel" />
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
          <Label class="text-sm font-medium">Matricule</Label>
          <Input
            :value="selectedPersonnel?.matricule || ''"
            class="bg-gray-100 transition-all h-10 rounded-md"
            readonly
            placeholder="Sélectionner d'abord un personnel"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm font-medium">Fonction</Label>
          <Input
            :value="selectedPersonnel?.fonction || ''"
            class="bg-gray-100 transition-all h-10 rounded-md"
            readonly
            placeholder="Sélectionner d'abord un personnel"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm font-medium">
            Année scolaire
            <SpanRequired />
          </Label>
          <Select v-model="formData.school_year_id" :disabled="loadingSchoolYears">
            <SelectTrigger class="!h-10 bg-white w-full">
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
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Détails de la formation"
      >
        <InputWrapper class="sm:col-span-2 lg:col-span-3">
          <Label class="text-sm font-medium">
            Titre de la formation
            <SpanRequired />
          </Label>
          <Input
            v-model="formData.title"
            placeholder="Ex: Formation en pédagogie active"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all rounded-md"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm font-medium">
            Date de début
            <SpanRequired />
          </Label>
          <Input
            type="date"
            v-model="formData.start_date"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all rounded-md"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm font-medium"> Date de fin </Label>
          <Input
            type="date"
            v-model="formData.end_date"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all rounded-md"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm font-medium"> Durée (jours) </Label>
          <Input
            type="number"
            v-model.number="formData.duration_days"
            min="1"
            placeholder="Nombre de jours"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all rounded-md"
          />
        </InputWrapper>

        <InputWrapper class="sm:col-span-2 lg:col-span-3">
          <Label class="text-sm font-medium"> Lieu / Endroit </Label>
          <Input
            v-model="formData.location"
            placeholder="Ex: Salle polyvalente, En ligne..."
            class="w-full h-10 border border-gray-200/40 bg-white transition-all rounded-md"
          />
        </InputWrapper>

        <InputWrapper class="sm:col-span-2 lg:col-span-3">
          <Label class="text-sm font-medium"> Description / Objectifs </Label>
          <Textarea
            v-model="formData.description"
            placeholder="Décrivez les objectifs et le contenu de la formation..."
            class="w-full min-h-[100px] border border-gray-200/40 bg-white transition-all resize-none rounded-md"
          />
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel" :disabled="isSubmitting">
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit" :disabled="!isFormValid || isSubmitting">
          <span
            v-if="isSubmitting"
            class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-white mr-1.5"
          ></span>
          <span v-else class="iconify hugeicons--floppy-disk mr-1"></span>
          {{ isSubmitting ? submittingText : submitButtonText }}
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
