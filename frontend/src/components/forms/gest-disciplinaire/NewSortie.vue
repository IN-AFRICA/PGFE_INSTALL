<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import CustomDatePicker from '@/components/ui/CustomDatePicker.vue'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import StudentExitService from '@/services/StudentExitService'

const router = useRouter()
const route = useRoute()

// Check if editing
const isEditing = computed(() => Boolean(route.query.edit))
const exitId = computed(() => String(route.query.edit || route.params.id || ''))

const formData = ref({
  date: '',
  student_id: '',
  exit_time: '',
  motif: '',
  school_year_id: '',
  semester: '',
})

// API calls for lookups
const { data: schoolYearsApi, fetchData: fetchSchoolYears } = useGetApi<any>(
  API_ROUTES.GET_SCHOOL_YEARS,
)
const { data: studentsApi, fetchData: fetchStudents } = useGetApi<any>(API_ROUTES.GET_STUDENTS)

// Normalize API responses
const schoolYears = computed(() => {
  const v: any = schoolYearsApi?.value
  const list = Array.isArray(v) ? v : (v?.years ?? [])
  return list.map((y: any) => ({ id: String(y.id), name: y.name }))
})

const students = computed(() => {
  const v: any = studentsApi?.value
  const list = Array.isArray(v) ? v : (v?.students?.data ?? v?.data ?? [])
  return list.map((s: any) => ({
    id: String(s.id),
    name: `${s.name ?? ''} ${s.firstname ?? ''}`.trim(),
  }))
})

// Search state for students (used by the Select search input)
const studentSearch = ref('')
const filteredStudents = ref<any[]>([])

function filterStudents(text: string) {
  studentSearch.value = text || ''
  if (!studentSearch.value) {
    filteredStudents.value = students.value || []
  } else {
    const q = studentSearch.value.toLowerCase()
    filteredStudents.value = (students.value || []).filter((s) =>
      (s.name || '').toLowerCase().includes(q),
    )
  }
}

const semestres = ['1er Semestre', '2ème Semestre']

onMounted(async () => {
  await Promise.all([fetchSchoolYears(), fetchStudents()])

  // Initialize filtered list after fetching students
  filteredStudents.value = students.value || []

  // Préselectionner automatiquement l'année scolaire active (si on n'est pas en édition)
  if (!isEditing.value) {
    const activeId = getActiveSchoolYearId()
    if (activeId) formData.value.school_year_id = String(activeId)
  }

  // Load data if editing
  if (isEditing.value) {
    await loadExitData()
  }
})

// Load exit data for editing
const loadExitData = async () => {
  try {
    // 1) Try via dedicated service (usually returns { data: {...} })
    let exitData: any = null
    try {
      const res = await StudentExitService.getStudentExitById(Number(exitId.value))
      const candidate =
        res?.data && typeof res.data === 'object' && 'data' in res.data
          ? (res.data as any).data
          : res?.data
      exitData = candidate
    } catch (e) {
      // ignore, will fallback
    }

    // 2) If still not an object with id, fallback to list endpoint and find by id
    if (!exitData || typeof exitData !== 'object' || !('id' in exitData)) {
      const { data, fetchData } = useGetApi<any>(API_ROUTES.GET_STUDENT_EXIT)
      await fetchData()
      const rawList = data.value
      const list = Array.isArray(rawList) ? rawList : (rawList?.data ?? [])
      exitData = list.find((it: any) => String(it.id) === exitId.value) || null
    }

    if (exitData) {
      formData.value = {
        // Backend usually returns YYYY-MM-DD; if ISO, truncate
        date: (exitData.date?.includes('T') ? exitData.date.split('T')[0] : exitData.date) || '',
        student_id: String(exitData.student_id || ''),
        // Normalize to HH:mm (backend expects H:i)
        exit_time: exitData.exit_time ? String(exitData.exit_time).slice(0, 5) : '',
        motif: exitData.motif || '',
        school_year_id: String(exitData.school_year_id || ''),
        semester: exitData.semester || '',
      }
    }
  } catch (error) {
    console.error('Error loading exit data:', error)
  }
}

const {
  postData,
  loading: creating,
  error: createError,
  success: createSuccess,
} = usePostApi<any>()
const { putData, loading: updating, error: updateError, success: updateSuccess } = usePutApi<any>()

const handleSubmit = async () => {
  // Debug - afficher les valeurs des champs
  console.log('FormData values:', formData.value)

  // Validation complète de tous les champs requis
  const missingFields = []
  if (!formData.value.date) missingFields.push('date')
  if (!formData.value.student_id) missingFields.push('student_id')
  if (!formData.value.exit_time) missingFields.push('exit_time')
  if (!formData.value.motif) missingFields.push('motif')
  if (!formData.value.school_year_id) missingFields.push('school_year_id')
  if (!formData.value.semester) missingFields.push('semester')

  if (missingFields.length > 0) {
    console.log('Missing fields:', missingFields)
    showCustomToast({ message: `Champs manquants: ${missingFields.join(', ')}`, type: 'error' })
    return
  }

  const payload = {
    date: formData.value.date,
    student_id: Number(formData.value.student_id),
    // Ensure HH:mm format for backend (H:i)
    exit_time: formData.value.exit_time ? String(formData.value.exit_time).slice(0, 5) : '',
    motif: formData.value.motif,
    school_year_id: Number(formData.value.school_year_id),
    semester: formData.value.semester,
  }

  if (isEditing.value) {
    await putData(API_ROUTES.UPDATE_STUDENT_EXIT.replace(':studentExitId', exitId.value), payload)

    if (updateError.value) {
      showCustomToast({ message: updateError.value, type: 'error' })
    } else if (updateSuccess.value) {
      showCustomToast({ message: 'Sortie mise à jour avec succès', type: 'success' })
      eventBus.emit('studentExitUpdated')
      router.push('/apprenants/operations/gestion-disciplinaire')
    }
  } else {
    await postData(API_ROUTES.CREATE_STUDENT_EXIT, payload)

    if (createError.value) {
      showCustomToast({ message: createError.value, type: 'error' })
    } else if (createSuccess.value) {
      showCustomToast({ message: 'Sortie créée avec succès', type: 'success' })
      eventBus.emit('studentExitUpdated')
      router.push('/apprenants/operations/gestion-disciplinaire')
    }
  }
}

const handleCancel = () => {
  router.push('/apprenants/operations/gestion-disciplinaire')
}

// Détecte l'année scolaire active dans la réponse brute de l'API `GET_SCHOOL_YEARS`
function getActiveSchoolYearId(): string | number | null {
  const raw: any = schoolYearsApi?.value
  let list: any[] = []
  if (Array.isArray(raw)) list = raw
  else if (raw && Array.isArray(raw.years)) list = raw.years

  if (!Array.isArray(list) || list.length === 0) return null

  // Cherche des flags communs indiquant l'année active
  const candidate = list.find(
    (y: any) =>
      y.active === true ||
      y.is_active === true ||
      y.current === true ||
      y.is_current === true ||
      y.status === 'active',
  )

  if (candidate && candidate.id !== undefined) return candidate.id

  // Si aucun flag, tente de trouver un élément marqué 'en cours' dans le nom
  const textCandidate = list.find(
    (y: any) =>
      String(y.name || '')
        .toLowerCase()
        .includes('en cours') ||
      String(y.name || '')
        .toLowerCase()
        .includes('actuelle'),
  )
  if (textCandidate && textCandidate.id !== undefined) return textCandidate.id

  // Fallback: retourne le premier élément
  return list[0].id ?? null
}
</script>

<template>
  <DashFormLayout
    :link-back="'/apprenants/operations/gestion-disciplinaire'"
    :title="isEditing ? 'Édition de Sortie d\'\u00c9lève' : 'Nouvelle Sortie d\'\u00c9lève'"
    group-route="/apprenants/operations"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Gestion Disciplinaire', href: '/apprenants/operations/gestion-disciplinaire' },
      { label: 'Sorties d\'\u00c9lèves', href: '/apprenants/operations/gestion-disciplinaire' },
      {
        label: isEditing ? 'Éditer Sortie' : 'Nouvelle Sortie',
        href: '/apprenants/operations/gestion-disciplinaire/sortie',
      },
    ]"
  >
    <form class="w-full flex flex-col space-y-8" @submit.prevent="handleSubmit">
      <FormSection title="" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10">
        <InputWrapper>
          <Label for="school_year_id">Année scolaire<SpanRequired /></Label>
          <Select v-model="formData.school_year_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez l'année" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="annee in schoolYears" :key="annee.id" :value="annee.id">
                {{ annee.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>
        <InputWrapper>
          <Label for="date">Date de sortie<SpanRequired /></Label>
          <CustomDatePicker id="date" v-model="formData.date" required class="w-full" />
        </InputWrapper>
        <InputWrapper>
          <Label for="exit_time">Heure de sortie<SpanRequired /></Label>
          <Input id="exit_time" v-model="formData.exit_time" type="time" required class="w-full" />
        </InputWrapper>
        <InputWrapper>
          <Label for="student_id">Élève<SpanRequired /></Label>
          <Select v-model="formData.student_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez un élève" />
            </SelectTrigger>
            <SelectContent>
              <div class="p-2">
                <Input
                  :value="studentSearch"
                  placeholder="Rechercher un apprenant..."
                  class="w-full h-8"
                  @input="(e: any) => filterStudents(e.target.value)"
                  @keydown.stop
                />
              </div>

              <SelectItem v-for="student in filteredStudents" :key="student.id" :value="student.id">
                {{ student.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>
        <InputWrapper>
          <Label for="semestre">Semestre <SpanRequired /> </Label>
          <Select v-model="formData.semester" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez le semestre" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="semestre in semestres" :key="semestre" :value="semestre">
                {{ semestre }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>
        <InputWrapper>
          <Label for="motif">Motif de la sortie<SpanRequired /></Label>
          <Textarea
            id="motif"
            v-model="formData.motif"
            placeholder="Décrivez le motif de la sortie..."
            required
            class="w-full min-h-[100px]"
          />
        </InputWrapper>
      </FormSection>
      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>
        <Button type="submit" :disabled="creating || updating">
          {{
            isEditing
              ? updating
                ? 'Mise à jour...'
                : 'Mettre à jour'
              : creating
                ? 'Enregistrement...'
                : 'Enregistrer la sortie'
          }}
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
