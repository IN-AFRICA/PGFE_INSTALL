<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog'

import {
  SelectContent,
  Select,
  SelectTrigger,
  SelectValue,
  SelectItem,
  SelectGroup,
} from '@/components/ui/select'
import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import ExcelManagerWithAPI from '@/components/molecules/ExcelManagerWithAPI.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

// Interface pour les données de cotation
interface PeriodNotes {
  P1?: number | null
  P2?: number | null
  E1?: number | null
  P3?: number | null
  P4?: number | null
  E2?: number | null
}

// Initialiser la fiche de cotation pour la classe/cours sélectionnés du modal
const initializeFiche = async () => {
  if (!modalClasseId.value || !modalCoursId.value) {
    showCustomToast({
      message: 'Veuillez sélectionner la classe et le cours à initialiser',
      type: 'error',
    })
    return
  }

  // Garde: si des données existent déjà pour cette classe/cours, ne pas ré-initialiser
  // Cela couvre le cas où le bouton est cliqué avant que le chargement n'ait fini d'updater l'UI
  if (selectedClasse.value && selectedCours.value && (cotationData.value?.length || 0) > 0) {
    await loadCotationData()
    showCustomToast({ message: 'La fiche existe déjà. Données rechargées.', type: 'success' })
    showInitDialog.value = false
    return
  }

  const initUrl = `${API_ROUTES.INITIALIZE_FICHE_COTATION}?classroom_id=${modalClasseId.value}&course_id=${modalCoursId.value}`

  try {
    await postInitialize(initUrl, {})

    // Vérifier s'il y a une erreur
    if (initializeError.value) {
      showCustomToast({ message: initializeError.value, type: 'error' })
      return
    }

    // Vérifier si des fiches ont été créées
    const result = initializeResponse.value
    const createdCount = result?.created_count || 0

    // Fermer le modal
    showInitDialog.value = false

    // Activer le flag pour éviter que les watchers interfèrent
    isRestoringFilters.value = true

    // Mettre à jour les filtres de la page avec les valeurs du modal
    selectedClasse.value = modalClasseId.value
    selectedCours.value = modalCoursId.value

    // Si une colonne active est choisie dans le modal, la positionner
    if (modalActivePeriod.value) {
      activePeriod.value = modalActivePeriod.value
    }

    setTimeout(() => {
      isRestoringFilters.value = false
      // Sauvegarder l'état
      saveState()
    }, 100)

    // Afficher le message approprié
    if (createdCount > 0) {
      showCustomToast({
        message: `Fiche de cotation initialisée avec succès (${createdCount} élève(s))`,
        type: 'success',
      })
    } else {
      // Les fiches existent déjà, charger les données existantes
      showCustomToast({
        message: 'Les fiches de cotation existent déjà. Chargement en cours...',
        type: 'success',
      })
    }

    // Charger les données (nouvelles ou existantes)
    await loadCotationData()
  } catch (e) {
    console.error('❌ Erreur initialisation:', e)
    showCustomToast({ message: "Échec de l'initialisation de la fiche", type: 'error' })
  }
}

interface CotationItem {
  id: number
  school_year_id: string
  school_year?: string // Nom de l'année scolaire
  student_id: string
  student?: string // Nom complet de l'étudiant
  classroom_id: string
  classroom?: string // Nom de la classe
  course_id: number
  course?: string // Nom du cours
  note: number | PeriodNotes // Peut être un nombre ou un objet
  maxima: PeriodNotes // Les maxima sont au niveau racine
  created_at: string
}

// Interfaces pour les données de référence
interface SchoolYear {
  id: number
  name: string
  school_id: string
  is_active: string
  description: string | null
}

interface Student {
  id: number
  name: string
  firstname: string
  lastname: string | null
  matricule: string
  gender: string
  email: string
}

interface Classroom {
  id: number
  name: string
  filiere_id?: number
  filiaire_id?: number
}

interface Semester {
  id: number
  name: string
}

interface Course {
  id: number
  name: string
  label?: string // Champ label utilisé par l'API
  // Maxima définis lors de la création du cours
  max_period_1?: number
  max_period_2?: number
  max_period_3?: number
  max_period_4?: number
  max_exam_1?: number
  max_exam_2?: number
}

interface Filiere {
  id: number
  name: string
}

// API pour récupérer les données de cotation
const {
  data: apiCotationData,
  loading: loadingCotations,
  error: cotationError,
  fetchData: fetchCotationData,
} = useGetApi<CotationItem[]>(API_ROUTES.GET_FICHE_COTATIONS)

// Wrapper pour charger les données avec les paramètres requis
const loadCotationData = async () => {
  if (!selectedClasse.value || !selectedCours.value) return

  const params = {
    classroom_id: selectedClasse.value,
    course_id: selectedCours.value,
  }
  await fetchCotationData(params)
}

// APIs pour récupérer les données de référence
const {
  data: schoolYearsData,
  loading: loadingSchoolYears,
  fetchData: loadSchoolYears,
} = useGetApi<SchoolYear[]>(API_ROUTES.GET_SCHOOL_YEARS)
// Charger TOUS les étudiants sans pagination pour éviter les décalages d'IDs
const {
  data: studentsData,
  loading: loadingStudents,
  fetchData: loadStudents,
} = useGetApi<Student[]>(`${API_ROUTES.GET_STUDENTS}?per_page=1000`)
const {
  data: classroomsData,
  loading: loadingClassrooms,
  fetchData: loadClassrooms,
} = useGetApi<Classroom[]>(API_ROUTES.GET_CLASSROOMS)
const {
  data: semestersData,
  loading: loadingSemesters,
  fetchData: loadSemesters,
} = useGetApi<Semester[]>(API_ROUTES.GET_SEMESTERS)
const {
  data: coursesData,
  loading: loadingCourses,
  fetchData: loadCourses,
} = useGetApi<Course[]>(API_ROUTES.GET_COURSES)

const {
  data: modalCoursesData,
  loading: loadingModalCourses,
  fetchData: loadModalCourses,
} = useGetApi<Course[]>(API_ROUTES.GET_COURSES)

const {
  data: filieresData,
  loading: loadingFilieres,
  fetchData: loadFilieres,
} = useGetApi<any[]>(API_ROUTES.GET_FILLIERES)

// Maps pour un accès rapide aux données de référence par ID
const schoolYearsMap = computed(() => {
  const map = new Map<number, SchoolYear>()

  if (schoolYearsData.value && Array.isArray(schoolYearsData.value)) {
    // Cas où c'est un tableau direct
    schoolYearsData.value.forEach((item) => map.set(item.id, item))
  } else if (
    schoolYearsData.value &&
    (schoolYearsData.value as any).years &&
    Array.isArray((schoolYearsData.value as any).years)
  ) {
    // Cas spécifique pour school-years : les données sont dans 'years'
    ;(schoolYearsData.value as any).years.forEach((item: SchoolYear) => map.set(item.id, item))
  } else if (
    schoolYearsData.value &&
    (schoolYearsData.value as any).data &&
    Array.isArray((schoolYearsData.value as any).data)
  ) {
    // Cas générique avec 'data'
    ;(schoolYearsData.value as any).data.forEach((item: SchoolYear) => map.set(item.id, item))
  }

  return map
})

const studentsMap = computed(() => {
  const map = new Map<number, Student>()

  if (studentsData.value && Array.isArray(studentsData.value)) {
    // Cas où c'est un tableau direct
    studentsData.value.forEach((item) => map.set(item.id, item))
  } else if (studentsData.value && (studentsData.value as any).students) {
    // Cas spécifique pour students : structure {success: true, students: {...}}
    const studentsObj = (studentsData.value as any).students

    // Vérifier si c'est une structure paginée avec 'data'
    if (studentsObj.data && Array.isArray(studentsObj.data)) {
      studentsObj.data.forEach((item: Student) => map.set(item.id, item))
    } else if (Array.isArray(studentsObj)) {
      // Cas où students est directement un tableau
      studentsObj.forEach((item: Student) => map.set(item.id, item))
    } else {
      // Cas où students est un objet avec des propriétés numériques (IDs comme clés)
      Object.values(studentsObj).forEach((item: any) => {
        if (item && typeof item === 'object' && item.id) {
          map.set(item.id, item)
        }
      })
    }
  } else if (
    studentsData.value &&
    (studentsData.value as any).data &&
    Array.isArray((studentsData.value as any).data)
  ) {
    // Cas générique avec 'data'
    ;(studentsData.value as any).data.forEach((item: Student) => map.set(item.id, item))
  }

  return map
})

const classroomsMap = computed(() => {
  const map = new Map<number, Classroom>()
  if (classroomsData.value && Array.isArray(classroomsData.value)) {
    classroomsData.value.forEach((item) => map.set(item.id, item))
  } else if (
    classroomsData.value &&
    (classroomsData.value as any).data &&
    Array.isArray((classroomsData.value as any).data)
  ) {
    ;(classroomsData.value as any).data.forEach((item: Classroom) => map.set(item.id, item))
  }
  return map
})

const semestersMap = computed(() => {
  const map = new Map<number, Semester>()
  if (semestersData.value && Array.isArray(semestersData.value)) {
    semestersData.value.forEach((item) => map.set(item.id, item))
  } else if (
    semestersData.value &&
    (semestersData.value as any).data &&
    Array.isArray((semestersData.value as any).data)
  ) {
    ;(semestersData.value as any).data.forEach((item: Semester) => map.set(item.id, item))
  }
  return map
})

const coursesMap = computed(() => {
  const map = new Map<number, Course>()
  if (coursesData.value && Array.isArray(coursesData.value)) {
    coursesData.value.forEach((item) => map.set(item.id, item))
  } else if (
    coursesData.value &&
    (coursesData.value as any).data &&
    Array.isArray((coursesData.value as any).data)
  ) {
    ;(coursesData.value as any).data.forEach((item: Course) => map.set(item.id, item))
  }
  return map
})

// ====== Local state pour l'édition des 6 colonnes (sans semester) ======
const editBuffer = ref<Record<number, { note: PeriodNotes; maxima: PeriodNotes }>>({})

const ensureEdit = (studentId: number | null) => {
  const id = Number(studentId || 0)
  if (!editBuffer.value[id]) {
    // Initialiser les notes à 0 pour affichage immédiat et saisie facile
    // Les maxima restent vides et seront fournis par l'API via GET
    editBuffer.value[id] = {
      note: { P1: 0, P2: 0, E1: 0, P3: 0, P4: 0, E2: 0 },
      maxima: {},
    }
  }
  return editBuffer.value[id]
}

// Validation d'une note saisie avec animation de clignotement
const validateNote = (studentId: number, period: keyof PeriodNotes, value: number | null) => {
  const key = `${studentId}-${period}`
  const maxValue = ensureEdit(studentId).maxima[period]

  // Supprimer de la liste des invalides si la valeur est nulle ou vide
  if (value === null || value === undefined || value === '') {
    invalidInputs.value.delete(key)
    blinkingInputs.value.delete(key)
    return true
  }

  const numValue = Number(value)

  // Vérifier si négatif
  if (numValue < 0) {
    invalidInputs.value.add(key)
    // Déclencher l'animation de clignotement
    triggerBlink(key)
    // Limiter les toasts (max 1 par seconde)
    const now = Date.now()
    if (now - lastErrorShown.value > 1000) {
      showCustomToast({
        message: `La note ne peut pas être négative`,
        type: 'error',
      })
      lastErrorShown.value = now
    }
    return false
  }

  // Vérifier si dépasse le maximum
  if (maxValue !== null && maxValue !== undefined && numValue > maxValue) {
    invalidInputs.value.add(key)
    // Déclencher l'animation de clignotement
    triggerBlink(key)
    // Limiter les toasts (max 1 par seconde)
    const now = Date.now()
    if (now - lastErrorShown.value > 1000) {
      showCustomToast({
        message: `La note ne peut pas dépasser ${maxValue}/${maxValue}`,
        type: 'error',
      })
      lastErrorShown.value = now
    }
    return false
  }

  // Valeur valide
  invalidInputs.value.delete(key)
  blinkingInputs.value.delete(key)
  return true
}

// Déclencher le clignotement 3 fois à 500ms
const triggerBlink = (key: string) => {
  blinkingInputs.value.add(key)
  let blinkCount = 0
  const interval = setInterval(() => {
    blinkCount++
    if (blinkCount >= 6) {
      // 3 clignotements = 6 changements d'état
      clearInterval(interval)
      // Garder l'input en rouge après les clignotements
    }
  }, 500)
}

// Vérifier si un input est invalide
const isInputInvalid = (studentId: number, period: keyof PeriodNotes): boolean => {
  return invalidInputs.value.has(`${studentId}-${period}`)
}

// Vérifier si un input est en train de clignoter
const isInputBlinking = (studentId: number, period: keyof PeriodNotes): boolean => {
  return blinkingInputs.value.has(`${studentId}-${period}`)
}

// Fonctions pour récupérer les noms à partir des IDs
const getSchoolYearName = (id: number | null): string => {
  if (!id) return '-'
  const schoolYear = schoolYearsMap.value.get(id)
  return schoolYear ? schoolYear.name : `ID: ${id}`
}

const getStudentName = (id: number | null): string => {
  if (!id) return '-'
  const student = studentsMap.value.get(id)
  if (student) {
    // Construire le nom complet avec les bonnes propriétés
    const fullName = `${student.firstname || ''} ${student.lastname || ''}`.trim()
    return fullName || student.name || `ID: ${id}`
  }
  return `ID: ${id}`
}

// Fonction d'affichage pour les étudiants (utiliser directement les données de l'API)
const getStudentDisplay = (item: CotationItem): string => {
  // L'API retourne déjà le nom complet de l'étudiant
  return item.student || getStudentName(Number(item.student_id))
}

const getClassroomName = (id: number | null): string => {
  if (!id) return '-'
  const classroom = classroomsMap.value.get(id)
  return classroom ? classroom.name : `ID: ${id}`
}

const getSemesterName = (id: number | null): string => {
  if (!id) return '-'
  const semester = semestersMap.value.get(id)
  return semester ? semester.name : `ID: ${id}`
}

const getCourseName = (id: number | null): string => {
  if (!id) return '-'
  const course = coursesMap.value.get(id)
  return course ? course.label || course.name || `ID: ${id}` : `ID: ${id}`
}

// Données de cotation normalisées (backend gère déjà la déduplication)
const cotationData = computed(() => {
  const rawData = apiCotationData.value
  if (!Array.isArray(rawData) || rawData.length === 0) return []

  // Normaliser les notes
  rawData.forEach((item: any) => {
    // Parser si string JSON
    if (typeof item.note === 'string') {
      try {
        item.note = JSON.parse(item.note)
      } catch {
        item.note = {}
      }
    } else if (typeof item.note === 'number') {
      // ✅ Convertir note=0 en {} pour faciliter la saisie
      item.note = {}
    } else if (item.note === null || item.note === undefined) {
      item.note = {}
    }
  })

  return rawData
})

const filteredCotationData = computed(() => {
  let filtered = cotationData.value

  // Filtre par recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter((item: any) => {
      const studentName = (item.student || '').toLowerCase()
      return studentName.includes(query)
    })
  }

  // Filtre par année scolaire
  if (selectedAnnee.value) {
    filtered = filtered.filter(
      (item: any) => String(item.school_year_id) === String(selectedAnnee.value),
    )
  }

  // Filtre par filière
  if (selectedFiliere.value) {
    filtered = filtered.filter((item) => {
      const classroom = classroomsMap.value.get(Number(item.classroom_id))

      // Vérifier au niveau racine
      const rootId = classroom?.filiere_id || classroom?.filiaire_id
      if (rootId && String(rootId) === String(selectedFiliere.value)) return true

      // Vérifier niveau imbriqué (cycle)
      const nestedId = classroom?.academic_level?.cycle?.filiaire_id
      if (nestedId && String(nestedId) === String(selectedFiliere.value)) return true

      return false
    })
  }

  // Filtre par classe
  if (selectedClasse.value) {
    filtered = filtered.filter(
      (item: any) => String(item.classroom_id) === String(selectedClasse.value),
    )
  }

  // Filtre par cours
  if (selectedCours.value) {
    filtered = filtered.filter(
      (item: any) => String(item.course_id) === String(selectedCours.value),
    )
  }

  // ✅ Afficher TOUS les étudiants, même avec note={}
  // Le filtre des notes vides se fait seulement lors de l'enregistrement
  return filtered
})

// État de chargement global
const isLoadingReferenceData = computed(
  () =>
    loadingSchoolYears.value ||
    loadingStudents.value ||
    loadingClassrooms.value ||
    loadingSemesters.value ||
    loadingCourses.value,
)

// Fonctions pour gérer l'import/export avec API
const handleImportSuccess = async (result: any) => {
  const message = result.imported_count
    ? `✅ Import réussi ! ${result.imported_count} ligne(s) importée(s)`
    : '✅ Import de la fiche de cotation réussi !'

  showCustomToast({
    message,
    type: 'success',
  })

  await loadCotationData()
}

const handleImportError = (message: string) => {
  showCustomToast({
    message: `❌ Erreur lors de l'import : ${message}`,
    type: 'error',
  })
}

const handleExportSuccess = () => {
  // Afficher un toast de succès
  showCustomToast({
    message: '✅ Export de la fiche de cotation réussi ! Le fichier a été téléchargé.',
    type: 'success',
  })
}

const handleExportError = (message: string) => {
  // Afficher un toast d'erreur
  showCustomToast({
    message: `❌ Erreur lors de l'export de la fiche de cotation : ${message}`,
    type: 'error',
  })
}

const handleTemplateSuccess = () => {
  // Afficher un toast de succès
  showCustomToast({
    message: '✅ Template de fiche de cotation téléchargé avec succès !',
    type: 'success',
  })
}

const handleTemplateError = (message: string) => {
  // Afficher un toast d'erreur
  showCustomToast({
    message: `❌ Erreur lors du téléchargement du template de fiche de cotation : ${message}`,
    type: 'error',
  })
}

// Fonction appelée avant l'export pour synchroniser les filtres
const handleBeforeExport = () => {
  updateExportFilters()
}

// Filtres pour l'export (basés sur les filtres de la page)
const exportFilters = ref({
  annee_scolaire: null as string | null,
  filiere: null as string | null,
  classe: null as string | null,
  cours: null as string | null,
  periode_examen: null as string | null,
  search: null as string | null,
})

// États pour les filtres de la page
const searchQuery = ref('')
const selectedAnnee = ref('')
const selectedFiliere = ref('')
const selectedClasse = ref('')
const selectedCours = ref('')
const selectedDate = ref('')

// Clé unique pour localStorage
const STORAGE_KEY = 'ficheCotation_lastState'

// Flag pour désactiver temporairement les watchers de reset
const isRestoringFilters = ref(false)

// Sauvegarder l'état complet (seulement après init ou save)
const saveState = () => {
  if (!selectedClasse.value || !selectedCours.value) return
  const state = {
    classroom_id: selectedClasse.value,
    course_id: selectedCours.value,
    timestamp: Date.now(),
  }
  localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
}

// Restaurer l'état complet
const restoreState = (): { classroom_id: string; course_id: string } | null => {
  const raw = localStorage.getItem(STORAGE_KEY)
  if (!raw) return null
  try {
    const state = JSON.parse(raw)
    return state
  } catch (e) {
    return null
  }
}

// Charger les données au montage du composant
onMounted(async () => {
  // Charger toutes les données de référence en parallèle
  await Promise.all([
    loadSchoolYears(),
    loadStudents(),
    loadClassrooms(),
    loadSemesters(),
    loadCourses(),
    loadFilieres(),
  ])

  // Restaurer l'état après le chargement des données de référence
  const state = restoreState()
  if (state) {
    isRestoringFilters.value = true
    selectedClasse.value = state.classroom_id
    selectedCours.value = state.course_id

    // Retrouver la filière associée à la classe restaurée
    const classrooms = Array.isArray(classroomsData.value)
      ? classroomsData.value
      : classroomsData.value?.data || []
    const restoredClass = classrooms.find((c: any) => String(c.id) === String(state.classroom_id))

    if (restoredClass) {
      const filiereId =
        restoredClass.filiere_id ||
        restoredClass.filiaire_id ||
        restoredClass.academic_level?.cycle?.filiaire_id
      if (filiereId) {
        selectedFiliere.value = String(filiereId)
      }
    }

    // Charger les données de cotation
    await loadCotationData()

    // Désactiver le flag après un délai pour laisser les watchers se stabiliser
    setTimeout(() => {
      isRestoringFilters.value = false
    }, 500)
  }
})

// UI state: modal de confirmation pour l'initialisation
const showInitDialog = ref(false)
const modalClasseId = ref<string | null>(null)
const modalCoursId = ref<string | null>(null)

// Colonne active: permet d'éditer UNE seule colonne à la fois (P1, P2, E1, P3, P4, E2)
// Valeur par défaut: P1
const activePeriod = ref<keyof PeriodNotes>('P1')
// Colonne active choisie dans le modal d'initialisation (pour pré-régler après init)
const modalActivePeriod = ref<keyof PeriodNotes>('P1')

// Helper: savoir si une cellule doit être désactivée selon la colonne active
const isDisabled = (period: keyof PeriodNotes) => {
  return activePeriod.value !== '' && activePeriod.value !== period
}

// Logs de debug pour suivre l'arrivée des données
// watch(() => apiCotationData.value, (val) => {
//   const len = Array.isArray(val) ? val.length : (val?.data?.length ?? 0)
// })
// watch(filteredCotationData, (val) => {
// })

// Utiliser directement la taille de cotationData (non filtrée) pour décider si la fiche existe
const hasData = computed(() => (cotationData.value?.length || 0) > 0)
const actionButtonLabel = computed(() => (hasData.value ? 'Enregistrer' : 'Initialiser la fiche'))
const isActionLoading = computed(() => savingCotation.value || initializingCotation.value)

const modalFilteredCourses = computed(() => {
  if (!modalCoursesData.value) {
    return []
  }
  const courses = Array.isArray(modalCoursesData.value)
    ? modalCoursesData.value
    : (modalCoursesData.value as any)?.data || []
  return courses
})

// Reset modal fields when opening
const openInitDialog = () => {
  modalClasseId.value = selectedClasse.value || null
  modalCoursId.value = selectedCours.value || null
  // Préremplir la colonne active du modal avec la colonne active actuelle
  modalActivePeriod.value = activePeriod.value
  showInitDialog.value = true
}

watch(modalClasseId, async (newVal, oldVal) => {
  if (oldVal !== undefined && newVal !== oldVal) {
    modalCoursId.value = null
  }
  if (newVal) {
    await loadModalCourses({ classroom_id: newVal })
  }
})

// Seed du buffer d'édition à partir des données chargées
watch(
  () => cotationData.value,
  (rows) => {
    if (!rows) return
    rows.forEach((item) => {
      const sid = Number(item.student_id || 0)
      if (sid <= 0) return
      const target = ensureEdit(sid)

      // Si note est un objet avec P1, P2, etc., l'utiliser
      if (item.note && typeof item.note === 'object') {
        target.note = { ...target.note, ...item.note }
      }

      // Copier les maxima (toujours présents)
      if (item.maxima && typeof item.maxima === 'object') {
        target.maxima = { ...target.maxima, ...item.maxima }
      }
    })
  },
  { immediate: true },
)

// Valider les notes en temps réel
watch(
  editBuffer,
  (buffer) => {
    Object.entries(buffer).forEach(([studentId, data]) => {
      const sid = Number(studentId)
      Object.entries(data.note).forEach(([period, value]) => {
        validateNote(sid, period as keyof PeriodNotes, value as number | null)
      })
    })
  },
  { deep: true },
)

// POST handler pour enregistrer la fiche (payload sans semester)
const {
  postData: postCotation,
  loading: savingCotation,
  error: errorCotation,
  errorDetails: errorDetailsCotation,
} = usePostApi<any>()
// POST handler pour initialiser la fiche de cotation
const {
  postData: postInitialize,
  loading: initializingCotation,
  response: initializeResponse,
  error: initializeError,
} = usePostApi<any>()

// Système de validation des notes
const invalidInputs = ref<Set<string>>(new Set())
const lastErrorShown = ref<number>(0)
const blinkingInputs = ref<Set<string>>(new Set())

const saveCotation = async () => {
  // Vérifier s'il y a des notes invalides
  if (invalidInputs.value.size > 0) {
    showCustomToast({
      message: `Impossible d'enregistrer : ${invalidInputs.value.size} note(s) invalide(s) (négative ou dépassant le maximum)`,
      type: 'error',
    })
    return
  }

  // Si les filtres sont vides mais qu'on a des données, récupérer depuis les données existantes
  let classroom_id = selectedClasse.value ? Number(selectedClasse.value) : null
  let course_id = selectedCours.value ? Number(selectedCours.value) : null

  // Si les filtres sont vides mais qu'on a des données, les récupérer depuis cotationData
  if ((!classroom_id || !course_id) && cotationData.value && cotationData.value.length > 0) {
    const firstItem = cotationData.value[0]
    if (!classroom_id && firstItem.classroom_id) {
      classroom_id = Number(firstItem.classroom_id)
      selectedClasse.value = String(firstItem.classroom_id)
    }
    if (!course_id && firstItem.course_id) {
      course_id = Number(firstItem.course_id)
      selectedCours.value = String(firstItem.course_id)
    }
  }

  // Vérification finale
  if (!classroom_id || !course_id) {
    showCustomToast({ message: 'Veuillez sélectionner la classe et le cours', type: 'error' })
    return
  }

  // Construire le tableau de notes avec la structure attendue par l'API
  // Filtrer pour n'envoyer que les étudiants avec au moins une note saisie
  const notesPayload = Object.entries(editBuffer.value)
    .filter(([sid]) => Number(sid) > 0)
    .map(([sid, payload]) => ({
      student_id: Number(sid),
      note: payload.note, // Objet avec P1, P2, E1, P3, P4, E2
    }))
    .filter((item) => {
      // Vérifier si au moins un champ de note est rempli
      const noteValues = Object.values(item.note)
      // ✅ Accepter 0 comme valeur valide, exclure seulement null/undefined/''
      const hasAtLeastOneNote = noteValues.some(
        (val) => val !== null && val !== undefined && val !== '',
      )
      return hasAtLeastOneNote
    })

  if (notesPayload.length === 0) {
    showCustomToast({ message: 'Aucune modification à enregistrer', type: 'error' })
    return
  }

  const body = {
    classroom_id,
    course_id,
    notes: notesPayload,
  }

  try {
    await postCotation(API_ROUTES.GET_FICHE_COTATIONS, body)

    // Vérifier s'il y a une erreur de validation
    if (errorCotation.value) {
      console.error('❌ Erreur validation backend:', errorCotation.value)
      console.error('❌ Détails erreur complète:', errorDetailsCotation.value)
      showCustomToast({ message: errorCotation.value, type: 'error' })
      return
    }

    // Sauvegarder l'état après enregistrement réussi
    saveState()

    showCustomToast({ message: 'Fiche de cotation enregistrée avec succès', type: 'success' })
    await loadCotationData()
  } catch (e) {
    console.error('❌ Erreur enregistrement:', e)
    showCustomToast({ message: "Erreur lors de l'enregistrement", type: 'error' })
  }
}

// Filtrage hiérarchique des classes par filière
const filteredClassrooms = computed(() => {
  if (!classroomsData.value) return []
  if (!selectedFiliere.value) return classroomsData.value

  const classrooms = Array.isArray(classroomsData.value)
    ? classroomsData.value
    : classroomsData.value?.data || []

  // Filtrer les classes
  const filtered = classrooms.filter((classroom: any) => {
    // Vérifier d'abord au niveau racine (compatibilité)
    const rootId = classroom.filiere_id || classroom.filiaire_id
    if (rootId && String(rootId) === String(selectedFiliere.value)) return true

    // Vérifier ensuite via le cycle (structure complète)
    const nestedId = classroom.academic_level?.cycle?.filiaire_id
    if (nestedId && String(nestedId) === String(selectedFiliere.value)) return true

    return false
  })

  // Trier les classes: Cycle Court en premier, puis par Nom
  return filtered.sort((a: any, b: any) => {
    // Récupérer le nom du cycle
    const cycleA = (a.academic_level?.cycle?.name || '').toLowerCase()
    const cycleB = (b.academic_level?.cycle?.name || '').toLowerCase()

    // Identifier si c'est un cycle court
    const isCourtA = cycleA.includes('court')
    const isCourtB = cycleB.includes('court')

    // Priorité au cycle court
    if (isCourtA && !isCourtB) return -1
    if (!isCourtA && isCourtB) return 1

    // Sinon tri alphabétique par nom de classe
    return a.name.localeCompare(b.name)
  })
})

// Filtrage hiérarchique des cours par classe
const filteredCourses = computed(() => {
  if (!coursesData.value) return []
  if (!selectedClasse.value) return []

  const courses = Array.isArray(coursesData.value)
    ? coursesData.value
    : coursesData.value?.data || []

  const filtered = courses.filter(
    (course: any) => String(course.classroom_id) === String(selectedClasse.value),
  )

  // Trier les cours par ordre alphabétique
  return filtered.sort((a: any, b: any) => {
    const nameA = (a.label || a.name || '').toLowerCase()
    const nameB = (b.label || b.name || '').toLowerCase()
    return nameA.localeCompare(nameB)
  })
})

// Reset classe et cours quand la filière change
watch(selectedFiliere, (newVal, oldVal) => {
  if (!isRestoringFilters.value && oldVal !== undefined && newVal !== oldVal) {
    selectedClasse.value = ''
    selectedCours.value = ''
  }
})

// Reset cours quand la classe change (désactivé pendant restauration)
watch(selectedClasse, (newVal, oldVal) => {
  if (!isRestoringFilters.value && oldVal !== undefined && newVal !== oldVal) {
    selectedCours.value = ''
  }
})

// Remplir automatiquement les filtres avec les données de la fiche chargée
watch(
  apiCotationData,
  (newData) => {
    if (newData && newData.length > 0) {
      const firstItem = newData[0]

      // Mettre à jour les filtres avec les données de la fiche
      if (firstItem.school_year_id && !selectedAnnee.value) {
        selectedAnnee.value = String(firstItem.school_year_id)
      }

      if (firstItem.classroom_id && !selectedClasse.value) {
        selectedClasse.value = String(firstItem.classroom_id)
      }

      if (firstItem.course_id && !selectedCours.value) {
        selectedCours.value = String(firstItem.course_id)
      }

      // Récupérer la filière depuis la classe
      if (firstItem.classroom_id && !selectedFiliere.value) {
        const classrooms = Array.isArray(classroomsData.value)
          ? classroomsData.value
          : classroomsData.value?.data || []

        const classroom = classrooms.find(
          (c: any) => String(c.id) === String(firstItem.classroom_id),
        )
        if (classroom && (classroom.filiere_id || classroom.filiaire_id)) {
          selectedFiliere.value = String(classroom.filiere_id || classroom.filiaire_id)
        }
      }
    }
  },
  { immediate: true },
)

// Computed pour mapper les filtres vers le format FilterBadges
const filterParams = computed(() => ({
  school_year_id: selectedAnnee.value || undefined,
  filiaire_id: selectedFiliere.value || undefined,
  classroom_id: selectedClasse.value || undefined,
  course_id: selectedCours.value || undefined,
}))

// Configuration des données de référence pour FilterBadges
const referenceData = computed(() => ({
  school_year_id: Array.isArray(schoolYearsData.value)
    ? schoolYearsData.value
    : schoolYearsData.value?.years || schoolYearsData.value?.data || [],
  filiaire_id: Array.isArray(filieresData.value)
    ? filieresData.value
    : filieresData.value?.data || [],
  classroom_id: Array.isArray(classroomsData.value)
    ? classroomsData.value
    : classroomsData.value?.data || [],
  course_id: Array.isArray(coursesData.value) ? coursesData.value : coursesData.value?.data || [],
}))

// Configuration des labels personnalisés pour FilterBadges
const customLabels = {
  school_year_id: (value: any, data: any[]) => {
    const year = data?.find((y: any) => String(y.id) === String(value))
    return year ? year.name : String(value)
  },
  filiaire_id: (value: any, data: any[]) => {
    const filiere = data?.find((f: any) => String(f.id) === String(value))
    return filiere ? filiere.name : String(value)
  },
  classroom_id: (value: any, data: any[]) => {
    const classroom = data?.find((c: any) => String(c.id) === String(value))
    return classroom ? classroom.name : String(value)
  },
  course_id: (value: any, data: any[]) => {
    const course = data?.find((c: any) => String(c.id) === String(value))
    return course ? course.label || course.name : String(value)
  },
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'school_year_id') selectedAnnee.value = ''
  if (key === 'filiaire_id') selectedFiliere.value = ''
  if (key === 'classroom_id') selectedClasse.value = ''
  if (key === 'course_id') selectedCours.value = ''
}
</script>

<template>
  <LayoutSaisieOperation active-tag-name="fiche-cotation" group="operations">
    <BoxPanelWrapper>
      <div class="flex items-center gap-3 justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              type="text"
              id="search"
              name="search"
              placeholder="Rechercher un cours..."
              v-model="searchQuery"
              class="w-full max-w-xs ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
            />
            <div
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
            >
              <span class="flex iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <Popover>
            <PopoverTrigger as-child>
              <Button variant="ghost" size="md" class="h-10 rounded-md border bg-white">
                Filtre
                <span class="iconify hugeicons--filter">Filtre</span>
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-80">
              <div class="grid gap-4">
                <div class="space-y-2">
                  <h4 class="font-medium leading-none">Filtrage</h4>
                </div>
                <div class="flex flex-col gap-3.5">
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="annee"
                      >Année scolaire</Label
                    >
                    <Select v-model="selectedAnnee">
                      <SelectTrigger id="annee" class="!h-10 bg-white w-full">
                        <SelectValue placeholder="Sélectionnez l'année scolaire" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="y in Array.isArray(schoolYearsData)
                              ? schoolYearsData
                              : schoolYearsData?.years || schoolYearsData?.data || []"
                            :key="y.id"
                            :value="String(y.id)"
                            >{{ y.name }}</SelectItem
                          >
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="filiere">Section</Label>
                    <Select v-model="selectedFiliere">
                      <SelectTrigger id="filiere" class="!h-10 bg-white w-full">
                        <SelectValue placeholder="Sélectionnez la filière" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="f in Array.isArray(filieresData)
                              ? filieresData
                              : filieresData?.data || []"
                            :key="f.id"
                            :value="String(f.id)"
                            >{{ f.name }}</SelectItem
                          >
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="classe">Classe</Label>
                    <Select v-model="selectedClasse" :disabled="!selectedFiliere">
                      <SelectTrigger
                        id="classe"
                        class="!h-10 bg-white w-full"
                        :disabled="!selectedFiliere"
                      >
                        <SelectValue placeholder="Sélectionnez d'abord une filière" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="c in filteredClassrooms"
                            :key="c.id"
                            :value="String(c.id)"
                            >{{ c.name }}</SelectItem
                          >
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="cours">Cours</Label>
                    <Select v-model="selectedCours" :disabled="!selectedClasse">
                      <SelectTrigger
                        id="cours"
                        class="!h-10 bg-white w-full"
                        :disabled="!selectedClasse"
                      >
                        <SelectValue placeholder="Sélectionnez d'abord une classe" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="c in filteredCourses"
                            :key="c.id"
                            :value="String(c.id)"
                            >{{ c.label || c.name }}</SelectItem
                          >
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="active_col"
                      >Colonne active (édition)</Label
                    >
                    <Select v-model="activePeriod">
                      <SelectTrigger id="active_col" class="!h-10 bg-white w-full">
                        <SelectValue
                          placeholder="Sélectionnez une colonne (P1, P2, E1, P3, P4, E2)"
                        />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem value="P1">P1</SelectItem>
                          <SelectItem value="P2">P2</SelectItem>
                          <SelectItem value="E1">E1</SelectItem>
                          <SelectItem value="P3">P3</SelectItem>
                          <SelectItem value="P4">P4</SelectItem>
                          <SelectItem value="E2">E2</SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </div>
            </PopoverContent>
          </Popover>
          <FilterBadges
            :filters="filterParams"
            :reference-data="referenceData"
            :custom-labels="customLabels"
            @remove-filter="removeFilter"
          />
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button
                variant="default"
                size="md"
                class="h-10 rounded-md"
                :disabled="isActionLoading"
              >
                <span v-if="isActionLoading" class="flex items-center gap-2">
                  <IconifySpinner size="sm" />
                  <span>Traitement...</span>
                </span>
                <span v-else class="flex items-center gap-2">
                  <span class="iconify hugeicons--arrow-down-01"></span>
                  <span>{{ actionButtonLabel }}</span>
                </span>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <template v-if="hasData">
                <DropdownMenuItem
                  @click="saveCotation"
                  :disabled="isActionLoading"
                  class="flex items-center cursor-pointer"
                >
                  <span class="flex mr-1.5 iconify hugeicons--floppy-disk"></span>
                  Enregistrer
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                  @click="openInitDialog"
                  :disabled="isActionLoading"
                  class="flex items-center cursor-pointer"
                >
                  <span class="flex mr-1.5 iconify hugeicons--exchange-01"></span>
                  Initialiser la fiche
                </DropdownMenuItem>
              </template>
              <template v-else>
                <DropdownMenuItem
                  @click="openInitDialog"
                  :disabled="isActionLoading"
                  class="flex items-center cursor-pointer"
                >
                  <span class="flex mr-1.5 iconify hugeicons--exchange-01"></span>
                  Initialiser la fiche
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                  @click="saveCotation"
                  :disabled="isActionLoading"
                  class="flex items-center cursor-pointer"
                >
                  <span class="flex mr-1.5 iconify hugeicons--floppy-disk"></span>
                  Enregistrer
                </DropdownMenuItem>
              </template>
            </DropdownMenuContent>
          </DropdownMenu>

          <ExcelManagerWithAPI
            :import-url="API_ROUTES.IMPORT_FICHE_COTATION"
            :export-url="API_ROUTES.EXPORT_FICHE_COTATION"
            button-text="Excel"
            export-filename="fiche-cotation"
            :export-filters="exportFilters"
            :show-export-excel="false"
            :show-export-pdf="false"
            @before-export="handleBeforeExport"
            @import-success="handleImportSuccess"
            @import-error="handleImportError"
            @export-success="handleExportSuccess"
            @export-error="handleExportError"
            @template-success="handleTemplateSuccess"
            @template-error="handleTemplateError"
          />
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead> Élève </TableHead>
              <TableHead class="text-center w-25">P1</TableHead>
              <TableHead class="text-center w-25">P2</TableHead>
              <TableHead class="text-center w-25">E1</TableHead>
              <TableHead class="text-center w-25">P3</TableHead>
              <TableHead class="text-center w-25">P4</TableHead>
              <TableHead class="text-center w-25">E2</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <!-- Affichage d'un loader pendant le chargement -->
            <TableRow v-if="loadingCotations || isLoadingReferenceData">
              <TableCell colspan="9" class="text-center py-8">
                <div class="flex items-center justify-center space-x-2">
                  <IconifySpinner size="lg" />
                  <span>Chargement des données...</span>
                </div>
              </TableCell>
            </TableRow>

            <!-- Affichage des données ou message vide -->
            <TableRow v-else-if="filteredCotationData.length === 0">
              <TableCell colspan="9" class="text-center py-8 text-gray-500">
                Aucune donnée de cotation disponible
              </TableCell>
            </TableRow>

            <!-- Affichage des données de fiche de cotation -->
            <TableRow v-for="item in filteredCotationData" :key="item.id">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{ getStudentDisplay(item) }}</TableCell>
              <TableCell>
                <Input
                  v-model.number="ensureEdit(item.student_id).note.P1"
                  :max="ensureEdit(item.student_id).maxima.P1 ?? undefined"
                  min="0"
                  step="0.1"
                  type="number"
                  :disabled="isDisabled('P1')"
                  :class="[
                    'w-20',
                    isInputInvalid(Number(item.student_id), 'P1') ? 'border-red-500 border-2' : '',
                    isInputBlinking(Number(item.student_id), 'P1') ? 'animate-pulse' : '',
                    isDisabled('P1')
                      ? 'opacity-50 bg-gray-100 text-gray-400 cursor-not-allowed'
                      : '',
                  ]"
                  @blur="
                    validateNote(Number(item.student_id), 'P1', ensureEdit(item.student_id).note.P1)
                  "
                />
              </TableCell>
              <TableCell>
                <Input
                  v-model.number="ensureEdit(item.student_id).note.P2"
                  :max="ensureEdit(item.student_id).maxima.P2 ?? undefined"
                  min="0"
                  step="0.1"
                  type="number"
                  :disabled="isDisabled('P2')"
                  :class="[
                    'w-20',
                    isInputInvalid(Number(item.student_id), 'P2') ? 'border-red-500 border-2' : '',
                    isInputBlinking(Number(item.student_id), 'P2') ? 'animate-pulse' : '',
                    isDisabled('P2')
                      ? 'opacity-50 bg-gray-100 text-gray-400 cursor-not-allowed'
                      : '',
                  ]"
                  @blur="
                    validateNote(Number(item.student_id), 'P2', ensureEdit(item.student_id).note.P2)
                  "
                />
              </TableCell>
              <TableCell>
                <Input
                  v-model.number="ensureEdit(item.student_id).note.E1"
                  :max="ensureEdit(item.student_id).maxima.E1 ?? undefined"
                  min="0"
                  step="0.1"
                  type="number"
                  :disabled="isDisabled('E1')"
                  :class="[
                    'w-20',
                    isInputInvalid(Number(item.student_id), 'E1') ? 'border-red-500 border-2' : '',
                    isInputBlinking(Number(item.student_id), 'E1') ? 'animate-pulse' : '',
                    isDisabled('E1')
                      ? 'opacity-50 bg-gray-100 text-gray-400 cursor-not-allowed'
                      : '',
                  ]"
                  @blur="
                    validateNote(Number(item.student_id), 'E1', ensureEdit(item.student_id).note.E1)
                  "
                />
              </TableCell>
              <TableCell>
                <Input
                  v-model.number="ensureEdit(item.student_id).note.P3"
                  :max="ensureEdit(item.student_id).maxima.P3 ?? undefined"
                  min="0"
                  step="0.1"
                  type="number"
                  :disabled="isDisabled('P3')"
                  :class="[
                    'w-20',
                    isInputInvalid(Number(item.student_id), 'P3') ? 'border-red-500 border-2' : '',
                    isInputBlinking(Number(item.student_id), 'P3') ? 'animate-pulse' : '',
                    isDisabled('P3')
                      ? 'opacity-50 bg-gray-100 text-gray-400 cursor-not-allowed'
                      : '',
                  ]"
                  @blur="
                    validateNote(Number(item.student_id), 'P3', ensureEdit(item.student_id).note.P3)
                  "
                />
              </TableCell>
              <TableCell>
                <Input
                  v-model.number="ensureEdit(item.student_id).note.P4"
                  :max="ensureEdit(item.student_id).maxima.P4 ?? undefined"
                  min="0"
                  step="0.1"
                  type="number"
                  :disabled="isDisabled('P4')"
                  :class="[
                    'w-20',
                    isInputInvalid(Number(item.student_id), 'P4') ? 'border-red-500 border-2' : '',
                    isInputBlinking(Number(item.student_id), 'P4') ? 'animate-pulse' : '',
                    isDisabled('P4')
                      ? 'opacity-50 bg-gray-100 text-gray-400 cursor-not-allowed'
                      : '',
                  ]"
                  @blur="
                    validateNote(Number(item.student_id), 'P4', ensureEdit(item.student_id).note.P4)
                  "
                />
              </TableCell>
              <TableCell>
                <Input
                  v-model.number="ensureEdit(item.student_id).note.E2"
                  :max="ensureEdit(item.student_id).maxima.E2 ?? undefined"
                  min="0"
                  step="0.1"
                  type="number"
                  :disabled="isDisabled('E2')"
                  :class="[
                    'w-20',
                    isInputInvalid(Number(item.student_id), 'E2') ? 'border-red-500 border-2' : '',
                    isInputBlinking(Number(item.student_id), 'E2') ? 'animate-pulse' : '',
                    isDisabled('E2')
                      ? 'opacity-50 bg-gray-100 text-gray-400 cursor-not-allowed'
                      : '',
                  ]"
                  @blur="
                    validateNote(Number(item.student_id), 'E2', ensureEdit(item.student_id).note.E2)
                  "
                />
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <!-- Modal de confirmation pour l'initialisation -->
      <AlertDialog :open="showInitDialog" @update:open="(v: any) => (showInitDialog = v)">
        <AlertDialogContent class="max-w-md">
          <AlertDialogHeader>
            <AlertDialogTitle>Initialiser la fiche de cotation</AlertDialogTitle>
            <AlertDialogDescription>
              Sélectionnez la classe et le cours pour initialiser la fiche de cotation.
            </AlertDialogDescription>
          </AlertDialogHeader>

          <div class="space-y-4 py-4">
            <!-- Sélection de la classe -->
            <div class="space-y-2">
              <Label>Classe *</Label>
              <Select v-model="modalClasseId">
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionnez une classe" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem
                      v-for="classroom in classroomsData"
                      :key="classroom.id"
                      :value="String(classroom.id)"
                    >
                      {{ classroom.name }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>

            <!-- Sélection du cours -->
            <div class="space-y-2">
              <Label>Cours *</Label>
              <Select v-model="modalCoursId" :disabled="!modalClasseId">
                <SelectTrigger>
                  <SelectValue
                    :placeholder="
                      modalClasseId ? 'Sélectionnez un cours' : 'Sélectionnez d\'abord une classe'
                    "
                  />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem
                      v-for="course in modalFilteredCourses"
                      :key="course.id"
                      :value="String(course.id)"
                    >
                      {{ course.label || course.name }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>
          </div>

          <AlertDialogFooter>
            <AlertDialogCancel @click="showInitDialog = false">Annuler</AlertDialogCancel>
            <Button
              @click="initializeFiche"
              :disabled="!modalClasseId || !modalCoursId || initializingCotation"
            >
              <span v-if="!initializingCotation" class="flex items-center gap-2">
                Initialiser
              </span>
              <span v-else class="flex items-center gap-2">
                <IconifySpinner size="sm" />
                <span>Initialisation...</span>
              </span>
            </Button>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
