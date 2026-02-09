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
import { usePutApi } from '@/composables/usePutApi'
import { useGetApi } from '@/composables/useGetApi'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// Mode édition
const salaireId = computed(() => route.params.id as string | undefined)
const isEditMode = computed(() => !!salaireId.value)
const loadingSalaire = ref(false)

// Données du formulaire
const formData = reactive({
  personal_id: undefined as number | undefined,
  mois_id: undefined as number | undefined,
  montant: undefined as number | undefined,
  school_year_id: undefined as number | undefined,
  description: '',
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

// Chargement des mois
const {
  data: moisData,
  fetchData: fetchMois,
  loading: loadingMois,
} = useGetApi(API_ROUTES.GET_MOIS)

// Liste des mois formatée
const mois = computed(() => {
  const data = Array.isArray(moisData.value) ? moisData.value : (moisData.value as any)?.data || []

  return data.map((m: any) => ({
    id: m.id,
    name: m.name || m.libelle || m.month || 'N/A',
  }))
})

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
  if (!formData.personal_id) return null
  return personnels.value.find((p: any) => p.id === formData.personal_id)
})

// Soumission du formulaire
const { loading: submitting, error: submitError, postData } = usePostApi()
const { loading: updating, error: updateError, putData } = usePutApi()

// État de chargement combiné
const isSubmitting = computed(() => submitting.value || updating.value)

// Validation du formulaire
const isFormValid = computed(() => {
  return (
    formData.personal_id !== undefined &&
    formData.mois_id !== undefined &&
    formData.montant !== undefined &&
    formData.montant > 0 &&
    formData.school_year_id !== undefined
  )
})

// Formater le montant en devise
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('fr-CD', {
    style: 'currency',
    currency: 'CDF',
    minimumFractionDigits: 0,
  }).format(amount)
}

// Charger les données du salaire en mode édition
async function fetchSalaireData() {
  if (!salaireId.value) return

  loadingSalaire.value = true
  try {
    const response = await fetch(
      `${import.meta.env.VITE_API_BASE_URL || 'https://pgfe-back.inafrica.tech/api/v1/'}${API_ROUTES.GET_ONE_SALAIRE(Number(salaireId.value))}`,
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
          Accept: 'application/json',
        },
      },
    )

    if (!response.ok) {
      throw new Error('Erreur lors du chargement du salaire')
    }

    const result = await response.json()
    const data = result.data

    if (data) {
      formData.personal_id = Number(data.academic_personal_id)
      formData.mois_id = Number(data.mois_id)
      formData.montant = Number(data.montant)
      formData.school_year_id = data.school_year_id ? Number(data.school_year_id) : undefined
      formData.description = data.description || ''
    }
  } catch (error) {
    console.error('Erreur lors du chargement du salaire:', error)
    showCustomToast({
      message: 'Erreur lors du chargement du salaire.',
      type: 'error',
    })
  } finally {
    loadingSalaire.value = false
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
    academic_personal_id: Number(formData.personal_id),
    mois_id: Number(formData.mois_id),
    montant: Number(formData.montant),
    school_year_id: Number(formData.school_year_id),
    description: formData.description.trim() || null,
    author_id: Number(currentUserId),
  }

  try {
    if (isEditMode.value) {
      // Mode édition
      await putData(API_ROUTES.UPDATE_SALAIRE(Number(salaireId.value)), payload)

      if (updateError.value) {
        console.error('❌ Erreur API:', updateError.value)
        showCustomToast({
          message: updateError.value,
          type: 'error',
        })
        return
      }

      showCustomToast({
        message: 'Salaire modifié avec succès !',
        type: 'success',
      })
    } else {
      // Mode création
      await postData(API_ROUTES.CREATE_SALAIRE, payload)

      if (submitError.value) {
        console.error('❌ Erreur API:', submitError.value)
        showCustomToast({
          message: submitError.value,
          type: 'error',
        })
        return
      }

      showCustomToast({
        message: 'Salaire enregistré avec succès !',
        type: 'success',
      })

      // Réinitialiser le formulaire seulement en mode création
      resetForm()
    }

    // Rediriger vers la liste
    setTimeout(() => {
      router.push('/rh/saisie/salaire')
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
  formData.personal_id = undefined
  formData.mois_id = undefined
  formData.montant = undefined
  formData.school_year_id = undefined
  formData.description = ''
}

// Annuler et retourner
function handleCancel() {
  router.push('/rh/saisie/salaire')
}

// Chargement initial
onMounted(async () => {
  // Charger les données du salaire si en mode édition
  if (isEditMode.value) {
    await fetchSalaireData()
  }
  await Promise.all([fetchPersonnels(), fetchFonctions(), fetchSchoolYears(), fetchMois()])
})

// Textes dynamiques selon le mode
const pageTitle = computed(() =>
  isEditMode.value ? 'Modifier le Paiement de Salaire' : 'Nouveau Paiement de Salaire',
)
const breadcrumbLabel = computed(() =>
  isEditMode.value ? 'Modifier Paiement' : 'Nouveau Paiement',
)
const submitButtonText = computed(() =>
  isEditMode.value ? 'Modifier le paiement' : 'Enregistrer le paiement',
)
const submittingText = computed(() => (isEditMode.value ? 'Modification...' : 'Enregistrement...'))
</script>

<template>
  <DashFormLayout
    :title="pageTitle"
    link-back="/rh/saisie/salaire"
    group-route="/rh/saisie/personnel"
    module="rh"
    :breadcrumb="[
      { label: 'GRH', href: '/rh' },
      { label: 'Salaire', href: '/rh/saisie/salaire' },
      { label: breadcrumbLabel, isActive: true },
    ]"
  >
    <!-- Loader pendant le chargement des données en mode édition -->
    <div v-if="loadingSalaire" class="flex items-center justify-center py-20">
      <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary"></div>
      <span class="ml-3 text-gray-600">Chargement des données...</span>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations du personnel"
      >
        <InputWrapper>
          <Label class="text-sm font-medium">
            Personnel
            <SpanRequired />
          </Label>
          <Select v-model="formData.personal_id" :disabled="loadingPersonnels">
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
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Détails du paiement"
      >
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

        <InputWrapper>
          <Label class="text-sm font-medium">
            Mois
            <SpanRequired />
          </Label>
          <Select v-model="formData.mois_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionner le mois" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem v-for="m in mois" :key="m.id" :value="m.id">
                  {{ m.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm font-medium">
            Montant (FC)
            <SpanRequired />
          </Label>
          <Input
            v-model.number="formData.montant"
            type="number"
            min="0"
            step="0.01"
            placeholder="Montant du salaire"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all rounded-md"
          />
          <p v-if="formData.montant" class="text-xs text-gray-600 mt-1">
            {{ formatCurrency(formData.montant) }}
          </p>
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection class="grid gap-y-6" title="Informations complémentaires">
        <InputWrapper>
          <Label class="text-sm font-medium"> Description / Observations </Label>
          <Textarea
            v-model="formData.description"
            placeholder="Notes ou observations sur ce paiement..."
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
          <span v-else class="flex iconify hugeicons--money-bag-02 mr-1.5"></span>
          {{ isSubmitting ? submittingText : submitButtonText }}
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
