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
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import Spinner from '@/components/ui/spinner/Spinner.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import api from '@/services/api.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { eventBus } from '@/utils/eventBus.ts'

const router = useRouter()
const route = useRoute()
const noteId = ref(route.params.id as string)
const isLoadingNote = ref(false)

// v-models stockent les IDs (string) tandis que l'UI affiche les noms
const formData = ref({
  anneeScolaire: '',
  filiere: '',
  classe: '',
  apprenant: '',
  numeroFauteCommise: '',
  conduiteSemestre1: '',
  conduiteSemestre2: '',
})

// Data API
const { data: schoolYears, fetchData: fetchSchoolYears } = useGetApi<any>(
  API_ROUTES.GET_SHOOL_YEARS,
)
const { data: filieresApi, fetchData: fetchFilieres } = useGetApi<any[]>(API_ROUTES.GET_FILLIERES)
const { data: classroomsApi, fetchData: fetchClassrooms } = useGetApi<any[]>(
  API_ROUTES.GET_CLASSROOMS,
)
const { data: studentsApi, fetchData: fetchStudents } = useGetApi<any>(API_ROUTES.GET_STUDENTS)
const { data: conduiteApi, fetchData: fetchConduite } = useGetApi<any>(API_ROUTES.GET_CONDUITE)

// Charger les données de la note à éditer
const loadNoteData = async () => {
  isLoadingNote.value = true
  try {
    console.log('[EditNote] Loading note with ID:', noteId.value)
    const { data } = await api.get(API_ROUTES.GET_NOTE_CONDUITE_BY_ID(Number(noteId.value)))
    console.log('[EditNote] API Response:', data)

    // Support multiple response structures
    const noteData = data.note || data.data || data.conduite_grade || data
    console.log('[EditNote] Extracted note data:', noteData)

    if (noteData) {
      // Attendre que les données de référence soient chargées
      await new Promise((resolve) => setTimeout(resolve, 100))

      formData.value = {
        anneeScolaire: String(noteData.school_year_id || ''),
        filiere: String(noteData.filiere_id || ''),
        classe: String(noteData.classroom_id || ''),
        apprenant: String(noteData.student_id || ''),
        numeroFauteCommise: String(noteData.fault_count || ''),
        conduiteSemestre1: String(noteData.conduite_semester_1_id || ''),
        conduiteSemestre2: String(noteData.conduite_semester_2_id || ''),
      }

      console.log('[EditNote] Form data set:', formData.value)
      console.log('[EditNote] Available years:', anneesScolaires.value)
      console.log('[EditNote] Available filieres:', filieres.value)
      console.log('[EditNote] Available classes:', classes.value)
      console.log('[EditNote] Available students:', students.value)
    } else {
      console.error('[EditNote] No note data found in response')
      showCustomToast({ message: 'Aucune donnée trouvée pour cette note', type: 'error' })
    }
  } catch (err) {
    console.error('[EditNote] Error loading note:', err)
    showCustomToast({ message: 'Erreur lors du chargement de la note', type: 'error' })
  } finally {
    isLoadingNote.value = false
  }
}

// Charger les données au montage
onMounted(async () => {
  console.log('[EditNote] Component mounted, noteId:', noteId.value)

  // Charger d'abord les données de référence
  await Promise.all([
    fetchSchoolYears(),
    fetchFilieres(),
    fetchClassrooms(),
    fetchStudents(),
    fetchConduite(),
  ])

  console.log('[EditNote] Reference data loaded')

  // Puis charger les données de la note
  await loadNoteData()
})

// Détecte l'année scolaire active dans la réponse brute de l'API
function getActiveSchoolYearId(): string | number | null {
  const raw: any = schoolYears?.value
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

// Normalisation des réponses
const anneesScolaires = computed(() => {
  const v: any = schoolYears?.value
  const list = Array.isArray(v) ? v : (v?.years ?? [])
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
    filiere_id: c.filiere_id ?? c.filiereId ?? null,
  }))
})

const students = computed(() => {
  const v: any = studentsApi?.value
  const list = Array.isArray(v) ? v : (v?.students?.data ?? v?.data ?? [])
  return list.map((s: any) => ({
    id: String(s.id),
    name: `${s.name ?? ''} ${s.firstname ?? ''}`.trim(),
    classroom_id: s.classroom_id ?? s.classroomId ?? null,
    filiere_id: s.filiere_id ?? s.filiereId ?? null,
  }))
})

// Search for students
const studentSearch = ref('')
const filteredStudentsList = computed(() => {
  const base = students.value || []
  if (!studentSearch.value) return base
  const q = studentSearch.value.toLowerCase()
  return base.filter((s: any) => (s.name || '').toLowerCase().includes(q))
})

const conduites = computed(() => {
  const v: any = conduiteApi?.value
  const list = Array.isArray(v) ? v : (v?.conduites?.data ?? v?.data ?? [])
  return list.map((n: any) => ({
    id: String(n.id ?? n.value ?? ''),
    name: n.name ?? n.label ?? String(n),
  }))
})

// Cascading filters
const filteredClasses = computed(() => {
  const fid = formData.value.filiere
  if (!fid) return classes.value
  return classes.value.filter((c: any) => !c.filiere_id || String(c.filiere_id) === String(fid))
})

const filteredStudents = computed(() => {
  const cid = formData.value.classe
  const fid = formData.value.filiere
  let base = students.value
  if (cid) {
    const byClass = base.filter(
      (s: any) => s.classroom_id && String(s.classroom_id) === String(cid),
    )
    if (byClass.length) return byClass
  }
  if (fid) {
    const byFiliere = base.filter((s: any) => s.filiere_id && String(s.filiere_id) === String(fid))
    if (byFiliere.length) return byFiliere
  }
  return base
})

// Apply search filter on top of cascading filters
const visibleStudents = computed(() => {
  const base = filteredStudents.value || []
  if (!studentSearch.value) return base
  const q = studentSearch.value.toLowerCase()
  return base.filter((s: any) => (s.name || '').toLowerCase().includes(q))
})

// PUT update note conduite
const { putData, loading: updating, error: updateError } = usePutApi<any>()

const handleSubmit = async () => {
  // Validation minimale
  if (!formData.value.anneeScolaire || !formData.value.classe || !formData.value.apprenant) {
    showCustomToast({ message: 'Veuillez remplir tous les champs obligatoires', type: 'error' })
    return
  }

  // Toujours envoyer fault_count = 0
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

  await putData(API_ROUTES.UPDATE_NOTE_CONDUITE(Number(noteId.value)), payload)

  if (updateError.value) {
    showCustomToast({
      message: updateError.value || 'Erreur lors de la mise à jour de la note',
      type: 'error',
    })
  } else {
    showCustomToast({ message: 'Note de conduite mise à jour avec succès', type: 'success' })
    eventBus.emit('noteConduiteUpdated')
    router.push('/apprenants/operations/gestion-disciplinaire/note-conduite')
  }
}

const handleCancel = () => {
  router.push('/apprenants/operations/gestion-disciplinaire/note-conduite')
}
</script>

<template>
  <DashFormLayout
    :link-back="'/apprenants/operations/gestion-disciplinaire/note-conduite'"
    title="Modifier la Note de Conduite"
    group-route="/apprenants/operations"
    module="students"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Gestion Disciplinaire', href: '/apprenants/operations/gestion-disciplinaire' },
      {
        label: 'Note de Conduite',
        href: '/apprenants/operations/gestion-disciplinaire/note-conduite',
      },
      { label: 'Modifier Note', href: '#' },
    ]"
  >
    <!-- État de chargement des données de la note -->
    <div v-if="isLoadingNote" class="w-full flex flex-col items-center justify-center py-20">
      <div class="flex flex-col items-center space-y-4">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="text-gray-600 text-lg">Chargement des informations de la note...</p>
      </div>
    </div>

    <!-- Formulaire principal -->
    <form v-else class="w-full flex flex-col space-y-8" @submit.prevent="handleSubmit">
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
          <Select v-model="formData.classe" required>
            <SelectTrigger class="!h-10 bg-white w-full">
              <SelectValue placeholder="Sélectionnez une classe" />
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
          <Select v-model="formData.apprenant" required>
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

              <SelectItem v-for="s in visibleStudents" :key="s.id" :value="s.id">
                {{ s.name }}
              </SelectItem>

              <div v-if="visibleStudents.length === 0" class="p-2 text-gray-500 text-sm">
                Aucun apprenant trouvé
              </div>
            </SelectContent>
          </Select>
        </InputWrapper>

        <!-- Nombre de fautes commises : caché, toujours 0 par défaut -->

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

      <div class="w-full flex h-px bg-gray-300"></div>

      <div class="flex items-center justify-end gap-2">
        <Button type="button" variant="outline" @click="handleCancel">
          <span class="flex iconify hugeicons--cancel-01 mr-1.5"></span>
          Annuler
        </Button>
        <Button type="submit" :disabled="updating">
          <span v-if="!updating" class="flex items-center gap-2">
            <span class="iconify hugeicons--floppy-disk mr-1"></span>
            <span>Mettre à jour</span>
          </span>
          <span v-else>
            <div class="flex items-center gap-2">
              <Spinner />
              <span>Mise à jour...</span>
            </div>
          </span>
        </Button>
      </div>
    </form>
  </DashFormLayout>
</template>
