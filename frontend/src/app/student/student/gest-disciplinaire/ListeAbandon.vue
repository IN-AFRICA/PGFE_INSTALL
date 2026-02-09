<script setup lang="ts">
import LayoutGestionDisciplinaire from '@/components/templates/LayoutGestionDisciplinaire.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import abandonCasesService from '@/services/abandonCasesService'
import SchoolYearService from '@/services/SchoolYearService'
import ClassroomService from '@/services/ClassroomService'
import FiliaireService from '@/services/FiliaireService'
import StudentsService from '@/services/StudentsService'
import type { AbandonCasePayload } from '@/types'
import SemestreService from '@/services/SemestreService'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue'
import ListFiliere from '@/utils/widgets/vues/ListFiliere.vue'
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
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { onMounted, ref, reactive, watch, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useSearch } from '@/composables/useSearch'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
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

// Example data lists (replace with API calls)
const anneesScolaires = ref([{ id: 1, label: '--------' }])
const classes = ref([{ id: 1, name: '-----------' }])
const filieres = ref([{ id: 1, name: '----------------------' }])
const semestres = ref([{ id: 1, label: '----------' }])
const students = ref([{ id: 1, full_name: '-------------' }])

const abandons = ref<(AbandonCasePayload & { id: number; created_at: string })[]>([])
const loading = ref(false)
const deleting = ref(false)

onMounted(() => {
  getAllSchoolYear()
  getAllClassroom()
  getAllFiliaire()
  getAllStudents()
  getAllSemestre()
  getAllAbandonCases()
})

// api call form services
const getAllSchoolYear = async () => {
  const res = await SchoolYearService.getAllSchoolYear()
  if (res) {
    anneesScolaires.value = res?.data?.years.map((y: any) => ({ id: y.id, label: y.name }))
  }
}
const getAllClassroom = async () => {
  const res = await ClassroomService.getAllClassroom()
  if (res && res.data) {
    // Handle both paginated and non-paginated responses or different structures
    const items = Array.isArray(res.data)
      ? res.data
      : Array.isArray(res.data.data)
        ? res.data.data
        : Array.isArray(res.data.data.data)
          ? res.data.data.data
          : []
    classes.value = items.map((c: any) => ({ id: c.id, name: c.name }))
  }
}
const getAllFiliaire = async () => {
  const res = await FiliaireService.getAllFiliaire()
  if (res) {
    filieres.value = res?.data?.data.map((f: any) => ({ id: f.id, name: f.name }))
  }
}
const getAllSemestre = async () => {
  const res = await SemestreService.getAllSemestre()
  if (res) {
    semestres.value = res?.data?.data.map((s: any) => ({ id: s.id, label: s.name }))
  }
}
const getAllStudents = async () => {
  const res = await StudentsService.getAllStudents()
  if (res) {
    students.value = res?.data?.students?.data.map((st: any) => ({
      id: st?.id,
      full_name: `${st?.name ?? ''} ${st?.firstname ?? ''} ${st?.lastname ?? ''}`,
    }))
  }
}
const getAllAbandonCases = async (params: { search?: string } = {}) => {
  loading.value = true
  try {
    const res = await abandonCasesService.getAllAbandonCases(params)
    if (res?.data) {
      abandons.value = res?.data?.data?.data || []
    }
  } catch (error) {
    showCustomToast({ message: 'Erreur lors du chargement des abandons', type: 'error' })
  } finally {
    loading.value = false
  }
}

const { query } = useSearch((params: { search?: string }) => {
  getAllAbandonCases({ ...params, ...filterParams })
}, 500)

// Paramètres de filtrage
const filterParams = reactive({
  classroom_id: undefined as number | undefined,
  filiere_id: undefined as number | undefined,
  school_year_id: undefined as number | undefined,
  date: undefined as string | undefined,
})

// Données de référence pour les labels des filtres
const referenceData = computed(() => ({
  classroom_id: classes.value || [],
  filiere_id: filieres.value || [],
  school_year_id: anneesScolaires.value || [],
  date: [],
}))

// Labels personnalisés pour les badges de filtre
const customLabels = {
  classroom_id: (value: any, data: any[]) => {
    const classroom = data?.find((c: any) => c.id === value)
    return classroom ? classroom.name : value
  },
  filiere_id: (value: any, data: any[]) => {
    const filiere = data?.find((f: any) => f.id === value)
    return filiere ? filiere.name : value
  },
  school_year_id: (value: any, data: any[]) => {
    const year = data?.find((y: any) => y.id === value)
    return year ? year.label : value
  },
  date: (value: any) => value,
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'classroom_id') filterParams.classroom_id = undefined
  if (key === 'filiere_id') filterParams.filiere_id = undefined
  if (key === 'school_year_id') filterParams.school_year_id = undefined
  if (key === 'date') filterParams.date = undefined
}

// Recharger les données quand les filtres changent
watch(
  [
    () => filterParams.classroom_id,
    () => filterParams.filiere_id,
    () => filterParams.school_year_id,
    () => filterParams.date,
  ],
  () => {
    const params: any = {}
    if (filterParams.classroom_id) params.classroom_id = filterParams.classroom_id
    if (filterParams.filiere_id) params.filiere_id = filterParams.filiere_id
    if (filterParams.school_year_id) params.school_year_id = filterParams.school_year_id
    if (filterParams.date) params.date = filterParams.date
    getAllAbandonCases(params)
  },
)

const deletingId = ref<number | null>(null)

const deleteAbandonCase = async (id: number) => {
  deletingId.value = id
  deleting.value = true
  try {
    await abandonCasesService.deleteAbandonCase(id)
    showCustomToast({ message: 'Abandon supprimé avec succès', type: 'success' })
    await getAllAbandonCases(filterParams)
  } catch (error) {
    showCustomToast({ message: 'Erreur lors de la suppression', type: 'error' })
  } finally {
    deleting.value = false
    deletingId.value = null
  }
}

function formatReadableDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <LayoutGestionDisciplinaire
    active-tag-name="abandons"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Gestion Disciplinaire', href: '/apprenants/operations/gestion-disciplinaire' },
      { label: 'Abandons', href: '/apprenants/operations/gestion-disciplinaire/abandons' },
    ]"
  >
    <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
      <div class="flex flex-1 items-center gap-2">
        <div class="relative max-w-xs w-full">
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
                <ListFiliere v-model="filterParams.filiere_id" />
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
          <RouterLink to="/apprenants/operations/gestion-disciplinaire/abandon">
            <span class="flex iconify hugeicons--plus-sign"></span>
            <span class="hidden sm:flex">Ajouter Abandon</span>
          </RouterLink>
        </Button>
      </div>
    </div>

    <div
      v-if="loading"
      class="mt-4 flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500"
    >
      <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
      <span>Chargement des abandons...</span>
    </div>
    <div
      v-else-if="abandons && abandons.length"
      class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
    >
      <Table class="rounded-md bg-white">
        <TableHeader>
          <TableRow>
            <TableHead>Date</TableHead>
            <TableHead>Année scolaire</TableHead>
            <TableHead>Classe</TableHead>
            <TableHead>Élève</TableHead>
            <TableHead>Section</TableHead>
            <TableHead>Semestre</TableHead>
            <TableHead>Commentaire</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="item in abandons"
            :key="item?.id || Math.random()"
            class="group hover:bg-gray-50"
          >
            <template v-if="item">
              <TableCell>{{
                item.created_at ? formatReadableDate(item.created_at) : '-'
              }}</TableCell>
              <TableCell>{{
                anneesScolaires.find((a) => a.id == item.school_year_id)?.label || '-'
              }}</TableCell>
              <TableCell>{{
                classes.find((c) => c.id == item.classroom_id)?.name || '-'
              }}</TableCell>
              <TableCell>{{
                students.find((s) => s.id == item.student_id)?.full_name || '-'
              }}</TableCell>
              <TableCell>{{
                filieres.find((f) => f.id == item.filiere_id)?.name || '-'
              }}</TableCell>
              <TableCell>{{
                semestres.find((s) => s.id == item.semester_id)?.label || '-'
              }}</TableCell>
              <TableCell>{{ item.comment || '-' }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <button
                    class="flex group-hover:hidden rounded-full size-8 items-center justify-center hover:bg-gray-100 transition"
                    aria-label="Plus d'actions"
                  >
                    <span
                      class="iconify hugeicons--more-vertical-circle-01"
                      aria-hidden="true"
                    ></span>
                  </button>

                  <div class="hidden group-hover:flex items-center gap-2">
                    <RouterLink
                      :to="`/apprenants/operations/gestion-disciplinaire/abandon/${item.id}`"
                    >
                      <Button
                        variant="ghost"
                        size="icon"
                        class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                        aria-label="Modifier"
                      >
                        <span class="iconify hugeicons--edit-02" aria-hidden="true"></span>
                      </Button>
                    </RouterLink>

                    <AlertDialog>
                      <AlertDialogTrigger as-child>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="size-8 justify-center text-gray-500 hover:text-red-600 hover:bg-red-50"
                          aria-label="Supprimer"
                          :disabled="deleting && deletingId === item.id"
                        >
                          <span class="iconify hugeicons--delete-02" aria-hidden="true"></span>
                        </Button>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Supprimer un abandon</AlertDialogTitle>
                          <AlertDialogDescription>
                            Vous etes sur le point de supprimer l'abandon de '{{
                              students.find((s) => s.id == item.student_id)?.full_name ||
                              'cet élève'
                            }}'. Etes-vous sur de continuer?
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Annuler</AlertDialogCancel>
                          <Button
                            variant="destructive"
                            :disabled="deleting && deletingId === item.id"
                            @click="deleteAbandonCase(item.id)"
                          >
                            <span
                              v-if="deleting && deletingId === item.id"
                              class="flex items-center gap-2"
                            >
                              <IconifySpinner size="sm" />
                              <span>Suppression...</span>
                            </span>
                            <span v-else>Oui, Supprimer</span>
                          </Button>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </div>
                </div>
              </TableCell>
            </template>
          </TableRow>
        </TableBody>
      </Table>
    </div>
    <div
      v-else
      class="mt-4 flex flex-col items-center justify-center h-full py-10 bg-white rounded-md text-gray-500"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="size-6"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"
        />
      </svg>
      <span>Aucun abandon trouvé pour le moment.</span>
    </div>
  </LayoutGestionDisciplinaire>
</template>
