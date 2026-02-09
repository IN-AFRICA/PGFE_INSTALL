<script setup lang="ts">
import { onMounted, onUnmounted, computed, reactive, ref } from 'vue'
import LayoutGestionDisciplinaire from '@/components/templates/LayoutGestionDisciplinaire.vue'
import { Button } from '@/components/ui/button'
import SearchInput from '@/components/atoms/SearchInput.vue'
import { useLocalSearch } from '@/composables/useSmartSearch'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue'
import ListSchoolYear from '@/utils/widgets/vues/ListSchoolYear.vue'

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { eventBus } from '@/utils/eventBus.ts'
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

// API returns IDs, so we fetch lookup collections and map IDs to names
interface NoteConduiteAPIItem {
  id: number | string
  school_year_id: number | string
  filiere_id: number | string
  classroom_id: number | string
  student_id: number | string
  fault_count: number | string
  conduite_semester_1_id?: number | string | null
  conduite_semester_2_id?: number | string | null
}

// Lookup datasets
const { data: filieres, fetchData: fetchFilieres } = useGetApi<any[]>(
  `${API_ROUTES.GET_FILLIERES}?per_page=1000`,
)
const { data: classrooms, fetchData: fetchClassrooms } = useGetApi<any[]>(
  `${API_ROUTES.GET_CLASSROOMS}?per_page=1000`,
)
const { data: students, fetchData: fetchStudents } = useGetApi<any[]>(
  `${API_ROUTES.GET_STUDENTS}?per_page=1000`,
)
const { data: schoolYears, fetchData: fetchSchoolYears } = useGetApi<any[]>(
  `${API_ROUTES.GET_SCHOOL_YEARS}?per_page=1000`,
)
const { data: conduites, fetchData: fetchConduites } = useGetApi<any[]>(
  `${API_ROUTES.GET_CONDUITE}?per_page=1000`,
)
const { data, loading, error, fetchData } = useGetApi<NoteConduiteAPIItem[]>(
  API_ROUTES.GET_NOTE_CONDUITES,
)

// Fetch data on mount (load school years first so names are available)
onMounted(async () => {
  await fetchSchoolYears()
  await Promise.all([
    fetchFilieres(),
    fetchClassrooms(),
    fetchStudents(),
    fetchData(),
    fetchConduites(),
  ])
})

// Écouter les événements de mise à jour
onMounted(() => {
  ;(eventBus as any).on('noteConduiteUpdated', async () => {
    console.log('Event noteConduiteUpdated reçu - rechargement des données...')
    await fetchData()
    console.log('Données rechargées avec succès')
  })
})

onUnmounted(() => {
  ;(eventBus as any).off('noteConduiteUpdated')
})

// Filieres may be array or paginated shape
const filiereList = computed(() => {
  const v: any = filieres?.value
  if (Array.isArray(v)) return v
  return v?.filiaires?.data ?? v?.data ?? []
})
const filiereById = computed(
  () => new Map((filiereList?.value ?? []).map((f: any) => [Number(f.id), f])),
)

// Classrooms may be array or paginated shape
const classroomList = computed(() => {
  const v: any = classrooms?.value
  if (Array.isArray(v)) return v
  return v?.classrooms?.data ?? v?.data ?? []
})
const classroomById = computed(
  () => new Map((classroomList?.value ?? []).map((c: any) => [Number(c.id), c])),
)

// Students endpoint may be array or paginated
const studentList = computed(() => {
  const v: any = students?.value
  if (Array.isArray(v)) return v
  return v?.students?.data ?? []
})
const studentById = computed(
  () => new Map((studentList?.value ?? []).map((s: any) => [Number(s.id), s])),
)

const schoolYearsList = computed(() => {
  const v: any = schoolYears?.value
  if (Array.isArray(v)) return v
  return v?.years ?? []
})
const schoolYearById = computed(() => {
  const list = schoolYearsList?.value ?? []
  return new Map(list.map((y: any) => [Number(y.id), y]))
})

// Conduites endpoint (used to resolve labels like "Excellent", "Bien", etc.)
const conduiteList = computed(() => {
  const v: any = conduites?.value
  if (Array.isArray(v)) return v
  return v?.conduites?.data ?? v?.data ?? []
})
const conduiteById = computed(
  () => new Map((conduiteList?.value ?? []).map((c: any) => [Number(c.id), c])),
)

// Helper to resolve conduite display label from various possible API shapes
const getConduiteLabel = (obj: any): string => {
  if (!obj) return ''
  return obj.name ?? obj.label ?? obj.libelle ?? obj.title ?? (typeof obj === 'string' ? obj : '')
}

const { deleteItem, deleting, errorDelete } = useDeleteApi<any>()
const deletingId = ref<number | null>(null)
const handleDelete = async (id: number, label?: string) => {
  const numId = typeof id === 'string' ? Number(id) : id

  if (!Number.isInteger(numId) || numId <= 0) {
    showCustomToast({ message: 'ID invalide: un entier positif est requis', type: 'error' })
    return
  }

  deletingId.value = numId
  const url = API_ROUTES.DELETE_NOTE_CONDUITE(numId)
  await deleteItem(url)

  // Attendre un peu pour que errorDelete.value soit mis à jour
  await new Promise((resolve) => setTimeout(resolve, 100))

  if (errorDelete.value) {
    // Il y a une erreur
    showCustomToast({
      message: errorDelete.value || 'Erreur lors de la suppression',
      type: 'error',
    })
  } else {
    // Pas d'erreur = succès
    await fetchData()
    showCustomToast({
      message: `Note de conduite "${label || numId}" supprimée avec succès`,
      type: 'success',
    })(eventBus as any).emit('noteConduiteUpdated')
  }
  deletingId.value = null
}

// Compute rows for the table with human-readable fields (no de-duplication)
const rows = computed(() => {
  const v: any = data?.value
  const itemsSource: any = Array.isArray(v)
    ? v
    : Array.isArray(v?.data)
      ? v?.data
      : Array.isArray(v?.data?.data)
        ? v?.data?.data
        : []
  const items: NoteConduiteAPIItem[] = itemsSource as NoteConduiteAPIItem[]
  const result: any[] = []
  for (const item of items) {
    const fy = schoolYearById.value.get(Number(item.school_year_id)) as any
    const fil = filiereById.value.get(Number(item.filiere_id)) as any
    const cls = classroomById.value.get(Number(item.classroom_id)) as any
    const stu = studentById.value.get(Number(item.student_id)) as any
    const enriched = item as any

    // Extract ID from item (should be present in simple format, missing in enriched format)
    const rawId =
      (item as any).id ?? (item as any).conduiteGradeId ?? (item as any).conduite_grade_id
    const safeId = rawId ? Number(rawId) : null

    const r = {
      id: Number.isFinite(safeId) && safeId > 0 ? safeId : null,
      originalIdRaw: rawId,
      // IDs originaux pour l'édition
      school_year_id: item.school_year_id,
      filiere_id: item.filiere_id,
      classroom_id: item.classroom_id,
      student_id: item.student_id,
      conduite_semester_1_id: (item as any).conduite_semester_1_id,
      conduite_semester_2_id: (item as any).conduite_semester_2_id,
      // Données d'affichage
      anneeScolaire: enriched.school_year?.name ?? fy?.name ?? String(item.school_year_id),
      filiere: enriched.filiere?.name ?? fil?.name ?? String(item.filiere_id),
      classe: enriched.classroom?.name ?? cls?.name ?? String(item.classroom_id),
      apprenant:
        enriched.student?.name ??
        (stu ? `${stu.name ?? ''} ${stu.firstname ?? ''}`.trim() : String(item.student_id)),
      numeroFauteCommisse: Number(item.fault_count),
      conduiteSemestre1:
        getConduiteLabel(conduiteById.value.get(Number((item as any).conduite_semester_1_id))) ||
        getConduiteLabel(enriched.conduite_semester_1) ||
        String((item as any).conduite_semester_1_id ?? ''),
      conduiteSemestre2:
        getConduiteLabel(conduiteById.value.get(Number((item as any).conduite_semester_2_id))) ||
        getConduiteLabel(enriched.conduite_semester_2) ||
        String((item as any).conduite_semester_2_id ?? ''),
    }
    result.push(r)
  }
  return result
})

// Paramètres de filtrage
const filterParams = reactive({
  classroom_id: undefined as number | undefined,
  school_year_id: undefined as number | undefined,
})

// Données de référence pour les labels des filtres
const referenceData = computed(() => ({
  classroom_id: classroomList.value || [],
  school_year_id: schoolYearsList.value || [],
}))

// Labels personnalisés pour les badges de filtre
const customLabels = {
  classroom_id: (value: any, data: any[]) => {
    const classroom = data?.find((c: any) => c.id === value)
    return classroom ? classroom.name : value
  },
  school_year_id: (value: any, data: any[]) => {
    const year = data?.find((y: any) => y.id === value)
    return year ? year.name : value
  },
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'classroom_id') filterParams.classroom_id = undefined
  if (key === 'school_year_id') filterParams.school_year_id = undefined
}

// Filtrage local des rows par les filtres sélectionnés
const filteredByParams = computed(() => {
  let result = rows.value
  if (filterParams.classroom_id) {
    result = result.filter((r) => Number(r.classroom_id) === Number(filterParams.classroom_id))
  }
  if (filterParams.school_year_id) {
    result = result.filter((r) => Number(r.school_year_id) === Number(filterParams.school_year_id))
  }
  return result
})

const {
  query: searchQuery,
  filteredData: filteredRows,
  clearSearch,
} = useLocalSearch(filteredByParams, [
  'anneeScolaire',
  'filiere',
  'classe',
  'apprenant',
  'conduiteSemestre1',
  'conduiteSemestre2',
])
</script>
<template>
  <LayoutGestionDisciplinaire
    active-tag-name="note-conduite"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Gestion Disciplinaire', href: '/apprenants/operations/gestion-disciplinaire' },
      {
        label: 'Notes de Conduite',
        href: '/apprenants/operations/gestion-disciplinaire/note-conduite',
      },
    ]"
  >
    <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
      <div class="flex flex-1 items-center gap-2">
        <SearchInput
          v-model="searchQuery"
          placeholder="Rechercher (année, section, classe, élève...)"
          wrapper-class="w-full max-w-xs"
          @clear="clearSearch"
        />
        <Popover>
          <PopoverTrigger as-child>
            <Button variant="ghost" size="sm" class="h-9 rounded-md border bg-white">
              <span class="hidden sm:flex">Filtre</span>
              <span class="iconify hugeicons--filter"></span>
            </Button>
          </PopoverTrigger>
          <PopoverContent class="w-80">
            <div class="grid gap-4">
              <div class="space-y-2">
                <h4 class="font-medium leading-none">Filtrage</h4>
              </div>
              <div class="flex flex-col gap-3.5">
                <ListClassRoom v-model="filterParams.classroom_id" />
                <ListSchoolYear v-model="filterParams.school_year_id" />
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
        <!-- <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="md" class="bg-white border border-border rounded-md ">
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
        <Button size="md" class="rounded-md" as-child>
          <RouterLink to="/apprenants/operations/gestion-disciplinaire/note">
            <span class="flex iconify hugeicons--plus-sign"></span>
            <span class="hidden sm:flex">Ajouter Note</span>
          </RouterLink>
        </Button>
      </div>
    </div>
    <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
      <Table class="rounded-md bg-white">
        <TableHeader>
          <TableRow>
            <TableHead class="w-[20px]">
              <Checkbox class="bg-white scale-70" />
            </TableHead>
            <TableHead>Année scolaire</TableHead>
            <TableHead>Section</TableHead>
            <TableHead>Classe</TableHead>
            <TableHead>Élève</TableHead>
            <TableHead>Conduite-semestre 1</TableHead>
            <TableHead>Conduite-semestre 2</TableHead>
            <TableHead> </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell :colspan="9" class="text-center py-10">
              <div class="flex flex-col items-center justify-center text-gray-500">
                <svg
                  class="animate-spin h-6 w-6 text-gray-400 mb-2"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span>Chargement des notes de conduite...</span>
              </div>
            </TableCell>
          </TableRow>
          <TableRow
            v-else
            v-for="(item, index) in filteredRows"
            :key="item.id + '-' + index"
            class="group hover:bg-gray-50"
          >
            <TableCell class="w-[40px]">
              <Checkbox class="bg-white scale-70" />
            </TableCell>
            <TableCell>{{ item.anneeScolaire }}</TableCell>
            <TableCell>{{ item.filiere }}</TableCell>
            <TableCell>{{ item.classe }}</TableCell>
            <TableCell>{{ item.apprenant }}</TableCell>
            <TableCell>{{ item.conduiteSemestre1 }}</TableCell>
            <TableCell>{{ item.conduiteSemestre2 }}</TableCell>
            <TableCell>
              <div class="flex items-center gap-2 w-max">
                <!-- Bouton "..." visible par défaut, caché au hover -->
                <button
                  class="flex group-hover:hidden rounded-full size-8 items-center justify-center hover:bg-gray-100 transition"
                  aria-label="Plus d'actions"
                >
                  <span
                    class="iconify hugeicons--more-vertical-circle-01"
                    aria-hidden="true"
                  ></span>
                </button>

                <!-- Boutons d'actions cachés par défaut, visibles au hover -->
                <div class="hidden group-hover:flex items-center gap-2">
                  <Button
                    variant="ghost"
                    size="icon"
                    class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                    @click="
                      $router.push(
                        `/apprenants/operations/gestion-disciplinaire/note-conduite/edit/${item.id}`,
                      )
                    "
                  >
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>
                  <AlertDialog>
                    <AlertDialogTrigger as-child>
                      <Button
                        title="Supprimer cette note de conduite"
                        :disabled="deleting"
                        variant="ghost"
                        size="icon"
                        class="size-8 justify-center text-gray-500 hover:text-red-600 hover:bg-red-50"
                        aria-label="Supprimer"
                      >
                        <span class="iconify hugeicons--delete-02" aria-hidden="true"></span>
                      </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>Supprimer cette note de conduite ?</AlertDialogTitle>
                        <AlertDialogDescription>
                          Cette action est irréversible.
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <AlertDialogFooter>
                        <AlertDialogCancel>Annuler</AlertDialogCancel>
                        <Button
                          variant="destructive"
                          :disabled="
                            deleting && deletingId === Number(item.id ?? item.originalIdRaw)
                          "
                          @click="
                            handleDelete(Number(item.id ?? item.originalIdRaw), item.apprenant)
                          "
                        >
                          <span
                            v-if="deleting && deletingId === Number(item.id ?? item.originalIdRaw)"
                            class="flex items-center gap-2"
                          >
                            <IconifySpinner size="sm" />
                            <span>Suppression...</span>
                          </span>
                          <span v-else>Confirmer</span>
                        </Button>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                </div>
              </div>
              <div v-if="errorDelete" class="text-red-500 text-xs mt-1">{{ errorDelete }}</div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </LayoutGestionDisciplinaire>
</template>
