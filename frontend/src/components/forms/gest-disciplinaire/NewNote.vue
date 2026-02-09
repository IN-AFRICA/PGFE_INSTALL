<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { eventBus } from '@/utils/eventBus.ts'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const router = useRouter()

const formData = ref({
  anneeScolaire: '',
  filiere: '',
  classe: '',
  apprenant: '',
  numeroFauteCommise: '',
  conduiteSemestre1: '',
  conduiteSemestre2: '',
})

const { data: schoolYears, fetchData: fetchSchoolYears } = useGetApi<any>(
  API_ROUTES.GET_SCHOOL_YEARS,
)
const { data: filieresApi, fetchData: fetchFilieres } = useGetApi<any[]>(API_ROUTES.GET_FILLIERES)
const { data: classroomsApi, fetchData: fetchClassrooms } = useGetApi<any[]>(
  API_ROUTES.GET_CLASSROOMS,
)
const { data: studentsApi, fetchData: fetchStudents } = useGetApi<any>(
  API_ROUTES.GET_STUDENT_REGISTRATIONS,
)
const { data: conduiteApi, fetchData: fetchConduite } = useGetApi<any>(API_ROUTES.GET_CONDUITE)

const studentSearch = ref('')

onMounted(async () => {
  await fetchSchoolYears()

  const activeId = getActiveSchoolYearId()
  if (activeId) formData.value.anneeScolaire = String(activeId)

  fetchFilieres()
  fetchClassrooms({ per_page: 1000 })
  fetchStudents({ per_page: 1000 })
  fetchConduite()
})

function getActiveSchoolYearId(): string | number | null {
  const raw: any = schoolYears?.value
  let list: any[] = []
  if (Array.isArray(raw)) list = raw
  else if (raw && Array.isArray(raw.years)) list = raw.years
  else if (raw && Array.isArray(raw.data)) list = raw.data

  if (!Array.isArray(list) || list.length === 0) return null

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

const anneesScolaires = computed(() => {
  const v: any = schoolYears?.value
  const list = Array.isArray(v) ? v : (v?.years ?? v?.data ?? [])
  return list.map((y: any) => ({ id: String(y.id), name: y.name }))
})

const filieres = computed(() => {
  const v: any = filieresApi?.value
  const list = Array.isArray(v) ? v : (v?.filiaires?.data ?? v?.data ?? [])
  return list.map((f: any) => ({ id: String(f.id), name: f.name }))
})

const classes = computed(() => {
  const v: any = classroomsApi?.value
  const list = Array.isArray(v) ? v : (v?.classrooms?.data ?? v?.data ?? [])
  return list.map((c: any) => ({
    id: String(c.id),
    name: c.name,
    filiere_id: c.filiere_id ?? c.filiaire_id ?? c.academic_level?.cycle?.filiaire_id ?? null,
  }))
})

const students = computed(() => {
  const v: any = studentsApi?.value
  const list = Array.isArray(v) ? v : (v?.students?.data ?? v?.data ?? [])
  return list.map((s: any) => ({
    id: String(s.student_id || s.id),
    name: s.student_name || `${s.name ?? ''} ${s.firstname ?? ''}`.trim(),
    classroom_id: s.classroom_id ?? s.classroomId ?? null,
    filiere_id: s.filiere_id ?? s.filiereId ?? null,
  }))
})

const conduites = computed(() => {
  const v: any = conduiteApi?.value
  const list = Array.isArray(v) ? v : (v?.conduites?.data ?? v?.data ?? [])
  return list.map((n: any) => ({
    id: String(n.id ?? n.value ?? ''),
    name: n.name ?? n.label ?? String(n),
  }))
})

const filteredClasses = computed(() => {
  if (!formData.value.filiere) return classes.value
  return classes.value.filter((c: any) => String(c.filiere_id) === String(formData.value.filiere))
})

const filteredStudentsList = computed(() => {
  let list = students.value

  if (formData.value.classe) {
    list = list.filter((s: any) => String(s.classroom_id) === String(formData.value.classe))
  }

  if (studentSearch.value) {
    const q = studentSearch.value.toLowerCase()
    list = list.filter((s: any) => (s.name || '').toLowerCase().includes(q))
  }

  return list
})

watch(
  () => formData.value.filiere,
  () => {
    formData.value.classe = ''
    formData.value.apprenant = ''
  },
)
watch(
  () => formData.value.classe,
  () => {
    formData.value.apprenant = ''
  },
)

const { postData, loading: creating, error: createError } = usePostApi<any>()

const resetForm = () => {
  formData.value = {
    anneeScolaire: '',
    filiere: '',
    classe: '',
    apprenant: '',
    numeroFauteCommise: '',
    conduiteSemestre1: '',
    conduiteSemestre2: '',
  }
  const activeId = getActiveSchoolYearId()
  if (activeId) formData.value.anneeScolaire = String(activeId)
}

const handleSubmit = async () => {
  if (!formData.value.anneeScolaire || !formData.value.classe || !formData.value.apprenant) {
    showCustomToast({ message: 'Veuillez remplir tous les champs obligatoires', type: 'error' })
    return
  }

  const payload = {
    school_year_id: Number(formData.value.anneeScolaire),
    filiere_id: formData.value.filiere ? Number(formData.value.filiere) : null,
    classroom_id: Number(formData.value.classe),
    student_id: Number(formData.value.apprenant),
    fault_count: 0,
    conduite_semester_1_id: formData.value.conduiteSemestre1
      ? Number(formData.value.conduiteSemestre1)
      : null,
    conduite_semester_2_id: formData.value.conduiteSemestre2
      ? Number(formData.value.conduiteSemestre2)
      : null,
  }

  await postData(API_ROUTES.CREATE_NOTE_CONDUITE, payload)

  await new Promise((resolve) => setTimeout(resolve, 100))

  if (createError.value) {
    showCustomToast({
      message: createError.value || 'Erreur lors de la création de la note',
      type: 'error',
    })
  } else {
    showCustomToast({ message: 'Note de conduite créée avec succès', type: 'success' })
    ;(eventBus as any).emit('noteConduiteUpdated')
    resetForm()
  }
}

const handleCancel = () => {
  router.push('/apprenants/operations/gestion-disciplinaire/note-conduite')
}
</script>

<template>
  <DashFormLayout
    :link-back="'/apprenants/operations/gestion-disciplinaire/note-conduite'"
    title="Nouvelle Note de Conduite"
    group-route="/apprenants/operations"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Gestion Disciplinaire', href: '/apprenants/operations/gestion-disciplinaire' },
      {
        label: 'Notes de Conduite',
        href: '/apprenants/operations/gestion-disciplinaire/note-conduite',
      },
      { label: 'Nouvelle Note', href: '/apprenants/operations/gestion-disciplinaire/note' },
    ]"
  >
    <form class="w-full flex flex-col space-y-8" @submit.prevent="handleSubmit">
      <FormSection title="" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-10">
        <InputWrapper>
          <Label for="anneeScolaire">Année scolaire<SpanRequired /></Label>
          <Select v-model="formData.anneeScolaire" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez l'année" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="annee in anneesScolaires" :key="annee.id" :value="annee.id">
                {{ annee.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="filiere">Section<SpanRequired /></Label>
          <Select v-model="formData.filiere" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez une filière" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="filiere in filieres" :key="filiere.id" :value="filiere.id">
                {{ filiere.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="classe">Classe<SpanRequired /></Label>
          <Select v-model="formData.classe" required :disabled="!formData.filiere">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  formData.filiere ? 'Sélectionnez une classe' : 'Sélectionnez d\'abord une section'
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

        <InputWrapper>
          <Label for="apprenant">Élève<SpanRequired /></Label>
          <Select v-model="formData.apprenant" required :disabled="!formData.classe">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue
                :placeholder="
                  formData.classe ? 'Sélectionnez un apprenant' : 'Sélectionnez d\'abord une classe'
                "
              />
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

              <SelectItem v-for="s in filteredStudentsList" :key="s.id" :value="s.id">
                {{ s.name }}
              </SelectItem>

              <div v-if="filteredStudentsList.length === 0" class="p-2 text-gray-500 text-sm">
                Aucun apprenant trouvé
              </div>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="conduiteSemestre1">Conduite-semestre 1</Label>
          <Select v-model="formData.conduiteSemestre1">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez la note" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="note in conduites" :key="note.id" :value="note.id">
                {{ note.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>

        <InputWrapper>
          <Label for="conduiteSemestre2">Conduite-semestre 2</Label>
          <Select v-model="formData.conduiteSemestre2">
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez la note" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="note in conduites" :key="note.id" :value="note.id">
                {{ note.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </InputWrapper>
      </FormSection>

      <div class="flex items-center justify-end gap-2">
        <div class="flex items-center gap-2">
          <Button type="button" variant="outline" @click="handleCancel"> Annuler </Button>
          <Button type="submit" :disabled="creating">
            {{ creating ? 'Enregistrement...' : 'Enregistrer la note' }}
          </Button>
        </div>
      </div>
    </form>
  </DashFormLayout>
</template>
