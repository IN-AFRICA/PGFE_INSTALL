<script setup lang="ts">
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
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import ExcelManagerWithAPI from '@/components/molecules/ExcelManagerWithAPI.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { API_ROUTES } from '@/utils/constants/api_route'
import { ref, onMounted, computed, watch } from 'vue'
import { eventBus } from '@/utils/eventBus'
import FilterBadges from '@/components/atoms/FilterBadges.vue'

// Interface pour les données de validation des lauréats
interface LaureatData {
  id: number
  last_name: string
  middle_name?: string
  first_name: string
  gender: string
  department: string // ID de la filière
  class: string // ID de la classe
  year: string // ID de l'année scolaire
  cycle: string // ID du cycle
  comment?: string
  percentage: number | string // Le backend retourne "percentage" en anglais
}

// Variables réactives
const searchQuery = ref('')
const selectedAnnee = ref('')
const selectedFiliere = ref('')
const selectedClasse = ref('')
const selectedDate = ref('')
const selectedGenre = ref('')
const selectedCycle = ref('')
const selectedPercentage = ref('')

// API pour récupérer les données de validation des lauréats
const {
  data: laureatData,
  error: laureatError,
  loading: laureatLoading,
  fetchData: fetchLaureatData,
} = useGetApi<LaureatData[]>(API_ROUTES.GET_LAUREAT)

// Debug: Afficher les données reçues de l'API
watch(
  laureatData,
  (newData) => {
    if (newData && newData.length > 0) {
      console.log('📊 Données lauréats reçues:', newData)
      console.log('📊 Premier élément:', newData[0])
      console.log(
        '📊 Type de pourcentage:',
        typeof newData[0]?.percentage,
        '- Valeur:',
        newData[0]?.percentage,
      )
    }
  },
  { immediate: true },
)
// API pour charger les données de référence
const { data: schoolYearsData, fetchData: loadSchoolYears } = useGetApi(API_ROUTES.GET_SCHOOL_YEARS)
const { data: classroomsData, fetchData: loadClassrooms } = useGetApi(API_ROUTES.GET_CLASSROOMS)
const { data: filieresData, fetchData: loadFilieres } = useGetApi(API_ROUTES.GET_FILLIERES)
const { data: cyclesData, fetchData: loadCycles } = useGetApi(API_ROUTES.GET_CYCLES)

// Maps pour résoudre les IDs en noms
const getFiliereNameById = (id: string) => {
  if (!filieresData.value || !id) return '-'
  const filiere = Array.isArray(filieresData.value)
    ? filieresData.value.find((f: any) => String(f.id) === String(id))
    : null
  return filiere?.name || id
}

const getClassroomNameById = (id: string) => {
  if (!classroomsData.value || !id) return '-'
  const classroom = Array.isArray(classroomsData.value)
    ? classroomsData.value.find((c: any) => String(c.id) === String(id))
    : null
  return classroom?.name || id
}

const getSchoolYearNameById = (id: string) => {
  if (!schoolYearsData.value || !id) return '-'
  const years = schoolYearsData.value?.years || schoolYearsData.value?.data || schoolYearsData.value
  const year = Array.isArray(years) ? years.find((y: any) => String(y.id) === String(id)) : null
  return year?.name || id
}

const getCycleNameById = (id: string) => {
  if (!cyclesData.value || !id) return '-'
  const cycle = Array.isArray(cyclesData.value)
    ? cyclesData.value.find((c: any) => String(c.id) === String(id))
    : null
  return cycle?.name || id
}

// Fonction pour formater le genre en français
const formatGender = (gender: string) => {
  if (!gender) return '-'
  const genderLower = gender.toLowerCase()
  if (genderLower === 'm' || genderLower === 'masculin' || genderLower === 'male') return 'Masculin'
  if (
    genderLower === 'f' ||
    genderLower === 'féminin' ||
    genderLower === 'feminin' ||
    genderLower === 'female'
  )
    return 'Féminin'
  return gender
}

// Listes computed
const schoolYearsList = computed(() => {
  const v: any = schoolYearsData.value
  if (Array.isArray(v)) return v
  return v?.years ?? v?.data ?? []
})

const filieresList = computed(() => {
  const v: any = filieresData.value
  if (Array.isArray(v)) return v
  return v?.data ?? []
})

const classroomsList = computed(() => {
  const v: any = classroomsData.value
  if (Array.isArray(v)) return v
  return v?.data ?? []
})

const cyclesList = computed(() => {
  const v: any = cyclesData.value
  if (Array.isArray(v)) return v
  return v?.data ?? []
})

// Données filtrées
const filteredLaureatData = computed(() => {
  if (!laureatData.value) return []

  let result = laureatData.value

  // Filtre recherche textuelle
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(
      (item) =>
        item.last_name.toLowerCase().includes(query) ||
        item.first_name.toLowerCase().includes(query) ||
        (item.middle_name && item.middle_name.toLowerCase().includes(query)),
    )
  }

  // Filtre Année
  if (selectedAnnee.value) {
    result = result.filter((item) => String(item.year) === String(selectedAnnee.value))
  }

  // Filtre Filière (Section)
  if (selectedFiliere.value) {
    result = result.filter((item) => String(item.department) === String(selectedFiliere.value))
  }

  // Filtre Classe
  if (selectedClasse.value) {
    result = result.filter((item) => String(item.class) === String(selectedClasse.value))
  }

  // Filtre Genre
  if (selectedGenre.value) {
    const selected = selectedGenre.value.toLowerCase()
    result = result.filter((item) => {
      if (!item.gender) return false
      const g = item.gender.toLowerCase()
      if (selected === 'm') {
        return g === 'm' || g === 'masculin' || g === 'male'
      }
      if (selected === 'f') {
        return g === 'f' || g === 'féminin' || g === 'feminin' || g === 'female'
      }
      return g === selected
    })
  }

  // Filtre Cycle
  if (selectedCycle.value) {
    result = result.filter((item) => String(item.cycle) === String(selectedCycle.value))
  }

  return result
})

// Handlers pour les événements Excel
const handleImportSuccess = (result: any) => {
  console.log('Import réussi:', result)
  const count = result.imported_count || result.count || 'plusieurs'
  showCustomToast({
    message: `${count} validation(s) de lauréat(s) importée(s) avec succès.`,
    type: 'success',
  })
  // Rafraîchir les données après import
  fetchLaureatData()
  // Émettre l'événement pour notifier les autres composants
  eventBus.emit('laureatUpdated')
}

const handleImportError = (error: any) => {
  console.error('Erreur import:', error)
  let errorMessage = "Erreur lors de l'import des validations de lauréats."

  if (error.message) {
    errorMessage = error.message
  } else if (error.errors) {
    if (Array.isArray(error.errors)) {
      errorMessage = error.errors.join(', ')
    } else if (typeof error.errors === 'object') {
      errorMessage = Object.values(error.errors).flat().join(', ')
    }
  }

  showCustomToast({
    message: errorMessage,
    type: 'error',
  })
}

const handleExportSuccess = () => {
  showCustomToast({
    message: 'Export des validations de lauréats réussi.',
    type: 'success',
  })
}

const handleExportError = (error: any) => {
  console.error('Erreur export:', error)
  showCustomToast({
    message: "Erreur lors de l'export des validations de lauréats.",
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
  console.error('Erreur template:', error)
  showCustomToast({
    message: 'Erreur lors du téléchargement du template.',
    type: 'error',
  })
}

// Fonction pour mettre à jour les filtres avant export
const handleBeforeExport = () => {
  updateExportFilters()
}

// Variables pour les filtres d'export
const exportFilters = ref({
  annee_scolaire: '',
  filiere: '',
  classe: '',
  date: '',
  search: '',
})

// Fonction pour synchroniser les filtres
const updateExportFilters = () => {
  exportFilters.value = {
    annee_scolaire: selectedAnnee.value,
    filiere: selectedFiliere.value,
    classe: selectedClasse.value,
    date: selectedDate.value,
    search: searchQuery.value,
  }
  console.log("Filtres d'export mis à jour:", exportFilters.value)
}

// Charger les données au montage du composant
onMounted(() => {
  fetchLaureatData()
  loadSchoolYears()
  loadClassrooms()
  loadFilieres()
  loadCycles()
})

// Écouter les événements de mise à jour
eventBus.on('laureatUpdated', () => {
  fetchLaureatData()
})

// ... (Handlers remain same)

// Computed pour mapper les filtres vers le format FilterBadges
const filterParams = computed(() => ({
  school_year_id: selectedAnnee.value || undefined,
  filiaire_id: selectedFiliere.value || undefined,
  classroom_id: selectedClasse.value || undefined,
  gender: selectedGenre.value || undefined,
  cycle_id: selectedCycle.value || undefined,
}))

// Configuration des données de référence pour FilterBadges
const referenceData = computed(() => ({
  school_year_id: schoolYearsList.value,
  filiaire_id: filieresList.value,
  classroom_id: classroomsList.value,
  cycle_id: cyclesList.value,
}))

// Configuration des labels personnalisés pour FilterBadges
const customLabels = {
  school_year_id: (value: any, data: any[]) => {
    const year = data?.find((y: any) => String(y.id) === String(value))
    return year ? `Année: ${year.name}` : String(value)
  },
  filiaire_id: (value: any, data: any[]) => {
    const filiere = data?.find((f: any) => String(f.id) === String(value))
    return filiere ? `Section: ${filiere.name}` : String(value)
  },
  classroom_id: (value: any, data: any[]) => {
    const classroom = data?.find((c: any) => String(c.id) === String(value))
    return classroom ? `Classe: ${classroom.name}` : String(value)
  },
  cycle_id: (value: any, data: any[]) => {
    const cycle = data?.find((c: any) => String(c.id) === String(value))
    return cycle ? `Cycle: ${cycle.name}` : String(value)
  },
  gender: (value: any) => `Genre: ${value === 'M' ? 'Masculin' : 'Féminin'}`,
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'school_year_id') selectedAnnee.value = ''
  if (key === 'filiaire_id') selectedFiliere.value = ''
  if (key === 'classroom_id') selectedClasse.value = ''
  if (key === 'gender') selectedGenre.value = ''
  if (key === 'cycle_id') selectedCycle.value = ''
}
</script>

<template>
  <LayoutSaisieOperation
    active-tag-name="validation-laureats"
    group="operations"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Validation Lauréat', href: '/apprenants/operations/validation-laureats' },
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
              placeholder="Rechercher un lauréat..."
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
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Sélectionner une année" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="year in schoolYearsList"
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
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Sélectionner une section" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="filiere in filieresList"
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
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Sélectionner une classe" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="classroom in classroomsList"
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
                    <Label class="text-foreground-muted font-light" for="genre">Genre</Label>
                    <Select v-model="selectedGenre">
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Sélectionner un genre" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem value="M">Masculin</SelectItem>
                          <SelectItem value="F">Féminin</SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light" for="cycle">Cycle</Label>
                    <Select v-model="selectedCycle">
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Sélectionner un cycle" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="cycle in cyclesList"
                            :key="cycle.id"
                            :value="String(cycle.id)"
                          >
                            {{ cycle.name }}
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
            :import-url="API_ROUTES.IMPORT_LAUREAT"
            :export-url="API_ROUTES.EXPORT_LAUREAT(selectedClasse)"
            :export-filename="'validation-laureats'"
            :export-filters="exportFilters"
            :show-export-excel="true"
            :show-export-pdf="false"
            @import-success="handleImportSuccess"
            @import-error="handleImportError"
            @export-success="handleExportSuccess"
            @export-error="handleExportError"
            @template-success="handleTemplateSuccess"
            @template-error="handleTemplateError"
            @before-export="handleBeforeExport"
          />
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <!-- État de chargement -->
        <div v-if="laureatLoading" class="flex items-center justify-center w-full h-64">
          <div class="flex items-center gap-2">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            <span class="text-foreground-muted"
              >Chargement des données de validation des lauréats...</span
            >
          </div>
        </div>

        <!-- État d'erreur -->
        <div v-else-if="laureatError" class="flex items-center justify-center w-full h-64">
          <div class="flex items-center gap-2 text-red-600">
            <span class="iconify hugeicons--alert-circle text-xl"></span>
            <span>Erreur lors du chargement des données: {{ laureatError }}</span>
          </div>
        </div>

        <!-- Tableau des données -->
        <Table v-else class="rounded-md bg-white">
          <TableHeader>
            <TableRow class="bg-primary">
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead>Nom</TableHead>
              <TableHead>Prénom</TableHead>
              <TableHead>Post-nom</TableHead>
              <TableHead>Genre</TableHead>
              <TableHead>Section</TableHead>
              <TableHead>Classe</TableHead>
              <TableHead>Année</TableHead>
              <TableHead>Cycle</TableHead>
              <TableHead>Pourcentage</TableHead>
              <TableHead>Commentaire</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <!-- Aucune donnée -->
            <TableRow v-if="!filteredLaureatData || filteredLaureatData.length === 0">
              <TableCell :colspan="11" class="text-center py-8">
                <div class="flex flex-col items-center gap-2 text-foreground-muted">
                  <span class="iconify hugeicons--file-search text-2xl"></span>
                  <span v-if="searchQuery"> Aucun résultat trouvé pour "{{ searchQuery }}" </span>
                  <span v-else> Aucune validation de lauréat disponible </span>
                </div>
              </TableCell>
            </TableRow>

            <!-- Données -->
            <TableRow v-for="(item, index) in filteredLaureatData" :key="item.id || index">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{ item.last_name || '-' }}</TableCell>
              <TableCell>{{ item.middle_name || '-' }}</TableCell>
              <TableCell>{{ item.first_name || '-' }}</TableCell>
              <TableCell>{{ formatGender(item.gender) }}</TableCell>
              <TableCell>{{ getFiliereNameById(item.department) }}</TableCell>
              <TableCell>{{ getClassroomNameById(item.class) }}</TableCell>
              <TableCell>{{ getSchoolYearNameById(item.year) }}</TableCell>
              <TableCell>{{ getCycleNameById(item.cycle) }}</TableCell>
              <TableCell>
                {{
                  item.percentage != null && item.percentage !== ''
                    ? Number(item.percentage) + '%'
                    : '-'
                }}
              </TableCell>
              <TableCell>{{ item.comment || '-' }}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
