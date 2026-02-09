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
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import TableRowActions from '@/components/molecules/TableRowActions.vue'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import FilterBadges from '@/components/atoms/FilterBadges.vue'

import TabPagination from '@/components/blocks/TabPagination.vue'
import ExcelManagerWithAPI from '@/components/molecules/ExcelManagerWithAPI.vue'
import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import NewClasse from '@/components/modals/NewClasse.vue'
import { useGetApi } from '@/composables/useGetApi'
import { usePagination } from '@/composables/usePagination'
import { eventBus } from '@/utils/eventBus'
import { API_ROUTES } from '@/utils/constants/api_route'

import { useDeleteApi } from '@/composables/useDeleteApi'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { computed, reactive, watch, ref } from 'vue'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

import { useAcademicLevels } from '@/composables/useAcademicLevels'

const { data, loading, error, fetchData, meta } = useGetApi(API_ROUTES.GET_CLASSROOMS)
// Charger les personnels scolaires pour résoudre le nom du titulaire
const { data: personnels, fetchData: fetchPersonnels } = useGetApi<any[]>(
  API_ROUTES.GET_PERSONNELS_ACADEMIQUES,
)
const { data: filieresData, fetchData: fetchFilieres } = useGetApi(API_ROUTES.GET_FILLIERES)

const paramFiliereId = computed(() => filterParams.filiere_id)
const { levelOptions, loadLevels } = useAcademicLevels(paramFiliereId, ref(null))

const { deleting, errorDelete, deleteItem } = useDeleteApi()

const { page, perPageCount } = usePagination(fetchData, 1, 15)
const totalItems = computed(() => meta.value?.total || 0)

fetchData({ page: page.value, limit: perPageCount.value })
fetchPersonnels()
fetchFilieres()
loadLevels()

const filterParams = reactive({
  label: '',
  filiere_id: undefined as number | undefined,
  academic_level_id: undefined as number | undefined,
  indicator: '',
  titulaire_id: undefined as number | undefined,
})

const filieres = computed(() => {
  const v = filieresData.value
  if (Array.isArray(v)) return v
  if ((v as any)?.data && Array.isArray((v as any).data)) return (v as any).data
  return []
})

const personnelList = computed(() => {
  const v: any = personnels?.value
  if (Array.isArray(v)) return v
  return v?.data ?? []
})

const referenceData = computed(() => ({
  filiere_id: filieres.value || [],
  academic_level_id: levelOptions.value || [],
  titulaire_id: personnelList.value || [],
}))

const customLabels: Record<string, (value: any, refData?: any[]) => string> = {
  label: (value) => `Classe: ${value}`,
  indicator: (value) => `Indicateur: ${value}`,
  filiere_id: (value, refData) => {
    const item = refData?.find((i: any) => String(i.id) === String(value))
    return item ? `Section: ${item.name}` : `Section: ${value}`
  },
  academic_level_id: (value, refData) => {
    const item = refData?.find((i: any) => String(i.id) === String(value))
    return item ? `Niveau: ${item.label}` : `Niveau: ${value}`
  },
  titulaire_id: (value, refData) => {
    const item = refData?.find((i: any) => String(i.id) === String(value))
    if (!item) return `Titulaire: ${value}`
    const name = [item.firstname, item.name].filter(Boolean).join(' ')
    return `Titulaire: ${name || value}`
  },
}

const removeFilter = (key: string) => {
  if (key === 'label') filterParams.label = ''
  if (key === 'indicator') filterParams.indicator = ''
  if (key === 'filiere_id') filterParams.filiere_id = undefined
  if (key === 'academic_level_id') filterParams.academic_level_id = undefined
  if (key === 'titulaire_id') filterParams.titulaire_id = undefined
}

// Recharger les données quand la page change (filtrage côté client donc pas de fetch sur filtres)
watch([page, perPageCount], () => {
  fetchData({ page: page.value, limit: perPageCount.value })
})

eventBus.on('classRoomUpdated', () => {
  fetchData({ page: page.value, limit: perPageCount.value })
})

//handleEdit
const handleEdit = (item: any) => {
  eventBus.emit('editClassRoom', item)
}

import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

//handleDelete
const deletingId = ref<number | null>(null)
const handleDelete = async (id: number) => {
  deletingId.value = id
  const url = API_ROUTES.DELETE_CLASSROOM.replace(':classroom', String(id))
  await deleteItem(url)
  eventBus.emit('classRoomUpdated')
  deletingId.value = null

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

// Résoudre le nom du titulaire (id -> "Nom Prénom")
const getTitulaireName = (id: string | number | null | undefined) => {
  if (!id) return '—'
  const list = Array.isArray(personnels?.value) ? (personnels.value as any[]) : []
  const found = list.find((p: any) => String(p.id) === String(id))
  if (!found) return '—'
  const first = String(found.name || '').trim()
  const last = String(found.firstname || '').trim()
  return [first, last].filter(Boolean).join(' ') || '—'
}

const items = computed(() => {
  if (!data.value) return []
  // Handle paginated response { data: [...] } or direct array [...]
  // If data.value is an array, use it.
  if (Array.isArray(data.value)) return data.value
  // If data.value is an object with a data property that is an array, use that.
  if ((data.value as any)?.data && Array.isArray((data.value as any).data))
    return (data.value as any).data
  return []
})

const filteredItems = computed(() => {
  let result = items.value

  // Filtre par nom (label)
  if (filterParams.label && filterParams.label.trim() !== '') {
    const search = filterParams.label.toLowerCase().trim()
    result = result.filter((item: any) => item.name?.toLowerCase().includes(search))
  }

  if (filterParams.indicator && filterParams.indicator.trim() !== '') {
    const search = filterParams.indicator.toLowerCase().trim()
    result = result.filter((item: any) => item.indicator?.toLowerCase().includes(search))
  }

  // Filtre par filière/section
  if (filterParams.filiere_id) {
    result = result.filter(
      (item: any) =>
        String(item.academic_level?.cycle?.filiaire_id) === String(filterParams.filiere_id) ||
        String(item.academic_level?.cycle?.filiaire?.id) === String(filterParams.filiere_id),
    )
  }

  if (filterParams.academic_level_id) {
    result = result.filter(
      (item: any) => String(item.academic_level_id) === String(filterParams.academic_level_id),
    )
  }

  if (filterParams.titulaire_id) {
    result = result.filter(
      (item: any) => String(item.titulaire_id) === String(filterParams.titulaire_id),
    )
  }

  return result
})

const exportFilters = ref<Record<string, any>>({})

const syncExportFilters = () => {
  exportFilters.value = {
    ...(filterParams.label ? { label: filterParams.label } : {}),
    ...(filterParams.indicator ? { indicator: filterParams.indicator } : {}),
    ...(filterParams.filiere_id ? { filiere_id: filterParams.filiere_id } : {}),
    ...(filterParams.academic_level_id
      ? { academic_level_id: filterParams.academic_level_id }
      : {}),
    ...(filterParams.titulaire_id ? { titulaire_id: filterParams.titulaire_id } : {}),
  }
}

const handleExportSuccess = () => {
  showCustomToast({
    message: 'Export réussi ! Le fichier a été téléchargé.',
    type: 'success',
  })
}

const handleExportError = (message: string) => {
  showCustomToast({
    message: message || "Erreur lors de l'export",
    type: 'error',
  })
}
</script>

<template>
  <LayoutSaisieOperation
    active-tag-name="classes"
    group="saisie"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Saisie Préalable', href: '/apprenants/saisie-prealable' },
      { label: 'Classes', href: '/apprenants/saisie-prealable/classes' },
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
              placeholder="Rechercher une classe..."
              v-model="filterParams.label"
              class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
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
                  <div class="flex flex-col space-y-1.5 flex-1">
                    <label for="filter_label" class="text-sm font-medium">Nom de la classe</label>
                    <Input
                      id="filter_label"
                      type="text"
                      v-model="filterParams.label"
                      placeholder="Rechercher par nom..."
                      class="h-10"
                    />
                  </div>

                  <div class="flex flex-col space-y-1.5 flex-1">
                    <label class="text-sm font-medium">Section</label>
                    <Select
                      :model-value="filterParams.filiere_id ? String(filterParams.filiere_id) : ''"
                      @update:model-value="
                        (val) => (filterParams.filiere_id = val ? Number(val) : undefined)
                      "
                    >
                      <SelectTrigger class="h-10 w-full">
                        <SelectValue placeholder="Sélectionnez une section" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem v-for="f in filieres" :key="f.id" :value="String(f.id)">
                            {{ f.name }}
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>

                  <div class="flex flex-col space-y-1.5 flex-1">
                    <label class="text-sm font-medium">Niveau</label>
                    <Select
                      :model-value="
                        filterParams.academic_level_id ? String(filterParams.academic_level_id) : ''
                      "
                      @update:model-value="
                        (val) => (filterParams.academic_level_id = val ? Number(val) : undefined)
                      "
                    >
                      <SelectTrigger class="h-10 w-full">
                        <SelectValue placeholder="Sélectionnez un niveau" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="level in levelOptions"
                            :key="level.id"
                            :value="String(level.id)"
                          >
                            {{ level.label }}
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>

                  <div class="flex flex-col space-y-1.5 flex-1">
                    <label for="filter_indicator" class="text-sm font-medium">Indicateur</label>
                    <Input
                      id="filter_indicator"
                      type="text"
                      v-model="filterParams.indicator"
                      placeholder="Ex: A, B..."
                      class="h-10"
                    />
                  </div>
                  <div class="flex flex-col space-y-1.5 flex-1">
                    <label class="text-sm font-medium">Titulaire</label>
                    <Select
                      :model-value="
                        filterParams.titulaire_id ? String(filterParams.titulaire_id) : ''
                      "
                      @update:model-value="
                        (val) => (filterParams.titulaire_id = val ? Number(val) : undefined)
                      "
                    >
                      <SelectTrigger class="h-10 w-full">
                        <SelectValue placeholder="Sélectionnez un titulaire" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem v-for="p in personnelList" :key="p.id" :value="String(p.id)">
                            {{ [p.firstname, p.name].filter(Boolean).join(' ') }}
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
          <!-- <ExcelManagerWithAPI
            button-text="Exporter"
            :show-import="false"
            :show-export-excel="false"
            :show-template="false"
            :export-url="API_ROUTES.EXPORT_CLASSROOMS"
            :export-filters="exportFilters"
            export-filename="classes"
            @before-export="syncExportFilters"
            @export-success="handleExportSuccess"
            @export-error="handleExportError"
          /> -->
          <NewClasse />
        </div>
      </div>
      <div
        v-if="loading"
        class="flex flex-col items-center justify-center py-10 bg-white rounded-md text-gray-500"
      >
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
        <span>Chargement des classes...</span>
      </div>
      <div
        v-else-if="filteredItems.length"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
      >
        <Table class="rounded-md">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead class="w-[60px]">N°</TableHead>
              <TableHead> Designation </TableHead>
              <TableHead> Section </TableHead>
              <TableHead> Niveau </TableHead>
              <TableHead> Indicateur </TableHead>
              <TableHead> Titulaire </TableHead>
              <TableHead> Actions </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in filteredItems" :key="item.id">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{
                (page - 1) * perPageCount + (filteredItems.indexOf(item) + 1)
              }}</TableCell>
              <TableCell>
                {{ item.name }}
              </TableCell>
              <TableCell>
                {{ item.academic_level?.cycle?.filiaire?.name || '—' }}
              </TableCell>
              <TableCell>
                {{ item.academic_level?.name || '—' }}
              </TableCell>
              <TableCell>
                {{ item.indicator ? String(item.indicator).toUpperCase() : '—' }}
              </TableCell>
              <TableCell>
                {{ item.titulaire ? item.titulaire.name : '—' }}
              </TableCell>
              <TableCell>
                <TableRowActions>
                  <template #actions>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                      @click="handleEdit(item)"
                    >
                      <span class="iconify hugeicons--edit-02"></span>
                    </Button>

                    <AlertDialog>
                      <AlertDialogTrigger as-child>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="size-8 justify-center text-gray-500 hover:text-red-600 hover:bg-red-50"
                        >
                          <span class="iconify hugeicons--delete-02"></span>
                        </Button>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Supprimer cette classe ?</AlertDialogTitle>
                          <AlertDialogDescription>
                            Vous êtes sur le point de supprimer la classe '{{ item.name }}'. Cette
                            action est irréversible.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel :disabled="deleting && deletingId === item.id"
                            >Annuler</AlertDialogCancel
                          >
                          <Button
                            :disabled="deleting && deletingId === item.id"
                            @click="handleDelete(item.id)"
                          >
                            <span v-if="!deleting || deletingId !== item.id">Supprimer</span>
                            <span v-else class="flex items-center gap-2">
                              <IconifySpinner class="text-white" />
                              <span>Suppression...</span>
                            </span>
                          </Button>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </template>
                </TableRowActions>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <div
        v-else
        class="flex flex-col items-center justify-center py-10 bg-white rounded-md text-gray-500"
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
        <span>Aucune classe trouvée pour le moment.</span>
      </div>
      <TabPagination
        v-if="items.length"
        v-model="page"
        :perPage="perPageCount"
        :totalItems="totalItems"
        @update:perPage="(val) => (perPageCount = val)"
      />
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
