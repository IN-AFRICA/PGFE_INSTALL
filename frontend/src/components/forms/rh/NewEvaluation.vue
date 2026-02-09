<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import { Textarea } from '@/components/ui/textarea'
import CustomDatePicker from '@/components/ui/CustomDatePicker.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import { Button } from '@/components/ui/button'
import { API_ROUTES } from '@/utils/constants/api_route'
import { usePostApi } from '@/composables/usePostApi'
import { useGetApi } from '@/composables/useGetApi'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Données du formulaire
const formData = reactive({
  academic_personal_id: undefined as number | undefined,
  critiques: '',
  c1_quantite_travail: undefined as number | undefined,
  c2_theorie_pratique: undefined as number | undefined,
  c3_determ_ress_perso: undefined as number | undefined,
  c4_ponctualite: undefined as number | undefined,
  c5_dr_att_posit_collab: undefined as number | undefined,
  school_year_id: undefined as number | undefined,
  semester_id: undefined as number | undefined,
})

// Chargement des données
const {
  data: personnelsData,
  fetchData: fetchPersonnels,
  loading: loadingPersonnels,
} = useGetApi(API_ROUTES.GET_PERSONALS)
const {
  data: schoolYearsData,
  fetchData: fetchSchoolYears,
  loading: loadingSchoolYears,
} = useGetApi(API_ROUTES.GET_SCHOOL_YEARS)
const {
  data: semestersData,
  fetchData: fetchSemesters,
  loading: loadingSemesters,
} = useGetApi(API_ROUTES.GET_SEMESTERS)
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
    fonction: p.fonction.title,
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

// Semestres formatés
const semesters = computed(() => {
  const data = Array.isArray(semestersData.value)
    ? semestersData.value
    : (semestersData.value as any)?.data || []

  return data.map((s: any) => ({
    id: s.id,
    label: s.name || s.libelle || s.title || `Semestre ${s.id}`,
  }))
})

// Écoles formatées
const schools = computed(() => {
  const data = Array.isArray(schoolsData.value)
    ? schoolsData.value
    : (schoolsData.value as any)?.data || []

  return data.map((school: any) => ({
    id: school.id,
    label:
      school.name || school.libelle || school.title || school.denomination || `École ${school.id}`,
  }))
})

// Personnel sélectionné
const selectedPersonnel = computed(() => {
  if (!formData.academic_personal_id) return null
  return personnels.value.find((p: any) => Number(p.id) === Number(formData.academic_personal_id))
})

// Soumission du formulaire
const { loading: submitting, error: submitError, postData } = usePostApi()

// Validation du formulaire
const isFormValid = computed(() => {
  return (
    formData.academic_personal_id !== undefined &&
    formData.critiques.trim() !== '' &&
    formData.critiques.length <= 255 &&
    formData.c1_quantite_travail !== undefined &&
    formData.c1_quantite_travail >= 0 &&
    formData.c2_theorie_pratique !== undefined &&
    formData.c2_theorie_pratique >= 0 &&
    formData.c3_determ_ress_perso !== undefined &&
    formData.c3_determ_ress_perso >= 0 &&
    formData.c4_ponctualite !== undefined &&
    formData.c4_ponctualite >= 0 &&
    formData.c5_dr_att_posit_collab !== undefined &&
    formData.c5_dr_att_posit_collab >= 0 &&
    formData.school_year_id !== undefined &&
    formData.semester_id !== undefined
  )
})

// Soumettre le formulaire
async function handleSubmit() {
  if (!isFormValid.value) {
    showCustomToast({
      message: 'Veuillez remplir tous les champs obligatoires correctement.',
      type: 'error',
    })
    return
  }

  // Récupérer l'auteur depuis le store
  const currentUser = authStore.user
  const authorId = currentUser?.id || currentUser?.user?.id || 1

  const payload = {
    critiques: formData.critiques.trim(),
    c1_quantite_travail: Number(formData.c1_quantite_travail),
    c2_theorie_pratique: Number(formData.c2_theorie_pratique),
    c3_determ_ress_perso: Number(formData.c3_determ_ress_perso),
    c4_ponctualite: Number(formData.c4_ponctualite),
    c5_dr_att_posit_collab: Number(formData.c5_dr_att_posit_collab),
    school_year_id: Number(formData.school_year_id),
    semester_id: Number(formData.semester_id),
    author_id: Number(authorId),
    academic_personal_id: Number(formData.academic_personal_id),
  }

  try {
    await postData(API_ROUTES.CREATE_EVALUATION, payload)

    if (submitError.value) {
      console.error('❌ Erreur API:', submitError.value)
      showCustomToast({
        message: submitError.value,
        type: 'error',
      })
      return
    }

    showCustomToast({
      message: 'Évaluation créée avec succès !',
      type: 'success',
    })

    // Réinitialiser le formulaire
    resetForm()

    // Rediriger vers la liste
    setTimeout(() => {
      router.push('/rh/saisie/evaluation')
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
  formData.critiques = ''
  formData.c1_quantite_travail = undefined
  formData.c2_theorie_pratique = undefined
  formData.c3_determ_ress_perso = undefined
  formData.c4_ponctualite = undefined
  formData.c5_dr_att_posit_collab = undefined
  formData.school_year_id = undefined
  formData.semester_id = undefined
}

// Annuler et retourner
function handleCancel() {
  router.push('/rh/saisie/evaluation')
}

// Chargement initial
onMounted(async () => {
  await Promise.all([
    fetchPersonnels(),
    fetchSchoolYears(),
    fetchSemesters(),
    fetchSchools(),
    fetchFonctions(),
  ])
})

// Handlers pour messages de validation personnalisés en français
const handleInvalidNumber = (e: Event) => {
  const t = e.target as HTMLInputElement | null
  if (!t) return
  if (t.validity.rangeOverflow) {
    t.setCustomValidity('La valeur doit être inférieure ou égale à 20.')
  } else if (t.validity.rangeUnderflow) {
    t.setCustomValidity('La valeur doit être supérieure ou égale à 0.')
  } else {
    t.setCustomValidity('Valeur invalide.')
  }
}

const clearNumberValidity = (e: Event) => {
  const t = e.target as HTMLInputElement | null
  if (!t) return
  t.setCustomValidity('')
}
</script>

<template>
  <DashFormLayout
    title="Nouvelle évaluation"
    link-back="/rh/saisie/evaluation"
    group-route="/rh/saisie/personnel"
    module="rh"
    :breadcrumb="[
      { label: 'GRH', href: '/rh' },
      { label: 'Evaluation', href: '/rh/saisie/evaluation' },
      { label: 'Nouvelle évaluation', isActive: true },
    ]"
  >
    <form @submit.prevent="handleSubmit" class="w-full flex flex-col space-y-8">
      <FormSection
        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10"
        title="Informations sur l'employé"
      >
        <InputWrapper>
          <Label class="text-sm">
            Nom du personnel
            <SpanRequired />
          </Label>
          <Select v-model="formData.academic_personal_id" :disabled="loadingPersonnels">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionner un employé" />
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
            :value="selectedPersonnel?.matricule || ''"
            class="bg-gray-100 transition-all h-10 rounded-md"
            readonly
            placeholder="Sélectionner d'abord un personnel"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">Fonction</Label>
          <Input
            :value="selectedPersonnel?.fonction || ''"
            class="bg-gray-100 transition-all h-10 rounded-md"
            readonly
            placeholder="Sélectionner d'abord un personnel"
          />
        </InputWrapper>

        <InputWrapper>
          <Label class="text-sm">
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
          <Label class="text-sm">
            Semestre
            <SpanRequired />
          </Label>
          <Select v-model="formData.semester_id" :disabled="loadingSemesters">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionner le semestre" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem v-for="semester in semesters" :key="semester.id" :value="semester.id">
                  {{ semester.label }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper class="lg:col-span-3">
          <Label class="text-sm">
            Critiques / Observations
            <SpanRequired />
          </Label>
          <Textarea
            v-model="formData.critiques"
            class="bg-white transition-all rounded-md min-h-[100px]"
            placeholder="Remarques ou notes particulières sur cette évaluation..."
            maxlength="255"
          />
          <p class="text-xs text-gray-500 mt-1">{{ formData.critiques.length }}/255 caractères</p>
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <FormSection class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10" title="Evaluation">
        <InputWrapper>
          <Label class="text-sm">
            C1 Quantité & Qualité de travail [/20]
            <SpanRequired />
          </Label>
          <Input
            v-model.number="formData.c1_quantite_travail"
            @invalid="handleInvalidNumber"
            @input="clearNumberValidity"
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            placeholder="0"
            min="0"
            max="20"
            step="0.5"
          />
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            C2 Maitrise des connaissances théoriques et pratiques [/20]
            <SpanRequired />
          </Label>
          <Input
            v-model.number="formData.c2_theorie_pratique"
            @invalid="handleInvalidNumber"
            @input="clearNumberValidity"
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            placeholder="0"
            min="0"
            max="20"
            step="0.5"
          />
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            C3 Détermination d'utiliser des ressources personnelles [/20]
            <SpanRequired />
          </Label>
          <Input
            v-model.number="formData.c3_determ_ress_perso"
            @invalid="handleInvalidNumber"
            @input="clearNumberValidity"
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            placeholder="0"
            min="0"
            max="20"
            step="0.5"
          />
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            C4 Ponctualité [/20]
            <SpanRequired />
          </Label>
          <Input
            v-model.number="formData.c4_ponctualite"
            @invalid="handleInvalidNumber"
            @input="clearNumberValidity"
            type="number"
            class="bg-white transition-all h-10 rounded-md"
            placeholder="0"
            min="0"
            max="20"
            step="0.5"
          />
        </InputWrapper>
        <InputWrapper>
          <Label class="text-sm">
            C5 Droiture, attitude positive, collaborations [/20]
            <SpanRequired />
          </Label>
          <Input
            v-model.number="formData.c5_dr_att_posit_collab"
            @invalid="handleInvalidNumber"
            @input="clearNumberValidity"
            class="bg-white transition-all h-10 rounded-md"
            placeholder="0"
            type="number"
            min="0"
            max="20"
            step="0.5"
          />
        </InputWrapper>
      </FormSection>

      <div class="w-full flex h-px bg-gray-300"></div>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel" :disabled="submitting">
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit" :disabled="!isFormValid || submitting">
          <span
            v-if="submitting"
            class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-white mr-1.5"
          ></span>
          <span v-else class="flex iconify hugeicons--money-bag-02 mr-1.5"></span>
          {{ submitting ? 'Enregistrement...' : 'Enregistrer' }}
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>

<style scoped></style>
