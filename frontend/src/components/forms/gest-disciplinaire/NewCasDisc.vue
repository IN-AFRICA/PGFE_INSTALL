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
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import type { StudentRegistered } from '@/models/student_registered'

const router = useRouter()
const route = useRoute()

// Check if we're in edit mode
const isEditing = ref(!!route.query.edit)
const caseId = ref(route.query.id ? Number(route.query.id) : null)

const formData = ref({
  date: '',
  student_id: 0,
  fault_count: 1,
  action: '',
  roi: '',
  classroom_id: 0,
  school_year_id: 0,
})

// API composables
const { postData, loading: loadingPost, error: errorPost, success: successPost } = usePostApi()
const { putData, loading: loadingPut, error: errorPut, success: successPut } = usePutApi()

// Get dropdown data

const { data: studentRegistrations, fetchData: fetchStudentRegistrations } = useGetApi<
  StudentRegistered[]
>(API_ROUTES.GET_STUDENT_REGISTRATIONS)
const { data: classrooms, fetchData: fetchClassrooms } = useGetApi<any[]>(API_ROUTES.GET_CLASSROOMS)

// Get additional dropdown data
const { data: schoolYearsRaw, fetchData: fetchSchoolYears } = useGetApi<any[]>(
  API_ROUTES.GET_SCHOOL_YEARS,
)
const { data: actionsRaw, fetchData: fetchActions } = useGetApi<any[]>(
  API_ROUTES.GET_DISCIPLINARY_ACTIONS,
)

onMounted(async () => {
  // Fetch dropdown data
  await Promise.all([
    fetchClassrooms({ per_page: 1000 }),
    fetchSchoolYears(),
    fetchActions(),
    fetchStudentRegistrations({ per_page: 1000 }),
  ])

  // Auto-select active school year if not editing
  if (!isEditing.value) {
    const activeId = getActiveSchoolYearId()
    if (activeId) formData.value.school_year_id = Number(activeId)
  }

  // If editing, fetch the case data
  if (isEditing.value && caseId.value) {
    await fetchCaseData()
  }
})

// Détecte l'année scolaire active dans la réponse brute de l'API
function getActiveSchoolYearId(): string | number | null {
  const raw: any = schoolYearsRaw?.value
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

const fetchCaseData = async () => {
  try {
    const url = API_ROUTES.GET_INDISCIPLINE_CASES + `/${caseId.value}`
    const { data, fetchData } = useGetApi<any>(url)
    await fetchData()
    const raw = data.value as any
    const item = raw && typeof raw === 'object' && 'data' in raw ? (raw as any).data : raw
    if (item) {
      formData.value = {
        date: item.date ?? '',
        student_id: Number(item.student_id ?? 0),
        fault_count: Number(item.fault_count ?? 1),
        action: String(item.action ?? ''),
        roi: String(item.roi ?? ''),
        classroom_id: Number(item.classroom_id ?? 0),
        school_year_id: Number(item.school_year_id ?? 0),
      }
    }
  } catch (error) {
    showCustomToast({ message: 'Erreur lors du chargement des données', type: 'error' })
  }
}

// Normalized lists for Selects
const studentsList = computed(() => {
  const v: any = (studentRegistrations as any)?.value
  const list = studentRegistrations.value || []
  return (list || [])
    .filter(
      (s: StudentRegistered) =>
        s &&
        s.id !== undefined &&
        s.id !== null &&
        s.classroom_id !== undefined &&
        Number(s.classroom_id) === formData.value.classroom_id,
    )
    .map((s: StudentRegistered) => ({
      id: Number(s.id),
      label: `${s.student.name ?? ''} ${s.student.firstname ?? ''}`.trim(),
    }))
})

// Simple inline search for students in NewCasDisc
const studentSearch = ref('')
const visibleStudentsList = computed(() => {
  const base = studentsList.value || []
  if (!studentSearch.value) return base
  const q = studentSearch.value.toLowerCase()
  return base.filter((s: any) => (s.label || '').toLowerCase().includes(q))
})

const classroomsList = computed(() => {
  const v: any = (classrooms as any)?.value
  const list = Array.isArray(v) ? v : (v?.classrooms?.data ?? v?.data ?? [])
  return (list || [])
    .filter((c: any) => c && c.id !== undefined && c.id !== null)
    .map((c: any) => ({ id: Number(c.id), label: c.name }))
})

const schoolYearsList = computed(() => {
  const v: any = (schoolYearsRaw as any)?.value
  const list = Array.isArray(v) ? v : (v?.years ?? [])
  return (list || [])
    .filter((y: any) => y && y.id !== undefined && y.id !== null)
    .map((y: any) => ({ id: Number(y.id), label: y.name ?? y.label }))
})

const actionsList = computed(() => {
  const v: any = (actionsRaw as any)?.value
  const list = Array.isArray(v) ? v : (v?.actions?.data ?? v?.data ?? [])
  return (list || [])
    .filter((a: any) => a && a.id !== undefined && a.id !== null)
    .map((a: any) => ({ id: Number(a.id), label: a.name ?? a.label ?? a.libelle ?? '' }))
})

const handleSubmit = async () => {
  try {
    // Toujours envoyer fault_count = 1
    formData.value.fault_count = 1

    if (isEditing.value && caseId.value) {
      // Update existing case
      const url = API_ROUTES.UPDATE_INDISCIPLINE_CASE.replace(
        ':indisciplineCase',
        String(caseId.value),
      )
      await putData(url, formData.value)

      if (successPut.value) {
        showCustomToast({
          message: "Cas d'indiscipline modifié avec succès",
          type: 'success',
        })
        eventBus.emit('indisciplineCaseUpdated')
        router.push('/apprenants/operations/gestion-disciplinaire/indiscipline')
      } else if (errorPut.value) {
        showCustomToast({ message: errorPut.value, type: 'error' })
      }
    } else {
      // Create new case
      await postData(API_ROUTES.CREATE_INDISCIPLINE_CASE, formData.value)

      if (successPost.value) {
        showCustomToast({
          message: "Cas d'indiscipline enregistré avec succès",
          type: 'success',
        })
        eventBus.emit('indisciplineCaseUpdated')
        router.push('/apprenants/operations/gestion-disciplinaire/indiscipline')
      } else if (errorPost.value) {
        showCustomToast({ message: errorPost.value, type: 'error' })
      }
    }
  } catch (error) {
    showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
  }
}

const handleCancel = () => {
  router.push('/apprenants/operations/gestion-disciplinaire/indiscipline')
}
</script>

<template>
  <DashFormLayout
    :link-back="'/apprenants/operations/gestion-disciplinaire/indiscipline'"
    title="Nouveau Cas d'Indiscipline"
    module="students"
    group-route="/apprenants/operations"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Gestion Disciplinaire', href: '/apprenants/operations/gestion-disciplinaire' },
      {
        label: 'Cas d\'Indiscipline',
        href: '/apprenants/operations/gestion-disciplinaire/indiscipline',
      },
      { label: 'Nouveau Cas', href: '/apprenants/operations/gestion-disciplinaire/cas' },
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
              <SelectItem
                v-for="schoolYear in schoolYearsList"
                :key="schoolYear.id"
                :value="schoolYear.id"
              >
                {{ schoolYear.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="classroom_id">Classe<SpanRequired /></Label>
          <Select v-model="formData.classroom_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez une classe" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="classroom in classroomsList"
                :key="classroom.id"
                :value="classroom.id"
              >
                {{ classroom.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="student_id">Élève<SpanRequired /></Label>
          <Select v-model="formData.student_id" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez un apprenant" />
            </SelectTrigger>
            <SelectContent>
              <div class="p-2">
                <Input
                  :value="studentSearch"
                  placeholder="Rechercher un apprenant..."
                  class="w-full h-8"
                  @input="(e: any) => (studentSearch = e.target.value)"
                  @keydown.stop
                />
              </div>

              <SelectItem
                v-for="student in visibleStudentsList"
                :key="student.id"
                :value="student.id"
              >
                {{ student.label }}
              </SelectItem>

              <div v-if="visibleStudentsList.length === 0" class="p-2 text-gray-500 text-sm">
                Aucun apprenant trouvé
              </div>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="date">Date<SpanRequired /></Label>
          <Input id="date" v-model="formData.date" type="date" required class="w-full" />
        </InputWrapper>

        <!-- ROI (Règlement d'Ordre Intérieur) -->
        <InputWrapper>
          <Label for="roi">ROI</Label>
          <Input
            id="roi"
            v-model="formData.roi"
            type="text"
            placeholder="Article du ROI"
            class="w-full"
          />
        </InputWrapper>

        <!-- Nombre de fautes : caché, toujours 1 par défaut -->

        <InputWrapper class="lg:col-span-3">
          <Label for="action">Action/Punition<SpanRequired /></Label>
          <Textarea
            id="action"
            v-model="formData.action"
            placeholder="Décrivez l'action disciplinaire ou la punition..."
            required
            class="w-full min-h-[100px]"
          />
        </InputWrapper>
      </FormSection>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>
        <Button type="submit" :disabled="loadingPost || loadingPut">
          <span
            v-if="loadingPost || loadingPut"
            class="iconify hugeicons--loading-03 animate-spin mr-2"
          ></span>
          {{ isEditing ? 'Modifier le cas' : 'Enregistrer le cas' }}
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
