<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
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

import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import { eventBus } from '@/utils/eventBus'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import AlertMessage from '@/components/modals/AlertMessage.vue'

import { computed, reactive, watch, ref } from 'vue'
import ExportDropdown from '@/components/ExportDropdown.vue'
import { useFileExport } from '@/composables/useFileExport'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import ListClassRoom from '@/utils/widgets/vues/ListClassRoom.vue'
import ListFiliere from '@/utils/widgets/vues/ListFiliere.vue'
import ListGender from '@/utils/widgets/vues/ListGender.vue'

const { data, loading, error, fetchData, meta } = useGetApi(API_ROUTES.GET_STUDENT_REGISTRATIONS)
const { deleting, errorDelete, deleteItem } = useDeleteApi()

// Paramètres de filtrage
const filterParams = reactive({
  classroom_id: undefined as number | undefined,
  filiaire_id: undefined as number | undefined,
  gender: undefined as string | undefined,
})

// Données pour les labels des filtres
const { data: classrooms, fetchData: fetchClassrooms } = useGetApi(API_ROUTES.GET_CLASSROOMS)
const { data: filieres, fetchData: fetchFilieres } = useGetApi(API_ROUTES.GET_FILLIERES)
const { data: countries, fetchData: fetchCountries } = useGetApi(API_ROUTES.GET_COUNTRIES)

fetchClassrooms()
fetchFilieres()
fetchCountries()

// configuration des données de référence
const referenceData = computed(() => ({
  classroom_id: classrooms.value || [],
  filiaire_id: filieres.value || [],
}))

// configuration ds labels
const custtomLabels = {
  classroom_id: (value: any, data: any[]) => {
    const classroom = data?.find((c: any) => c.id === value)
    return classroom ? classroom.name : value
  },
  filiaire_id: (value: any, data: any[]) => {
    const filiere = data?.find((f: any) => f.id === value)
    return filiere ? filiere.name : value
  },
  gender: (value: any) => {
    return value
  },
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'classroom_id') filterParams.classroom_id = undefined
  if (key === 'filiaire_id') filterParams.filiaire_id = undefined
  if (key === 'gender') filterParams.gender = undefined
}

const { query } = useSearch((params: { search: string }) => {
  fetchData({ page: page.value, per_page: perPageCount.value, ...filterParams, ...params })
}, 500)
const { page, perPageCount, total } = usePagination(fetchData, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

fetchData({ page: page.value, per_page: perPageCount.value })

// Watch pour les filtres
watch(
  [() => filterParams.classroom_id, () => filterParams.filiaire_id, () => filterParams.gender],
  () => {
    const params: any = {
      page: page.value,
      per_page: perPageCount.value,
      search: query.value,
    }
    if (filterParams.classroom_id) params.classroom_id = filterParams.classroom_id
    if (filterParams.filiaire_id) params.filiaire_id = filterParams.filiaire_id
    if (filterParams.gender) params.gender = filterParams.gender

    fetchData(params)
  },
  { deep: true },
)

watch(meta, (m) => {
  if (!m) return
  if (typeof m.total === 'number') total.value = m.total
  if (typeof m.per_page === 'number' && perPageCount.value !== m.per_page) {
    perPageCount.value = m.per_page
  }
  if (typeof m.current_page === 'number' && page.value !== m.current_page) {
    page.value = m.current_page
  }
})

eventBus.on('studentUpdated', () => {
  fetchData({
    page: page.value,
    per_page: perPageCount.value,
    search: query.value,
    ...filterParams,
  })
})

const handleDelete = async (id: number) => {
  const url = API_ROUTES.DELETE_STUDENT.replace(':student', String(id))
  await deleteItem(url)
  eventBus.emit('studentUpdated')
  if (errorDelete.value) {
    showCustomToast({
      message: errorDelete.value,
      type: 'error',
    })
    return
  } else {
    showCustomToast({
      message: 'Classe supprimée avec succès.',
      type: 'success',
    })
  }
}

function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}

// Fonction pour résoudre le nom du pays à partir de son ID
function resolveCountryName(countryId: number | string | undefined): string {
  if (!countryId) return '—'
  const country = (countries.value || []).find((c: any) => String(c.id) === String(countryId))
  return country?.name || '—'
}

// === Export ===
const { loading: exportLoading, exportMultiFormat } = useFileExport()

const handleExport = async (format: 'pdf' | 'excel' = 'excel') => {
  // Construire la query string à partir des filtres actuels
  const params = new URLSearchParams()
  if (filterParams.classroom_id) params.append('classroom_id', String(filterParams.classroom_id))
  if (filterParams.filiaire_id) params.append('filiaire_id', String(filterParams.filiaire_id))
  if (filterParams.gender) params.append('gender', filterParams.gender)
  if (query.value) params.append('search', query.value)

  const queryString = params.toString()
  const excelUrl = queryString
    ? `${API_ROUTES.EXPORT_STUDENT_REGISTRATIONS}?${queryString}`
    : API_ROUTES.EXPORT_STUDENT_REGISTRATIONS
  const pdfUrl = queryString
    ? `${API_ROUTES.EXPORT_STUDENT_REGISTRATIONS_PDF}?${queryString}`
    : API_ROUTES.EXPORT_STUDENT_REGISTRATIONS_PDF

  await exportMultiFormat(pdfUrl, excelUrl, format, 'inscriptions_etudiants')
}
</script>

<template>
  <LayoutSaisieOperation
    active-tag-name="inscriptions"
    group="operations"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Opérations', href: '/apprenants/operations' },
      { label: 'Inscriptions', href: '/apprenants/operations' },
    ]"
  >
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              type="text"
              v-model="query"
              id="search"
              name="search"
              placeholder="Recherche..."
              class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
            />
            <div
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
            >
              <span class="flex iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <Popover>
            <PopoverTrigger as-child>
              <Button variant="ghost" size="sm" class="h-10 rounded-md border bg-white">
                <span class="hidden sm:flex"> Filtre</span>
                <span class="iconify hugeicons--filter">Filtre</span>
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-80">
              <div class="grid gap-4">
                <div class="space-y-2">
                  <h4 class="font-medium leading-none">Filtrage</h4>
                </div>
                <div class="flex flex-col gap-3.5">
                  <ListClassRoom v-model="filterParams.classroom_id" />
                  <ListFiliere v-model="filterParams.filiaire_id" />
                  <ListGender v-model="filterParams.gender" :use-full-label="true" />
                </div>
              </div>
            </PopoverContent>
          </Popover>

          <FilterBadges
            :filters="filterParams"
            :reference-data="referenceData"
            :custom-labels="custtomLabels"
            @remove-filter="removeFilter"
          />
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" as-child>
            <RouterLink to="/apprenants/operations/nouveau-eleve">
              <span class="flex iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Nouveau Eleve</span>
            </RouterLink>
          </Button>
          <ExportDropdown :loading="exportLoading" @export="handleExport" />
        </div>
      </div>
      <div
        v-if="loading"
        class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500"
      >
        <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
        <span>Chargement des inscriptions...</span>
      </div>
      <div
        v-else-if="data && data.length"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
      >
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead class="w-[60px]">N°</TableHead>
              <TableHead>Matricule</TableHead>
              <TableHead>Nom</TableHead>
              <TableHead>Postnom</TableHead>
              <TableHead>Prénom</TableHead>
              <TableHead>Pays</TableHead>
              <TableHead>Sexe</TableHead>
              <TableHead>Classe</TableHead>
              <TableHead>Section</TableHead>
              <TableHead>Date de naissance</TableHead>
              <TableHead>Lieu de naissance</TableHead>
              <TableHead>N° Tél</TableHead>
              <TableHead> Operations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in data" :key="item.id">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{ (page - 1) * perPageCount + (data.indexOf(item) + 1) }}</TableCell>
              <TableCell>{{ item.student.matricule }}</TableCell>
              <TableCell>{{ item.student_name }}</TableCell>
              <TableCell>{{ item.student?.lastname || '—' }}</TableCell>
              <TableCell>{{ item.student?.firstname || '—' }}</TableCell>
              <TableCell>{{
                resolveCountryName(item.country_id || item.student?.country_id)
              }}</TableCell>
              <TableCell>{{ item.student.gender }}</TableCell>
              <TableCell>{{ item.classroom_name || 'Non assigné' }}</TableCell>
              <TableCell>{{ item.filiaire.name || 'Non assigné' }}</TableCell>
              <TableCell>{{
                new Date(item.student.birth_date).toLocaleDateString('fr-FR')
              }}</TableCell>
              <TableCell>{{ item.student?.birth_place || '—' }}</TableCell>
              <TableCell>{{ item.student?.phone_number || '—' }}</TableCell>
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
                    >
                      <RouterLink :to="`/apprenants/operations/edition-eleve/${item.id}`">
                        <span class="iconify hugeicons--edit-02"></span>
                      </RouterLink>
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                    >
                      <RouterLink :to="`/apprenants/operations/student-infos/${item.student_id}`">
                        <span class="iconify hugeicons--eye"></span>
                      </RouterLink>
                    </Button>

                    <AlertMessage
                      action="danger"
                      title="Supprimer un étudiant"
                      :message="`Vous êtes sur le point de supprimer l'étudiant '${item.student.name} ${item.student.firstname}'. Êtes-vous sûr de continuer?`"
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
                          @click="handleDelete(item.id)"
                          size="sm"
                          class="h-10 px-4"
                        >
                          Oui, Supprimer
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
      <div
        v-else
        class="flex flex-col items-center justify-center h-full py-10 bg-white rounded-md text-gray-500"
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
        <span>Aucune inscription trouvée pour le moment.</span>
      </div>

      <TabPagination
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
