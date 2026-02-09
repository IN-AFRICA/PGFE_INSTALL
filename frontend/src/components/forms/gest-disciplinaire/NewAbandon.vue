<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  SelectGroup,
  SelectLabel,
} from '@/components/ui/select'
import { onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import abandonCasesService from '@/services/abandonCasesService'
import SchoolYearService from '@/services/SchoolYearService'
import ClassroomService from '@/services/ClassroomService'
import FiliaireService from '@/services/FiliaireService'
import StudentsService from '@/services/StudentsService'
import SemestreService from '@/services/SemestreService'
import type { AbandonCasePayload } from '@/types'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { toast } from 'vue-sonner'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { computed } from 'vue'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const router = useRouter()
const route = useRoute()

const isEditMode = ref(false)
const abandonId = ref<number | null>(null)

const formData = ref<AbandonCasePayload>({
  school_year_id: 0,
  classroom_id: 0,
  filiere_id: null,
  semester_id: 1,
  student_id: 0,
  comment: '',
})

// Utilisation de useGetApi pour charger les données
const { data: rawSchoolYears, fetchData: fetchSchoolYears } = useGetApi(API_ROUTES.GET_SCHOOL_YEARS)
const { data: rawClassrooms, fetchData: fetchClassrooms } = useGetApi(API_ROUTES.GET_CLASSROOMS)
const { data: rawFilieres, fetchData: fetchFilieres } = useGetApi(API_ROUTES.GET_FILLIERES)
const { data: rawSemestres, fetchData: fetchSemestres } = useGetApi(API_ROUTES.GET_SEMESTRE)
const { data: rawStudents, fetchData: fetchStudents } = useGetApi(
  API_ROUTES.GET_STUDENT_REGISTRATIONS,
)
const studentSearch = ref('')

// Computed properties pour transformer les données brutes en format utilisable pour les Select
const anneesScolaires = computed(() => {
  const data = rawSchoolYears.value
  const items = Array.isArray(data) ? data : (data as any)?.years || (data as any)?.data || []
  return items.map((y: any) => ({ id: y.id, label: y.name }))
})

const classes = computed(() => {
  const data = rawClassrooms.value
  const items = Array.isArray(data)
    ? data
    : (data as any)?.classrooms?.data || (data as any)?.data || []
  return items.map((c: any) => ({
    id: c.id,
    name: c.name,
    filiere_id: c.filiere_id || c.filiaire_id || c.academic_level?.cycle?.filiaire_id,
  }))
})

const filieres = computed(() => {
  const data = rawFilieres.value
  const items = Array.isArray(data) ? data : (data as any)?.data || []
  return items.map((f: any) => ({ id: f.id, name: f.name }))
})

const semestres = computed(() => {
  const data = rawSemestres.value
  const items = Array.isArray(data) ? data : (data as any)?.data || []
  return items.map((s: any) => ({ id: s.id, label: s.name }))
})

const students = computed(() => {
  const data = rawStudents.value
  // Gestion spécifique pour les étudiants qui sont souvent dans data.students.data ou data.data
  const items = Array.isArray(data)
    ? data
    : (data as any)?.students?.data || (data as any)?.data || []
  return items.map((st: any) => ({
    id: st.student_id || st.id,
    full_name:
      st.student_name || `${st?.name ?? ''} ${st?.firstname ?? ''} ${st?.lastname ?? ''}`.trim(),
    classroom_id: st.classroom_id,
  }))
})

// Filtrage hiérarchique
const filteredClasses = computed(() => {
  if (!formData.value.filiere_id) return classes.value
  return classes.value.filter((c) => String(c.filiere_id) === String(formData.value.filiere_id))
})

const filteredStudentsList = computed(() => {
  let list = students.value
  if (formData.value.classroom_id) {
    list = list.filter((s) => String(s.classroom_id) === String(formData.value.classroom_id))
  }
  if (studentSearch.value) {
    list = list.filter((s) => s.full_name.toLowerCase().includes(studentSearch.value.toLowerCase()))
  }
  return list
})

// Reset en cascade
import { watch } from 'vue'
watch(
  () => formData.value.filiere_id,
  () => {
    // Si on change de filière, on reset la classe et l'élève (sauf si edit mode initial)
    // On pourrait ajouter une condition pour ne pas reset au chargement initial si déjà rempli
  },
)

watch(
  () => formData.value.classroom_id,
  () => {
    // Si on change de classe, on reset l'élève
  },
)

onMounted(async () => {
  await fetchSchoolYears()

  const activeYearId = getActiveSchoolYearId()
  if (activeYearId) {
    formData.value.school_year_id = Number(activeYearId)
  }

  fetchClassrooms({ per_page: 1000 })
  fetchFilieres()
  fetchStudents({ per_page: 1000 })
  fetchSemestres()

  if (route.params.id) {
    isEditMode.value = true
    abandonId.value = Number(route.params.id)
    await loadAbandonDetails(abandonId.value)
    await loadAbandonDetails(abandonId.value)
  }
})

watch(semestres, (newVal) => {
  if (newVal.length > 0 && !formData.value.semester_id) {
    formData.value.semester_id = newVal[0].id
  }
})

function getActiveSchoolYearId(): string | number | null {
  const raw: any = rawSchoolYears.value
  let list: any[] = []
  if (Array.isArray(raw)) list = raw
  else if (raw && Array.isArray(raw.years)) list = raw.years
  else if (raw && Array.isArray(raw.data)) list = raw.data

  if (!Array.isArray(list) || list.length === 0) return null

  // Cherche des flags communs ou par nom
  const candidate = list.find(
    (y: any) =>
      y.active === true ||
      y.is_active === true ||
      y.current === true ||
      y.is_current === true ||
      y.status === 'active',
  )
  if (candidate?.id) return candidate.id

  const textCandidate = list.find(
    (y: any) =>
      String(y.name || '')
        .toLowerCase()
        .includes('en cours') ||
      String(y.name || '')
        .toLowerCase()
        .includes('actuelle'),
  )
  if (textCandidate?.id) return textCandidate.id

  return list[0]?.id ?? null
}

async function loadAbandonDetails(id: number) {
  try {
    const res = await abandonCasesService.getAbandonCaseById(id)
    if (res?.data) {
      formData.value = {
        school_year_id: res.data?.data.school_year_id,
        classroom_id: res.data?.data.classroom_id,
        filiere_id: res.data?.data.filiere_id,
        semester_id: res.data?.data.semester_id,
        student_id: res.data?.data.student_id,
        comment: res.data?.data.comment,
      }
      showCustomToast({ message: 'Données chargées avec succès', type: 'success' })
    }
  } catch (error) {
    showCustomToast({ message: 'Erreur lors du chargement des données', type: 'error' })
  }
}

function handleCancel() {
  router.push('/apprenants/operations/gestion-disciplinaire/abandons')
}

const loading = ref(false)

async function handleSubmit() {
  loading.value = true
  try {
    if (isEditMode.value && abandonId.value) {
      await abandonCasesService.updateAbandonCase(abandonId.value, formData.value)
      showCustomToast({ message: 'Abandon mis à jour avec succès', type: 'success' })
    } else {
      await abandonCasesService.createAbandonCase(formData.value)
      showCustomToast({ message: 'Abandon créé avec succès', type: 'success' })
    }
    router.push('/apprenants/operations/gestion-disciplinaire/abandons')
  } catch (error: any) {
    console.error('Form submission error:', error)
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors
      let message = 'Erreur de validation'
      if (errors) {
        message = Object.values(errors).flat().join('\n')
      } else if (error.response.data.message) {
        message = error.response.data.message
      }
      showCustomToast({ message: message, type: 'error' })
    } else {
      showCustomToast({ message: "Erreur lors de l'enregistrement", type: 'error' })
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <DashFormLayout
    :link-back="'/apprenants/operations/gestion-disciplinaire/abandons'"
    :title="isEditMode ? 'Modifier Abandon' : 'Nouvel Abandon'"
    group-route="/apprenants/operations"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Gestion Disciplinaire', href: '/apprenants/operations/gestion-disciplinaire' },
      {
        label: 'Liste des Abandons',
        href: '/apprenants/operations/gestion-disciplinaire/abandons',
      },
      { label: isEditMode ? 'Modifier Abandon' : 'Nouvel Abandon', href: '#' },
    ]"
  >
    <form class="w-full flex flex-col space-y-8" @submit.prevent="handleSubmit">
      <FormSection title="" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10">
        <!-- Année scolaire -->
        <InputWrapper>
          <Label for="school_year_id">Année scolaire<SpanRequired /></Label>
          <Select v-model="formData.school_year_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  isEditMode
                    ? anneesScolaires.find((a) => a.id == formData.school_year_id)?.label
                    : `Sélectionnez l'année`
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="annee in anneesScolaires" :key="annee.id" :value="annee.id">
                {{ annee.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <!-- Section (Filière) - Déplacé avant Classe -->
        <InputWrapper>
          <Label for="filiere">Section<SpanRequired /></Label>
          <Select v-model="formData.filiere_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  isEditMode
                    ? filieres.find((f) => f.id == formData.filiere_id)?.name
                    : `Sélectionnez la section`
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="filiere in filieres" :key="filiere.id" :value="filiere.id">
                {{ filiere.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <!-- Classe - Filtrée par Section -->
        <InputWrapper>
          <Label for="classroom">Classe<SpanRequired /></Label>
          <Select v-model="formData.classroom_id" required :disabled="!formData.filiere_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  formData.filiere_id
                    ? isEditMode
                      ? classes.find((c) => c.id == formData.classroom_id)?.name
                      : `Sélectionnez la classe`
                    : `Sélectionnez d'abord une section`
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="classe in filteredClasses" :key="classe.id" :value="classe.id">
                {{ classe.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <!-- Semestre -->
        <InputWrapper>
          <Label for="semester">Semestre<SpanRequired /></Label>
          <Select v-model="formData.semester_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  isEditMode
                    ? semestres.find((s) => s.id == formData.semester_id)?.label
                    : `Sélectionnez le semestre`
                "
              />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="semestre in semestres" :key="semestre.id" :value="semestre.id">
                {{ semestre.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <!-- Élève - Filtré par Classe -->
        <InputWrapper>
          <Label for="student">Élève<SpanRequired /></Label>
          <Select v-model="formData.student_id" required :disabled="!formData.classroom_id">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  isEditMode
                    ? students.find((s) => s.id == formData.student_id)?.full_name
                    : `Sélectionnez l'élève`
                "
              />
            </SelectTrigger>
            <SelectContent>
              <div class="p-2">
                <Input
                  :value="studentSearch"
                  placeholder="Rechercher un élève..."
                  class="w-full h-8"
                  @input="(e: any) => (studentSearch = e.target.value)"
                  @keydown.stop
                />
              </div>

              <SelectItem
                v-for="student in filteredStudentsList"
                :key="student.id"
                :value="student.id"
              >
                {{ student.full_name }}
              </SelectItem>

              <div v-if="filteredStudentsList.length === 0" class="p-2 text-gray-500 text-sm">
                Aucun apprenant trouvé
              </div>
            </SelectContent>
          </Select>
        </InputWrapper>

        <!-- Commentaire -->
        <InputWrapper class="lg:col-span-3">
          <Label for="comment">Commentaire<SpanRequired /></Label>
          <Textarea
            v-model="formData.comment"
            placeholder="Décrivez les raisons de l'abandon..."
            required
            class="w-full min-h-[100px]"
          />
        </InputWrapper>
      </FormSection>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>
        <Button type="submit" :disabled="loading">
          <span v-if="loading" class="flex items-center gap-2">
            <IconifySpinner size="sm" />
            <span>Enregistrement...</span>
          </span>
          <span v-else>
            {{ isEditMode ? "Mettre à jour l'abandon" : "Enregistrer l'abandon" }}
          </span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
