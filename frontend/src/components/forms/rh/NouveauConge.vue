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
const congeId = computed(() => route.params.id as string | undefined)
const isEditMode = computed(() => !!congeId.value)
const loadingConge = ref(false)

// Données du formulaire
const formData = reactive({
  personal_id: undefined as number | undefined,
  jour_demand: undefined as number | undefined,
  description: '',
  creer_a: new Date().toISOString().split('T')[0], // Date d'aujourd'hui par défaut
})

// Chargement des personnels
const {
  data: personnelsData,
  fetchData: fetchPersonnels,
  loading: loadingPersonnels,
} = useGetApi(API_ROUTES.GET_PERSONALS)

// Chargement des fonctions pour l'affichage
const { data: fonctionsData, fetchData: fetchFonctions } = useGetApi(API_ROUTES.GET_FONCTIONS)

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

// Personnel sélectionné
const selectedPersonnel = computed(() => {
  if (!formData.personal_id) return null
  return personnels.value.find((p: any) => p.id === formData.personal_id)
})

// Soumission du formulaire
const { loading: submitting, error: submitError, postData } = usePostApi()
const { loading: updating, error: updateError, putData } = usePutApi()

// Fetch existing congé data for edit mode
async function fetchCongeData(id: string) {
  loadingConge.value = true
  try {
    const response = await fetch(
      `https://pgfe-back.inafrica.tech/api/v1/${API_ROUTES.GET_ONE_PERSONNEL_CONGE(Number(id))}`,
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
          'Content-Type': 'application/json',
        },
      },
    )
    const result = await response.json()

    if (result.success && result.data) {
      const data = result.data
      formData.personal_id = Number(data.academic_personal_id)
      formData.jour_demand = Number(data.jour_demand)
      formData.description = data.description || ''
      formData.creer_a = data.creer_a || new Date().toISOString().split('T')[0]
    }
  } catch (error) {
    console.error('Erreur lors du chargement du congé:', error)
    showCustomToast({
      message: 'Erreur lors du chargement des données du congé.',
      type: 'error',
    })
  } finally {
    loadingConge.value = false
  }
}

// Validation du formulaire
const isFormValid = computed(() => {
  return (
    formData.personal_id !== undefined &&
    formData.jour_demand !== undefined &&
    formData.jour_demand >= 1 &&
    formData.creer_a !== ''
  )
})

// Computed for loading state
const isSubmitting = computed(() => submitting.value || updating.value)

// Soumettre le formulaire
async function handleSubmit() {
  if (!isFormValid.value) {
    showCustomToast({
      message: 'Veuillez remplir tous les champs obligatoires correctement.',
      type: 'error',
    })
    return
  }

  // Format date as YYYY-MM-DD as expected by API
  const dateObj = new Date(formData.creer_a)
  const day = String(dateObj.getDate()).padStart(2, '0')
  const month = String(dateObj.getMonth() + 1).padStart(2, '0')
  const year = dateObj.getFullYear()
  const formattedDate = `${year}-${month}-${day}`

  const payload = {
    academic_personal_id: Number(formData.personal_id),
    jour_demand: Number(formData.jour_demand),
    creer_a: formattedDate,
    description: formData.description.trim() || '',
  }

  try {
    if (isEditMode.value && congeId.value) {
      // Mode édition: PUT request
      await putData(API_ROUTES.UPDATE_PERSONNEL_CONGE(Number(congeId.value)), payload)

      if (updateError.value) {
        console.error('❌ Erreur API:', updateError.value)
        showCustomToast({
          message: updateError.value,
          type: 'error',
        })
        return
      }

      showCustomToast({
        message: 'Congé modifié avec succès !',
        type: 'success',
      })
    } else {
      // Mode création: POST request
      await postData(API_ROUTES.CREATE_PERSONNEL_CONGE, payload)

      if (submitError.value) {
        console.error('❌ Erreur API:', submitError.value)
        showCustomToast({
          message: submitError.value,
          type: 'error',
        })
        return
      }

      showCustomToast({
        message: 'Congé créé avec succès !',
        type: 'success',
      })

      // Réinitialiser le formulaire uniquement en mode création
      resetForm()
    }

    // Rediriger vers la liste
    setTimeout(() => {
      router.push('/rh/saisie/conge')
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
  formData.jour_demand = undefined
  formData.description = ''
  formData.creer_a = new Date().toISOString().split('T')[0]
}

// Annuler et retourner
function handleCancel() {
  router.push('/rh/saisie/conge')
}

// Chargement initial
onMounted(async () => {
  await Promise.all([fetchPersonnels(), fetchFonctions()])

  // En mode édition, charger les données existantes
  if (isEditMode.value && congeId.value) {
    await fetchCongeData(congeId.value)
  }
})
</script>

<template>
  <DashFormLayout
    :title="isEditMode ? 'Modifier le congé' : 'Nouveau congé'"
    link-back="/rh/saisie/conge"
    group-route="/rh/saisie/personnel"
    module="rh"
    :breadcrumb="[
      { label: 'GRH', href: '/rh' },
      { label: 'Congé', href: '/rh/saisie/conge' },
      { label: isEditMode ? 'Modifier le congé' : 'Nouveau congé', isActive: true },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations générales"
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

        <InputWrapper>
          <Label class="text-sm font-medium">
            Date de demande
            <SpanRequired />
          </Label>
          <Input
            type="date"
            v-model="formData.creer_a"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all rounded-md"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm font-medium">
            Durée (jours)
            <SpanRequired />
          </Label>
          <Input
            v-model.number="formData.jour_demand"
            type="number"
            min="1"
            placeholder="Nombre de jours"
            class="w-full h-10 border border-gray-200/40 bg-white transition-all rounded-md"
          />
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection class="grid gap-y-6" title="Détails">
        <InputWrapper>
          <Label class="text-sm font-medium"> Motif / Description </Label>
          <Textarea
            v-model="formData.description"
            placeholder="Décrivez le motif de la demande de congé..."
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
        <Button type="submit" :disabled="!isFormValid || isSubmitting || loadingConge">
          <span
            v-if="isSubmitting"
            class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-white mr-1.5"
          ></span>
          <span v-else class="flex iconify hugeicons--money-bag-02 mr-1.5"></span>
          {{
            isSubmitting
              ? isEditMode
                ? 'Modification...'
                : 'Enregistrement...'
              : isEditMode
                ? 'Modifier le congé'
                : 'Soumettre la demande'
          }}
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
