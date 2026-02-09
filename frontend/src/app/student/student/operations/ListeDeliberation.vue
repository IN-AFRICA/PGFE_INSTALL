<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
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
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
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
import { useGetApi } from '@/composables/useGetApi'
import { usePutApi } from '@/composables/usePutApi'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { Classroom } from '@/models/classroom'
import { Deliberation } from '@/models/deliberation'
import { Course } from '@/models/course'
import { PeriodNote } from '@/models/periode_note'

// États des filtres
const searchQuery = ref('')
const selectedClasse = ref('')
const selectedCours = ref('')
const selectedAnnee = ref('')

// Flag pour désactiver temporairement les watchers
const isRestoringFilters = ref(false)

// API pour charger les délibérations
const {
  data: apiDeliberations,
  loading: loadingDeliberations,
  error: errorDeliberations,
  fetchData: loadDeliberations,
} = useGetApi<Deliberation[]>(API_ROUTES.GET_DELIBERATIONS)

// API pour mettre à jour une délibération
const {
  putData: updateDeliberation,
  loading: loadingUpdate,
  error: errorUpdate,
  success: successUpdate,
} = usePutApi()

// Charger les données de référence
const { data: classroomsData, fetchData: loadClassrooms } = useGetApi<Classroom[]>(
  API_ROUTES.GET_CLASSROOMS,
)

const { data: coursesData, fetchData: loadCourses } = useGetApi<Course[]>(API_ROUTES.GET_COURSES)

const { data: filterCoursesData, fetchData: loadFilterCourses } = useGetApi<any[]>(
  API_ROUTES.GET_COURSES,
)

const { data: modalCoursesData, fetchData: loadModalCourses } = useGetApi<any[]>(
  API_ROUTES.GET_COURSES,
)

const { data: schoolYearsData, fetchData: loadSchoolYears } = useGetApi<any[]>(
  API_ROUTES.GET_SCHOOL_YEARS,
)

// POST handler pour initialiser les délibérations
const {
  postData: postInitialize,
  loading: initializingDeliberation,
  response: initializeResponse,
  error: initializeError,
} = usePostApi<any>()

// Données avec normalisation
const deliberationsData = computed(() => {
  const rawData = apiDeliberations.value || []
  if (!Array.isArray(rawData)) return []

  // Normaliser les notes dans cotations
  rawData.forEach((delib: any) => {
    if (delib.cotations && Array.isArray(delib.cotations)) {
      delib.cotations.forEach((cot: any) => {
        // Si note est une string JSON, la parser
        if (typeof cot.note === 'string') {
          try {
            cot.note = JSON.parse(cot.note)
          } catch {
            cot.note = {}
          }
        }
        // Si note est 0 ou un nombre, convertir en objet vide
        if (typeof cot.note === 'number') {
          cot.note = {}
        }
      })
    }
  })

  return rawData
})

// Toutes les classes disponibles
const allClassrooms = computed(() => {
  if (!classroomsData.value) return []
  return Array.isArray(classroomsData.value)
    ? classroomsData.value
    : classroomsData.value?.data || []
})

// Toutes les années scolaires
const allSchoolYears = computed(() => {
  if (!schoolYearsData.value) return []

  // Gérer différents formats de réponse API
  if (Array.isArray(schoolYearsData.value)) {
    return schoolYearsData.value
  }

  // Cas spécifique pour school-years : les données sont dans 'years'
  if ((schoolYearsData.value as any).years && Array.isArray((schoolYearsData.value as any).years)) {
    return (schoolYearsData.value as any).years
  }

  // Cas générique avec 'data'
  if ((schoolYearsData.value as any).data && Array.isArray((schoolYearsData.value as any).data)) {
    return (schoolYearsData.value as any).data
  }

  return []
})

const filteredCourses = computed(() => {
  if (!filterCoursesData.value) return []
  return Array.isArray(filterCoursesData.value)
    ? filterCoursesData.value
    : filterCoursesData.value?.data || []
})

// Données filtrées - affiche seulement si classe ET cours sélectionnés
const filteredDeliberations = computed(() => {
  // Ne rien afficher si classe ou cours non sélectionnés
  if (!selectedClasse.value || !selectedCours.value) {
    return []
  }

  let filtered = deliberationsData.value

  // Filtre par classe (l'API retourne classroom.id pas classroom_id)
  filtered = filtered.filter(
    (item: any) => String(item.classroom?.id) === String(selectedClasse.value),
  )

  // Filtre par cours
  filtered = filtered.filter((item: any) => String(item.course?.id) === String(selectedCours.value))

  // Filtre par recherche (nom étudiant - l'API retourne student.name pas student_name)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter((item: any) => {
      const studentName = (item.student?.name || '').toLowerCase()
      return studentName.includes(query)
    })
  }

  return filtered
})

// Calculer le pourcentage annuel
// Le backend calcule maintenant le pourcentage, on utilise delib.pourcentage directement
const calculateAnnualPercentage = (delib: Deliberation): number => {
  return delib.pourcentage || 0
}

// Obtenir la note d'une période spécifique
const getNoteValue = (delib: Deliberation, period: keyof PeriodNote): number | null => {
  if (!delib.note) return null
  return delib.note[period] ?? null
}

// Toggle de validation/repêchage
const toggleValidation = async (delib: Deliberation) => {
  const newValue = delib.is_validated === '1' ? '0' : '1'

  // Le backend attend les paramètres en query params, pas dans le body
  const baseUrl = API_ROUTES.UPDATE_DELIBERATION.replace(':id', String(delib.id))
  const url = `${baseUrl}?student_id=${delib.student.id}&is_validated=${newValue}`

  await updateDeliberation(url, {})

  // Vérifier le succès de la requête via successUpdate.value
  if (successUpdate.value) {
    // Mettre à jour localement seulement si succès
    delib.is_validated = newValue

    showCustomToast({
      message: newValue === '1' ? 'Élève validé' : 'Validation retirée',
      type: 'success',
    })
  } else if (errorUpdate.value) {
    showCustomToast({
      message: errorUpdate.value || 'Erreur lors de la mise à jour',
      type: 'error',
    })
  }
}

// UI state: modal de confirmation pour l'initialisation
const showInitDialog = ref(false)
const modalClasseId = ref<string | null>(null)
const modalCoursId = ref<string | null>(null)
const modalAnneeId = ref<string | null>(null)

const modalFilteredCourses = computed(() => {
  if (!modalCoursesData.value) return []
  return Array.isArray(modalCoursesData.value)
    ? modalCoursesData.value
    : modalCoursesData.value?.data || []
})

// Initialiser les délibérations
const initializeDeliberation = async () => {
  if (!modalClasseId.value || !modalCoursId.value || !modalAnneeId.value) {
    showCustomToast({
      message: "Veuillez sélectionner la classe, le cours et l'année scolaire",
      type: 'error',
    })
    return
  }

  // Envoyer les paramètres dans le body de la requête POST
  const payload = {
    classroom_id: parseInt(modalClasseId.value),
    course_id: parseInt(modalCoursId.value),
    school_year_id: parseInt(modalAnneeId.value),
  }

  try {
    await postInitialize(API_ROUTES.INITIALIZE_DELIBERATION, payload)

    if (initializeError.value) {
      showCustomToast({ message: initializeError.value, type: 'error' })
      return
    }

    const result = initializeResponse.value
    const createdCount = result?.created_count || 0

    // Fermer le modal
    showInitDialog.value = false

    // Activer le flag pour éviter que les watchers interfèrent
    isRestoringFilters.value = true

    // Mettre à jour les filtres de la page avec les valeurs du modal
    selectedClasse.value = modalClasseId.value
    selectedCours.value = modalCoursId.value
    selectedAnnee.value = modalAnneeId.value

    // Sauvegarder l'état
    setTimeout(() => {
      isRestoringFilters.value = false
      saveState()
    }, 100)

    // Afficher le message approprié
    if (createdCount > 0) {
      showCustomToast({
        message: `Délibérations initialisées avec succès (${createdCount} élève(s))`,
        type: 'success',
      })
    } else {
      showCustomToast({
        message: 'Les délibérations existent déjà. Chargement en cours...',
        type: 'success',
      })
    }

    // Charger les délibérations
    await loadDeliberations()
  } catch (e) {
    console.error('❌ Erreur initialisation:', e)
    showCustomToast({ message: "Échec de l'initialisation des délibérations", type: 'error' })
  }
}

// Ouvrir le modal d'initialisation
const openInitDialog = () => {
  modalClasseId.value = selectedClasse.value || null
  modalCoursId.value = selectedCours.value || null
  modalAnneeId.value = selectedAnnee.value || null
  showInitDialog.value = true
}

// Persistance simple avec localStorage
const STORAGE_KEY = 'deliberation_lastState'

const saveState = () => {
  if (!selectedClasse.value || !selectedCours.value || !selectedAnnee.value) return
  const state = {
    classroom_id: selectedClasse.value,
    course_id: selectedCours.value,
    school_year_id: selectedAnnee.value,
    timestamp: Date.now(),
  }
  localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
}

const restoreState = (): {
  classroom_id: string
  course_id: string
  school_year_id: string
} | null => {
  const raw = localStorage.getItem(STORAGE_KEY)
  if (!raw) return null
  try {
    const state = JSON.parse(raw)
    return state
  } catch (e) {
    return null
  }
}

watch(selectedClasse, async (newVal, oldVal) => {
  if (!isRestoringFilters.value && oldVal !== undefined && newVal !== oldVal) {
    selectedCours.value = ''
  }
  if (newVal) {
    await loadFilterCourses({ classroom_id: newVal })
  }
})

watch(modalClasseId, async (newVal, oldVal) => {
  if (oldVal !== undefined && newVal !== oldVal) {
    modalCoursId.value = null
  }
  if (newVal) {
    await loadModalCourses({ classroom_id: newVal })
  }
})

// Charger les délibérations quand classe ET cours sont sélectionnés
watch([selectedClasse, selectedCours], ([classe, cours]) => {
  if (classe && cours) {
    loadDeliberations()
  }
})

// Charger les données au montage
onMounted(async () => {
  await Promise.all([loadClassrooms(), loadCourses(), loadSchoolYears()])

  // Restaurer l'état après le chargement des données de référence
  const state = restoreState()
  if (state) {
    isRestoringFilters.value = true
    selectedClasse.value = state.classroom_id
    selectedCours.value = state.course_id
    selectedAnnee.value = state.school_year_id

    // Charger les délibérations
    await loadDeliberations()

    // Désactiver le flag après un délai pour laisser les watchers se stabiliser
    setTimeout(() => {
      isRestoringFilters.value = false
    }, 500)
  } else {
    // Si pas d'état sauvegardé, sélectionner l'année active par défaut
    const years = allSchoolYears.value
    if (Array.isArray(years)) {
      const activeYear = years.find(
        (y: any) =>
          y.is_active === '1' || y.is_active === 1 || y.is_current === 1 || y.statut === 'En cours',
      )
      if (activeYear) {
        selectedAnnee.value = String(activeYear.id)
      }
    }
  }
})

// Computed pour mapper les filtres vers le format FilterBadges
const filterParams = computed(() => ({
  school_year_id: selectedAnnee.value || undefined,
  classroom_id: selectedClasse.value || undefined,
  course_id: selectedCours.value || undefined,
}))

// Configuration des données de référence pour FilterBadges
const referenceData = computed(() => ({
  school_year_id:
    schoolYearsData.value?.years || schoolYearsData.value?.data || schoolYearsData.value || [],
  classroom_id: Array.isArray(classroomsData.value)
    ? classroomsData.value
    : classroomsData.value?.data || [],
  course_id: Array.isArray(coursesData.value) ? coursesData.value : coursesData.value?.data || [],
}))

// Configuration des labels personnalisés pour FilterBadges
const customLabels = {
  school_year_id: (value: any, data: any[]) => {
    const year = data?.find((y: any) => String(y.id) === String(value))
    return year ? `Année: ${year.name}` : String(value)
  },
  classroom_id: (value: any, data: any[]) => {
    const classroom = data?.find((c: any) => String(c.id) === String(value))
    return classroom ? `Classe: ${classroom.name}` : String(value)
  },
  course_id: (value: any, data: any[]) => {
    const course = data?.find((c: any) => String(c.id) === String(value))
    return course ? `Cours: ${course.label || course.name}` : String(value)
  },
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'school_year_id') selectedAnnee.value = ''
  if (key === 'classroom_id') selectedClasse.value = ''
  if (key === 'course_id') selectedCours.value = ''
}

// Fonction pour naviguer vers la délibération générale
const router = useRouter()

const handleViewGeneralDelib = (delib: Deliberation) => {
  if (!selectedClasse.value || !selectedAnnee.value) {
    showCustomToast({
      message: 'Veuillez sélectionner une classe et une année scolaire',
      type: 'error',
    })
    return
  }

  // Résoudre noms lisibles
  const studentLabel = [
    delib?.student?.name,
    delib?.student?.lastname,
    delib?.student?.firstname,
    (delib as any)?.student_name,
  ]
    .filter(Boolean)
    .join(' ')
    .toString()
  const classroomLabel =
    typeof delib?.classroom === 'object'
      ? (delib?.classroom?.name || delib?.classroom?.label || '').toString()
      : (delib?.classroom || '').toString()
  const filiereLabel =
    typeof delib?.filiaire === 'object'
      ? (delib?.filiaire?.name || delib?.filiaire?.label || '').toString()
      : (delib?.filiaire || '').toString()

  const query = new URLSearchParams({
    classroom_id: String(selectedClasse.value),
    school_year_id: String(selectedAnnee.value),
    skip_missing: 'false',
    weight_by_hourly: 'false',
    student_name: studentLabel,
    classroom_name: classroomLabel,
    filiere_name: filiereLabel,
  })

  router.push(
    `/apprenants/operations/deliberation-generale/${delib.student.id}?${query.toString()}`,
  )
}
</script>

<template>
  <LayoutSaisieOperation
    group="operations"
    active-tag-name="deliberation"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Délibération', href: '/apprenants/operations/deliberation' },
    ]"
  >
    <BoxPanelWrapper>
      <div class="flex items-center gap-3 justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              type="text"
              id="search"
              name="search"
              placeholder="Rechercher un apprenant..."
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
                  <p class="text-sm text-muted-foreground">
                    Sélectionnez une classe et un cours pour afficher les délibérations
                  </p>
                </div>
                <div class="flex flex-col gap-3.5">
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="classe">Classe</Label>
                    <Select v-model="selectedClasse">
                      <SelectTrigger id="classe" class="!h-10 bg-white w-full">
                        <SelectValue placeholder="Sélectionnez la classe" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="c in allClassrooms"
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
          <Button @click="openInitDialog" size="md" :disabled="initializingDeliberation">
            <span v-if="!initializingDeliberation" class="flex items-center gap-2">
              Initialiser
            </span>
            <span v-else class="flex items-center gap-2">
              <IconifySpinner size="sm" />
              <span>Initialisation...</span>
            </span>
          </Button>
          <!-- <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="md" class="bg-white border border-border rounded-md">
                                Exporter
                                <span class="iconify hugeicons--arrow-down-01 " />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                            <DropdownMenuItem class="flex items-center">
                                <span class="flex mr-1.5 iconify hugeicons--pdf-02"></span>
                                Exporter pdf
                            </DropdownMenuItem>
                            <DropdownMenuItem class="flex items-center">
                                <span class="flex mr-1.5 iconify hugeicons--ai-sheets"></span>
                                Exporter Excel
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu> -->
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="min-w-70">Élève</TableHead>
              <TableHead class="text-center">P1</TableHead>
              <TableHead class="text-center">P2</TableHead>
              <TableHead class="text-center">E1</TableHead>
              <TableHead class="text-center bg-blue-50/50 font-semibold">SEM 1</TableHead>
              <TableHead class="text-center">P3</TableHead>
              <TableHead class="text-center">P4</TableHead>
              <TableHead class="text-center">E2</TableHead>
              <TableHead class="text-center bg-blue-50/50 font-semibold">SEM 2</TableHead>
              <TableHead class="text-center">Moyenne</TableHead>
              <TableHead class="text-center">% Annuelle</TableHead>
              <TableHead class="text-center">Validation</TableHead>
              <TableHead class="text-center">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loadingDeliberations">
              <TableCell colspan="13" class="text-center py-8 text-gray-500">
                Chargement des délibérations...
              </TableCell>
            </TableRow>
            <TableRow v-else-if="errorDeliberations">
              <TableCell colspan="13" class="text-center py-8 text-red-500">
                Erreur: {{ errorDeliberations }}
              </TableCell>
            </TableRow>
            <TableRow v-else-if="!selectedClasse || !selectedCours">
              <TableCell colspan="13" class="text-center py-8 text-gray-500">
                Veuillez sélectionner une classe et un cours pour afficher les délibérations
              </TableCell>
            </TableRow>
            <TableRow v-else-if="!filteredDeliberations || filteredDeliberations.length === 0">
              <TableCell colspan="13" class="text-center py-8 text-gray-500">
                Aucune délibération trouvée pour cette classe et ce cours
              </TableCell>
            </TableRow>
            <TableRow v-for="delib in filteredDeliberations" :key="delib.id" v-else>
              <TableCell>
                {{
                  [
                    delib.student?.name,
                    delib.student?.lastname,
                    delib.student?.firstname,
                    (delib as any)?.student_name,
                  ]
                    .filter(Boolean)
                    .join(' ') || '-'
                }}
              </TableCell>
              <TableCell class="text-center">{{ delib.note?.P1 ?? '-' }}</TableCell>
              <TableCell class="text-center">{{ delib.note?.P2 ?? '-' }}</TableCell>
              <TableCell class="text-center">{{ delib.note?.E1 ?? '-' }}</TableCell>
              <TableCell class="text-center bg-blue-50/30 font-semibold">{{
                delib.semestre_1_total ?? '-'
              }}</TableCell>
              <TableCell class="text-center">{{ delib.note?.P3 ?? '-' }}</TableCell>
              <TableCell class="text-center">{{ delib.note?.P4 ?? '-' }}</TableCell>
              <TableCell class="text-center">{{ delib.note?.E2 ?? '-' }}</TableCell>
              <TableCell class="text-center bg-blue-50/30 font-semibold">{{
                delib.semestre_2_total ?? '-'
              }}</TableCell>
              <TableCell class="text-center font-medium">{{ delib.moyenne_note ?? '-' }}</TableCell>
              <TableCell class="text-center font-semibold">{{ delib.pourcentage }}%</TableCell>
              <TableCell class="flex justify-center items-center">
                <div class="flex gap-2">
                  <label class="relative inline-flex items-center cursor-pointer group">
                    <input
                      type="checkbox"
                      :checked="delib.is_validated === '1'"
                      @change="toggleValidation(delib)"
                      :disabled="loadingUpdate"
                      class="sr-only peer"
                    />
                    <div
                      class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-[#0093db] transition-colors duration-200 group-hover:ring-2 group-hover:ring-[#0093db]"
                    ></div>
                    <div
                      class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-4"
                    ></div>
                  </label>
                </div>
              </TableCell>
              <TableCell>
                <div class="group flex items-center justify-between">
                  <div class="flex-1"></div>

                  <button
                    class="ml-2 group-hover:hidden rounded-full size-8 flex items-center justify-center hover:bg-gray-100 transition"
                  >
                    <span class="iconify hugeicons--more-vertical-circle-01"></span>
                  </button>

                  <div class="hidden group-hover:flex items-center gap-2 ml-2">
                    <Button
                      variant="ghost"
                      size="icon"
                      class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                      @click="handleViewGeneralDelib(delib)"
                    >
                      <span class="iconify hugeicons--eye"></span>
                    </Button>
                  </div>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </BoxPanelWrapper>

    <!-- Modal d'initialisation -->
    <AlertDialog :open="showInitDialog" @update:open="showInitDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Initialiser les délibérations</AlertDialogTitle>
          <AlertDialogDescription>
            Sélectionnez la classe, le cours et l'année scolaire pour initialiser les délibérations.
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div class="flex flex-col gap-4 py-4">
          <div class="space-y-2">
            <Label for="modal-annee">Année scolaire *</Label>
            <Select v-model="modalAnneeId">
              <SelectTrigger id="modal-annee" class="!h-10 bg-white">
                <SelectValue placeholder="Sélectionnez l'année scolaire" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem
                    v-for="year in allSchoolYears"
                    :key="year.id"
                    :value="String(year.id)"
                    >{{ year.name || year.year }}</SelectItem
                  >
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label for="modal-classe">Classe *</Label>
            <Select v-model="modalClasseId">
              <SelectTrigger id="modal-classe" class="!h-10 bg-white">
                <SelectValue placeholder="Sélectionnez la classe" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-for="c in allClassrooms" :key="c.id" :value="String(c.id)">{{
                    c.name
                  }}</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label for="modal-cours">Cours *</Label>
            <Select v-model="modalCoursId" :disabled="!modalClasseId">
              <SelectTrigger id="modal-cours" class="!h-10 bg-white" :disabled="!modalClasseId">
                <SelectValue placeholder="Sélectionnez d'abord une classe" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-for="c in modalFilteredCourses" :key="c.id" :value="String(c.id)">{{
                    c.label || c.name
                  }}</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel>Annuler</AlertDialogCancel>
          <Button
            @click="initializeDeliberation"
            :disabled="!modalClasseId || !modalCoursId || !modalAnneeId || initializingDeliberation"
          >
            <span v-if="!initializingDeliberation" class="flex items-center gap-2">
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
  </LayoutSaisieOperation>
</template>
