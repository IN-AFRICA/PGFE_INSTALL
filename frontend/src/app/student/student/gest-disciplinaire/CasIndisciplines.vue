<script setup lang="ts">
import LayoutGestionDisciplinaire from '@/components/templates/LayoutGestionDisciplinaire.vue'
import { Button } from '@/components/ui/button'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue'
import ListSchoolYear from '@/utils/widgets/vues/ListSchoolYear.vue'
import DateFilter from '@/utils/widgets/vues/DateFilter.vue'

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { onMounted, ref, computed, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'
import type { IndisciplineCase } from '@/models/indiscipline_case'

const router = useRouter()

// API integration
const {
  data: indisciplineCases,
  fetchData,
  loading,
  error,
} = useGetApi<IndisciplineCase[]>(API_ROUTES.GET_INDISCIPLINE_CASES)
const { deleteItem, deleting: deleting, errorDelete } = useDeleteApi()

// Pagination
const page = ref(1)
const perPage = ref(10)

const query = ref('')
// Lookup datasets for resolving flat IDs to labels
const { data: students, fetchData: fetchStudents } = useGetApi<any[]>(
  `${API_ROUTES.GET_STUDENTS}?per_page=1000`,
)
const { data: classrooms, fetchData: fetchClassrooms } = useGetApi<any[]>(
  `${API_ROUTES.GET_CLASSROOMS}?per_page=1000`,
)
const { data: filieres, fetchData: fetchFilieres } = useGetApi<any[]>(
  `${API_ROUTES.GET_FILLIERES}?per_page=1000`,
)
const { data: schoolYears, fetchData: fetchSchoolYears } = useGetApi<any[]>(
  `${API_ROUTES.GET_SCHOOL_YEARS}?per_page=1000`,
)

onMounted(async () => {
  await Promise.all([
    fetchStudents(),
    fetchClassrooms(),
    fetchFilieres(),
    fetchSchoolYears(),
    fetchData({ page: page.value, limit: perPage.value }),
  ])
})

// Paramètres de filtrage
const filterParams = reactive({
  classroom_id: undefined as number | undefined,
  school_year_id: undefined as number | undefined,
  date: undefined as string | undefined,
})

// Données de référence pour les labels des filtres
const referenceData = computed(() => ({
  classroom_id: classroomList.value || [],
  school_year_id: schoolYearList.value || [],
  date: [],
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
  date: (value: any) => value,
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'classroom_id') filterParams.classroom_id = undefined
  if (key === 'school_year_id') filterParams.school_year_id = undefined
  if (key === 'date') filterParams.date = undefined
}

// Recharger les données quand les filtres changent
watch(
  [() => filterParams.classroom_id, () => filterParams.school_year_id, () => filterParams.date],
  () => {
    const params: any = { page: 1, limit: perPage.value }
    if (filterParams.classroom_id) params.classroom_id = filterParams.classroom_id
    if (filterParams.school_year_id) params.school_year_id = filterParams.school_year_id
    if (filterParams.date) params.date = filterParams.date
    page.value = 1
    fetchData(params)
  },
)

// Listen for updates
eventBus.on('indisciplineCaseUpdated', () => {
  const params: any = { page: page.value, limit: perPage.value }
  if (filterParams.classroom_id) params.classroom_id = filterParams.classroom_id
  if (filterParams.school_year_id) params.school_year_id = filterParams.school_year_id
  if (filterParams.date) params.date = filterParams.date
  fetchData(params)
})

// Normalize lookup lists and build maps by ID
const studentList = computed(() => {
  const v: any = students?.value
  return Array.isArray(v) ? v : (v?.students?.data ?? v?.data ?? [])
})
const studentById = computed(
  () => new Map((studentList.value ?? []).map((s: any) => [Number(s.id), s])),
)

const classroomList = computed(() => {
  const v: any = classrooms?.value
  return Array.isArray(v) ? v : (v?.classrooms?.data ?? v?.data ?? [])
})
const classroomById = computed(
  () => new Map((classroomList.value ?? []).map((c: any) => [Number(c.id), c])),
)

const filiereList = computed(() => {
  const v: any = filieres?.value
  return Array.isArray(v) ? v : (v?.filiaires?.data ?? v?.data ?? [])
})
const filiereById = computed(
  () => new Map((filiereList.value ?? []).map((f: any) => [Number(f.id), f])),
)

const schoolYearList = computed(() => {
  const v: any = schoolYears?.value
  return Array.isArray(v) ? v : (v?.years ?? [])
})
const schoolYearById = computed(
  () => new Map((schoolYearList.value ?? []).map((y: any) => [Number(y.id), y])),
)

const actionList = computed(() => {
  const v: any = actions?.value
  return Array.isArray(v) ? v : (v?.actions?.data ?? v?.data ?? [])
})
const actionById = computed(
  () => new Map((actionList.value ?? []).map((a: any) => [Number(a.id), a])),
)

// Build enriched rows resolved from flat IDs
const rows = computed(() => {
  const v: any = indisciplineCases?.value
  const items: IndisciplineCase[] = Array.isArray(v)
    ? v
    : Array.isArray(v?.data)
      ? v.data
      : Array.isArray(v?.data?.data)
        ? v.data.data
        : []
  return items
    .map((item: IndisciplineCase) => {
      const id = Number(item.id)
      const stu = studentById.value.get(Number(item.student_id))
      const cls = classroomById.value.get(Number(item.classroom_id))
      const fil = filiereById.value.get(Number(item.filiere_id))
      const yr = schoolYearById.value.get(Number(item.school_year_id))

      const apprenant = stu
        ? `${stu.name ?? ''} ${stu.firstname ?? ''}`.trim()
        : String(item.student_id ?? '')
      const classe = item.classroom?.name || cls?.name || String(item.classroom_id ?? '')
      const filiere = cls?.academic_level?.cycle?.filiaire?.name || fil?.name || ''
      const anneeScolaire = item.school_year?.name || yr?.name || String(item.school_year_id ?? '')
      const actionPunition = item.action

      return {
        id,
        date: item.date,
        apprenant,
        fault_count: item.fault_count,
        actionPunition,
        roi: item.roi || '-',
        classe,
        filiere,
        anneeScolaire,
      }
    })
    .filter((item: any) => {
      // Apply search filter
      const searchTerm = query.value.toLowerCase()
      if (searchTerm) {
        return (
          item.apprenant.toLowerCase().includes(searchTerm) ||
          item.classe.toLowerCase().includes(searchTerm) ||
          item.filiere.toLowerCase().includes(searchTerm) ||
          item.anneeScolaire.toLowerCase().includes(searchTerm) ||
          (item.actionPunition && item.actionPunition.toLowerCase().includes(searchTerm))
        )
      }
      return true
    })
})

// Handle edit
const handleEdit = (id: number) => {
  router.push(`/apprenants/operations/gestion-disciplinaire/cas?edit=true&id=${id}`)
}

// Handle delete
const handleDelete = async (id: number) => {
  const url = API_ROUTES.DELETE_INDISCIPLINE_CASE.replace(':indisciplineCase', String(id))
  await deleteItem(url)

  if (errorDelete.value) {
    showCustomToast({ message: errorDelete.value, type: 'error' })
  } else {
    showCustomToast({ message: "Cas d'indiscipline supprimé avec succès.", type: 'success' })
    eventBus.emit('indisciplineCaseUpdated')
  }
}
</script>
<template>
  <LayoutGestionDisciplinaire
    active-tag-name="indiscipline"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Gestion Disciplinaire', href: '/apprenants/operations/gestion-disciplinaire' },
      {
        label: 'Cas d\'Indiscipline',
        href: '/apprenants/operations/gestion-disciplinaire/cas-indisciplines',
      },
    ]"
  >
    <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
      <div class="flex flex-1 items-center gap-2">
        <div class="relative w-full max-w-xs">
          <Input
            v-model="query"
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher..."
            class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-9 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
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
                <DateFilter v-model="filterParams.date" label="Date" />
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
        <Button size="md" class="rounded-md" as-child>
          <RouterLink to="/apprenants/operations/gestion-disciplinaire/cas">
            <span class="flex iconify hugeicons--plus-sign"></span>
            <span class="hidden sm:flex">Ajouter Cas</span>
          </RouterLink>
        </Button>
      </div>
    </div>

    <div v-if="loading" class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8">
      <div class="flex flex-col items-center justify-center w-full gap-3">
        <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
        <span>Chargement des cas d'indiscipline...</span>
      </div>
    </div>

    <div
      v-else-if="rows && rows.length > 0"
      class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
    >
      <Table class="rounded-md bg-white">
        <TableHeader>
          <TableRow>
            <TableHead class="w-[20px]">
              <Checkbox class="bg-white scale-70" />
            </TableHead>
            <TableHead>Date</TableHead>
            <TableHead>Élève</TableHead>
            <TableHead>Faute commise</TableHead>
            <TableHead>Action/Punition</TableHead>
            <TableHead>ROI</TableHead>
            <TableHead>Classe</TableHead>
            <TableHead>Section</TableHead>
            <TableHead>Année scolaire</TableHead>
            <TableHead> </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="item in rows" :key="item.id">
            <TableCell class="w-[40px]">
              <Checkbox class="bg-white scale-70" />
            </TableCell>
            <TableCell>{{ item.date }}</TableCell>
            <TableCell>{{ item.apprenant }}</TableCell>
            <TableCell>{{ item.fault_count }}</TableCell>
            <TableCell>{{ item.actionPunition }}</TableCell>
            <TableCell>{{ item.roi }}</TableCell>
            <TableCell>{{ item.classe }}</TableCell>
            <TableCell>{{ item.filiere }}</TableCell>
            <TableCell>{{ item.anneeScolaire }}</TableCell>
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
                    @click="handleEdit(item.id)"
                  >
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>

                  <AlertMessage
                    action="danger"
                    title="Supprimer un cas d'indiscipline"
                    :message="`Vous êtes sur le point de supprimer le cas d'indiscipline de '${item.apprenant}'. Êtes-vous sûr de continuer?`"
                  >
                    <template #trigger>
                      <Button
                        variant="ghost"
                        size="icon"
                        class="size-8 justify-center text-gray-500 hover:text-red-600 hover:bg-red-50"
                      >
                        <span class="iconify hugeicons--delete-02"></span>
                      </Button>
                    </template>
                    <template #confirm-action-button>
                      <Button
                        variant="destructive"
                        :disabled="deleting"
                        @click="handleDelete(item.id)"
                        size="sm"
                        class="h-10 px-4"
                      >
                        {{ deleting ? 'Suppression...' : 'Oui, Supprimer' }}
                      </Button>
                    </template>
                  </AlertMessage>
                </div>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div v-else class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8">
      <div class="flex flex-col items-center justify-center w-full gap-3">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="size-12 text-foreground-muted/50"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"
          />
        </svg>
        <span class="text-sm text-foreground-muted"
          >Aucun cas d'indiscipline trouvé pour le moment.</span
        >
        <span v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</span>
      </div>
    </div>
  </LayoutGestionDisciplinaire>
</template>
