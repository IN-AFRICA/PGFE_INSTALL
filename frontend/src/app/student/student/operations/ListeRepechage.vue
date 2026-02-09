<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
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
  SelectContent,
  Select,
  SelectTrigger,
  SelectValue,
  SelectItem,
  SelectGroup,
} from '@/components/ui/select'
import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import ExcelManagerWithAPI from '@/components/molecules/ExcelManagerWithAPI.vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import { Classroom } from '@/models/classroom'
import { Repechage } from '@/models/repechage'
import { Course } from '@/models/course'
import { Filiere } from '@/models/filiere'
import { SchoolYear } from '@/models/school_year'
import { Student } from '@/models/students'
// États des filtres
const searchQuery = ref('')
const selectedAnnee = ref('')
const selectedFiliere = ref('')
const selectedClasse = ref('')
const selectedCours = ref('')

// API pour charger les repêchages
const {
  data: apiRepechages,
  loading: loadingRepechages,
  error: errorRepechages,
  fetchData: loadRepechages,
} = useGetApi<Repechage[]>(API_ROUTES.GET_REPECHAGES)

// Charger les données de référence
const { data: schoolYearsData, fetchData: loadSchoolYears } = useGetApi<any[]>(
  `${API_ROUTES.GET_SCHOOL_YEARS}?per_page=1000`,
)

const { data: classroomsData, fetchData: loadClassrooms } = useGetApi<Classroom[]>(
  `${API_ROUTES.GET_CLASSROOMS}?per_page=1000`,
)

const { data: coursesData, fetchData: loadCourses } = useGetApi<Course[]>(
  `${API_ROUTES.GET_COURSES}?per_page=1000`,
)

const { data: filterCoursesData, fetchData: loadFilterCourses } = useGetApi<Course[]>(
  API_ROUTES.GET_COURSES,
)

const { data: filieresData, fetchData: loadFilieres } = useGetApi<Filiere[]>(
  `${API_ROUTES.GET_FILLIERES}?per_page=1000`,
)

const { data: studentsData, fetchData: loadStudents } = useGetApi<Student[]>(
  `${API_ROUTES.GET_STUDENTS}?per_page=1000`,
)

// Maps pour accès rapide
const studentsMap = computed(() => {
  const map = new Map<number, Student>()
  const v = studentsData.value
  const students = Array.isArray(v) ? v : (v?.students?.data ?? v?.data ?? [])

  if (Array.isArray(students)) {
    students.forEach((student: Student) => map.set(student.id, student))
  }
  return map
})

const classroomsMap = computed(() => {
  const map = new Map<number, Classroom>()
  const v = classroomsData.value
  const classrooms = Array.isArray(v) ? v : (v?.classrooms?.data ?? v?.data ?? [])

  if (Array.isArray(classrooms)) {
    classrooms.forEach((classroom: any) => map.set(classroom.id, classroom))
  }
  return map
})

const coursesMap = computed(() => {
  const map = new Map<number, Course>()
  const v = coursesData.value
  const courses = Array.isArray(v) ? v : (v?.courses?.data ?? v?.data ?? [])

  if (Array.isArray(courses)) {
    courses.forEach((course: any) => map.set(course.id, course))
  }

  // Ajouter aussi les cours filtrés au cas où
  if (filterCoursesData.value) {
    const v2 = filterCoursesData.value
    const courses2 = Array.isArray(v2) ? v2 : (v2?.courses?.data ?? v2?.data ?? [])
    if (Array.isArray(courses2)) {
      courses2.forEach((course: any) => map.set(course.id, course))
    }
  }
  return map
})

const filieresMap = computed(() => {
  const map = new Map<number, Filiere>()
  const v = filieresData.value
  const filieres = Array.isArray(v) ? v : (v?.filiaires?.data ?? v?.data ?? [])

  if (Array.isArray(filieres)) {
    filieres.forEach((filiere: any) => map.set(filiere.id, filiere))
  }
  return map
})

const schoolYearsMap = computed(() => {
  const map = new Map<number, SchoolYear>()
  const v = schoolYearsData.value
  const years = v?.years || v?.data || (Array.isArray(v) ? v : [])

  if (Array.isArray(years)) {
    years.forEach((year: SchoolYear) => map.set(year.id, year))
  }
  return map
})

// Wrapper pour charger les repêchages avec filtres
const loadRepechageData = async () => {
  if (!selectedClasse.value || !selectedCours.value) return

  const params = {
    classroom_id: selectedClasse.value,
    course_id: selectedCours.value,
  }
  await loadRepechages(params)
}

// Données enrichies avec les noms
const enrichedRepechages = computed(() => {
  if (!apiRepechages.value) return []

  return apiRepechages.value.map((rep: any) => {
    // 1. Essayer de récupérer les objets directement depuis la réponse (si le backend les envoie)
    let student = rep.student
    let classroom = rep.classroom
    let course = rep.course
    let schoolYear = rep.school_year

    // 2. Si pas d'objet imbriqué, chercher dans les maps de référence par ID
    // Utiliser String() pour la comparaison sûre
    if (!student && rep.student_id) student = studentsMap.value.get(Number(rep.student_id))
    if (!classroom && rep.classroom_id)
      classroom = classroomsMap.value.get(Number(rep.classroom_id))
    if (!course && rep.course_id) course = coursesMap.value.get(Number(rep.course_id))
    if (!schoolYear && rep.school_year_id)
      schoolYear = schoolYearsMap.value.get(Number(rep.school_year_id))

    // 3. Récupérer la filière (via la classe ou l'ID direct s'il existe)
    let filiere = null
    if (classroom && classroom.academic_level?.cycle?.filiaire_id) {
      filiere = filieresMap.value.get(Number(classroom.academic_level.cycle.filiaire_id))
    }
    // Fallback on direct filiaire object if it exists? (not standard but possible)

    return {
      ...rep,
      student_name: student?.name
        ? `${student.name} ${student.firstname || ''}`
        : rep.student_name || 'N/A',
      classroom_name: classroom?.name || rep.classroom_name || 'N/A',
      course_name: course?.label || course?.name || rep.course_name || 'N/A',
      school_year_name: schoolYear?.name || rep.school_year_name || 'N/A',
      filiere_name: filiere?.name || rep.filiere_name || 'N/A',
    }
  })
})

// Filtrage des repêchages
const filteredRepechages = computed(() => {
  let filtered = enrichedRepechages.value

  // Filtre par recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(
      (rep) =>
        rep.student_name.toLowerCase().includes(query) ||
        rep.course_name.toLowerCase().includes(query),
    )
  }

  return filtered
})

const filteredCourses = computed(() => {
  if (!filterCoursesData.value) return []
  return Array.isArray(filterCoursesData.value)
    ? filterCoursesData.value
    : (filterCoursesData.value as any)?.data || []
})

// Filtrer les classes par filière
const filteredClassrooms = computed(() => {
  if (!classroomsData.value) return []
  if (!selectedFiliere.value) return classroomsData.value
  return classroomsData.value.filter(
    (classroom: Classroom) =>
      String(classroom.academic_level.cycle.filiaire_id) === String(selectedFiliere.value),
  )
})

// Watcher pour charger les repêchages quand les filtres changent
watch([selectedClasse, selectedCours], () => {
  if (selectedClasse.value && selectedCours.value) {
    loadRepechageData()
  }
})

watch(selectedClasse, async (newVal, oldVal) => {
  if (oldVal !== undefined && newVal !== oldVal) {
    selectedCours.value = ''
  }
  if (newVal) {
    await loadFilterCourses({ classroom_id: newVal })
  }
})

// Reset classe et cours quand filière change
watch(selectedFiliere, (newVal, oldVal) => {
  if (oldVal !== undefined && newVal !== oldVal) {
    selectedClasse.value = ''
    selectedCours.value = ''
  }
})

onMounted(async () => {
  await Promise.all([
    loadSchoolYears(),
    loadClassrooms(),
    loadCourses(),
    loadFilieres(),
    loadStudents(),
  ])

  const years = schoolYearsData.value?.years || schoolYearsData.value?.data || schoolYearsData.value
  if (Array.isArray(years)) {
    const activeYear = years.find((y: SchoolYear) => y.is_active === '1' || y.is_active === 1)
    if (activeYear) {
      selectedAnnee.value = String(activeYear.id)
    }
  }
})

// Computed pour mapper les filtres vers le format FilterBadges
const filterParams = computed(() => ({
  school_year_id: selectedAnnee.value || undefined,
  filiaire_id: selectedFiliere.value || undefined,
  classroom_id: selectedClasse.value || undefined,
  course_id: selectedCours.value || undefined,
}))

// Configuration des données de référence pour FilterBadges
const referenceData = computed(() => ({
  school_year_id:
    schoolYearsData.value?.years || schoolYearsData.value?.data || schoolYearsData.value || [],
  filiaire_id: Array.isArray(filieresData.value) ? filieresData.value : [],
  classroom_id: Array.isArray(classroomsData.value) ? classroomsData.value : [],
  course_id: Array.isArray(coursesData.value) ? coursesData.value : [],
}))

const customLabels = {
  school_year_id: (value: any) => {
    const year = schoolYearsMap.value.get(Number(value))
    return year ? `Année: ${year.name}` : String(value)
  },
  filiaire_id: (value: any) => {
    const filiere = filieresMap.value.get(Number(value))
    return filiere ? `Section: ${filiere.name}` : String(value)
  },
  classroom_id: (value: any) => {
    const classroom = classroomsMap.value.get(Number(value))
    return classroom ? `Classe: ${classroom.name}` : String(value)
  },
  course_id: (value: any) => {
    const course = coursesMap.value.get(Number(value))
    return course ? `Cours: ${course.label}` : String(value)
  },
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'school_year_id') selectedAnnee.value = ''
  if (key === 'filiaire_id') selectedFiliere.value = ''
  if (key === 'classroom_id') selectedClasse.value = ''
  if (key === 'course_id') selectedCours.value = ''
}

// Variables pour les filtres d'export
const exportFilters = ref({
  classroom_id: '',
  course_id: '',
  school_year_id: '',
  filiere_id: '',
  search: '',
})

// Fonction pour synchroniser les filtres avant export
const updateExportFilters = () => {
  exportFilters.value = {
    classroom_id: selectedClasse.value,
    course_id: selectedCours.value,
    school_year_id: selectedAnnee.value,
    filiere_id: selectedFiliere.value,
    search: searchQuery.value,
  }
}

// Handlers pour les événements Excel
const handleImportSuccess = (result: any) => {
  const count = result.imported_count || result.count || 'plusieurs'
  showCustomToast({
    message: `${count} repêchage(s) importé(s) avec succès.`,
    type: 'success',
  })
  loadRepechageData()
  eventBus.emit('repechageUpdated')
}

const handleImportError = (error: any) => {
  showCustomToast({
    message: error || "Erreur lors de l'import des repêchages.",
    type: 'error',
  })
}

const handleExportSuccess = () => {
  showCustomToast({
    message: 'Export des repêchages réussi.',
    type: 'success',
  })
}

const handleExportError = (error: any) => {
  showCustomToast({
    message: "Erreur lors de l'export des repêchages.",
    type: 'error',
  })
}

const handleTemplateSuccess = () => {
  showCustomToast({
    message: 'Template téléchargé avec succès.',
    type: 'success',
  })
}

const handleTemplateError = (error: any) => {
  showCustomToast({
    message: 'Erreur lors du téléchargement du template.',
    type: 'error',
  })
}

const handleBeforeExport = () => {
  updateExportFilters()
}
</script>

<template>
  <LayoutSaisieOperation
    group="operations"
    active-tag-name="repechage"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Repêchage', href: '/apprenants/operations/repechage' },
    ]"
  >
    <BoxPanelWrapper>
      <div class="flex items-center gap-3 justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              v-model="searchQuery"
              type="text"
              id="search"
              name="search"
              placeholder="Rechercher un apprenant..."
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
                    Sélectionnez une classe et un cours pour afficher les repêchages
                  </p>
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
                            v-for="year in Array.from(schoolYearsMap.values())"
                            :key="year.id"
                            :value="String(year.id)"
                          >
                            {{ year.name }}
                          </SelectItem>
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
                            v-for="filiere in Array.from(filieresMap.values())"
                            :key="filiere.id"
                            :value="String(filiere.id)"
                          >
                            {{ filiere.name }}
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="classe">Classe</Label>
                    <Select v-model="selectedClasse">
                      <SelectTrigger id="classe" class="!h-10 bg-white w-full">
                        <SelectValue placeholder="Sélectionnez la classe" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="classroom in filteredClassrooms"
                            :key="classroom.id"
                            :value="String(classroom.id)"
                          >
                            {{ classroom.name }}
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="cours">Cours</Label>
                    <Select v-model="selectedCours">
                      <SelectTrigger id="cours" class="!h-10 bg-white w-full">
                        <SelectValue placeholder="Sélectionnez le cours" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="course in filteredCourses"
                            :key="course.id"
                            :value="String(course.id)"
                          >
                            {{ course.label }}
                          </SelectItem>
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
          <ExcelManagerWithAPI
            :import-url="API_ROUTES.IMPORT_REPECHAGE"
            :export-url="API_ROUTES.EXPORT_REPECHAGE"
            :export-pdf-url="API_ROUTES.EXPORT_PDF_REPECHAGE"
            export-filename="repechages"
            :export-filters="exportFilters"
            :show-export-excel="true"
            :show-export-pdf="true"
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
              <TableHead> Année scolaire </TableHead>
              <TableHead> Section </TableHead>
              <TableHead> Classe </TableHead>
              <TableHead> Cours </TableHead>
              <TableHead> Élève </TableHead>
              <TableHead> Note obtenue (%) </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loadingRepechages">
              <TableCell :colspan="7" class="text-center py-8 text-gray-500">
                Chargement des données...
              </TableCell>
            </TableRow>
            <TableRow v-else-if="errorRepechages">
              <TableCell :colspan="7" class="text-center py-8 text-red-500">
                Erreur: {{ errorRepechages }}
              </TableCell>
            </TableRow>
            <TableRow v-else-if="!selectedClasse || !selectedCours">
              <TableCell :colspan="7" class="text-center py-8 text-gray-500">
                Veuillez sélectionner une classe et un cours pour voir les repêchages
              </TableCell>
            </TableRow>
            <TableRow v-else-if="filteredRepechages.length === 0">
              <TableCell :colspan="7" class="text-center py-8 text-gray-500">
                Aucun repêchage trouvé
              </TableCell>
            </TableRow>
            <TableRow v-else v-for="repechage in filteredRepechages" :key="repechage.id">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{ repechage.school_year_name }}</TableCell>
              <TableCell>{{ repechage.filiere_name }}</TableCell>
              <TableCell>{{ repechage.classroom_name }}</TableCell>
              <TableCell>{{ repechage.course_name }}</TableCell>
              <TableCell>{{ repechage.student_name }}</TableCell>
              <TableCell>{{ repechage.pourcentage.toFixed(2) }}%</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
