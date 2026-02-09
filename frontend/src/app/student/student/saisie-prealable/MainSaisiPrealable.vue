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

import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import NewFilliere from '@/components/modals/NewFilliere.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import FilterPopover from '@/components/atoms/FilterPopover.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import ListAcademicalLevel from '@/utils/widgets/vues/ListAcademicalLevel.vue'
import { ref, computed, reactive, watch } from 'vue'

const page = ref(1)
const perPage = ref(15)

const { data, loading, error, fetchData, meta } = useGetApi(API_ROUTES.GET_FILLIERES)
const { deleting, errorDelete, deleteItem } = useDeleteApi()

// Charger les niveaux scolaires pour affichage et filtre
const { data: academicLevels, fetchData: fetchAcademicLevels } = useGetApi<any[]>(
  API_ROUTES.GET_ACADEMIC_LEVELS,
)

fetchData({ page: page.value, limit: perPage.value })
fetchAcademicLevels()

// Paramètres de filtrage
const filterParams = reactive({
  label: '',
  academic_level_id: undefined as number | undefined,
  code: '',
})

// Extraire la liste des niveaux
const levelList = computed(() => {
  const v: any = academicLevels?.value
  if (Array.isArray(v)) return v
  return v?.data ?? []
})

// Map pour l'affichage dans le tableau
const academicLevelMap = computed<Record<string, string>>(() => {
  return levelList.value.reduce(
    (acc: any, item: any) => {
      const key = String(item?.id ?? '')
      const label = String(item?.name ?? item?.label ?? '')
      if (key) acc[key] = label
      return acc
    },
    {} as Record<string, string>,
  )
})

const getAcademicLevelName = (id: string | number) => academicLevelMap.value[String(id)] || 'N/A'

// Données de référence pour les labels des filtres
const referenceData = computed(() => ({
  academic_level_id: levelList.value || [],
}))

// Labels personnalisés pour les badges de filtre
const customLabels: Record<string, (value: any, refData?: any[]) => string> = {
  label: (value) => `Section: ${value}`,
  code: (value) => `Code: ${value}`,
  academic_level_id: (value, refData) => {
    const item = refData?.find((i: any) => String(i.id) === String(value))
    return `Niveau: ${item ? item.name || item.label : value}`
  },
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'label') filterParams.label = ''
  if (key === 'code') filterParams.code = ''
  if (key === 'academic_level_id') filterParams.academic_level_id = undefined
}

eventBus.on('filiereUpdated', () => {
  fetchData({ page: page.value, limit: perPage.value })
})

// Handle edit
const handleEdit = (id: string | number) => {
  // Trouver l'élément à éditer
  const itemToEdit = data.value.find((item: any) => item.id === id)
  if (itemToEdit) {
    eventBus.emit('editFilliere', itemToEdit)
  }
}

const itemToDelete = ref<any>(null)

// Handle delete
const handleDelete = async (item: any) => {
  itemToDelete.value = item
  const url = API_ROUTES.DELETE_FILLIERE.replace(':filiaire', String(item.id))
  await deleteItem(url)
  eventBus.emit('filiereUpdated')
  itemToDelete.value = null

  if (errorDelete.value) {
    showCustomToast({
      message: errorDelete.value,
      type: 'error',
    })
  } else {
    showCustomToast({
      message: 'Section supprimée avec succès.',
      type: 'success',
    })
  }
}

const items = computed(() => {
  if (!data.value) return []
  if (Array.isArray(data.value)) return data.value
  if ((data.value as any)?.data && Array.isArray((data.value as any).data))
    return (data.value as any).data
  return []
})

// Filtrage côté client
const filteredItems = computed(() => {
  let result = [...items.value] // Copie pour ne pas muter l'original

  // Filtre par nom (label)
  if (filterParams.label && filterParams.label.trim() !== '') {
    const search = filterParams.label.toLowerCase().trim()
    result = result.filter((item: any) => item.name?.toLowerCase().includes(search))
  }

  // Filtre par code
  if (filterParams.code && filterParams.code.trim() !== '') {
    const search = filterParams.code.toLowerCase().trim()
    result = result.filter((item: any) => item.code?.toLowerCase().includes(search))
  }

  // Filtre par niveau académique
  if (filterParams.academic_level_id) {
    result = result.filter(
      (item: any) => String(item.academic_level_id) === String(filterParams.academic_level_id),
    )
  }

  // Tri par ID décroissant (plus récents en premier)
  result.sort((a: any, b: any) => Number(b.id) - Number(a.id))

  return result
})

// Pagination côté client
const paginatedItems = computed(() => {
  const start = (page.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredItems.value.slice(start, end)
})

// Total des items filtrés pour la pagination
const totalItems = computed(() => filteredItems.value.length)
</script>

<template>
  <LayoutSaisieOperation
    active-tag-name="sections"
    group="saisie"
    :breadcrumb="[
      { label: 'Élèves', href: '/apprenants' },
      { label: 'Saisie Préalable', href: '/apprenants/saisie-prealable' },
      { label: 'Sections', href: '/apprenants/saisie-prealable' },
    ]"
  >
    <BoxPanelWrapper>
      <div class="flex items-center gap-3 justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              v-model="filterParams.label"
              type="text"
              id="search"
              name="search"
              placeholder="Rechercher une section..."
              class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
            />
            <div
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
            >
              <span class="flex iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <FilterPopover>
            <div class="flex flex-col gap-3.5">
              <div class="flex flex-col space-y-1.5 flex-1">
                <label for="filter_label" class="text-sm font-medium">Nom de la section</label>
                <Input
                  id="filter_label"
                  type="text"
                  v-model="filterParams.label"
                  placeholder="Rechercher par nom..."
                  class="h-10"
                />
              </div>
              <ListAcademicalLevel
                :model-value="
                  filterParams.academic_level_id ? String(filterParams.academic_level_id) : ''
                "
                :items="levelList"
                @update:model-value="
                  (val) => (filterParams.academic_level_id = val ? Number(val) : undefined)
                "
              />
              <div class="flex flex-col space-y-1.5 flex-1">
                <label for="filter_code" class="text-sm font-medium">Code</label>
                <Input
                  id="filter_code"
                  type="text"
                  v-model="filterParams.code"
                  placeholder="Rechercher par code..."
                  class="h-10"
                />
              </div>
            </div>
          </FilterPopover>
          <FilterBadges
            :filters="filterParams"
            :reference-data="referenceData"
            :custom-labels="customLabels"
            @remove-filter="removeFilter"
          />
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
          <NewFilliere />
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader class="bg-primary text-white text-xs uppercase">
            <TableRow>
              <TableHead class="w-14">
                <Checkbox :checked="false" class="bg-white scale-70" />
              </TableHead>
              <TableHead>Nom de la section</TableHead>
              <TableHead>Code</TableHead>
              <TableHead>Operations</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="item in paginatedItems" :key="item.id">
              <TableCell class="w-14">
                <Checkbox :checked="false" class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{ item.name }}</TableCell>

              <TableCell>{{ item.code || '—' }}</TableCell>
              <TableCell class="px-4">
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
                          <AlertDialogTitle>Supprimer une section</AlertDialogTitle>
                          <AlertDialogDescription>
                            Vous êtes sur le point de supprimer la section '{{ item.name }}'.
                            Êtes-vous sûr de continuer?
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel :disabled="deleting && itemToDelete?.id === item.id"
                            >Annuler</AlertDialogCancel
                          >
                          <Button
                            :disabled="deleting && itemToDelete?.id === item.id"
                            @click="handleDelete(item)"
                          >
                            <span v-if="!deleting || itemToDelete?.id !== item.id">Supprimer</span>
                            <span v-else class="flex items-center gap-2">
                              <IconifySpinner class="text-white" />
                              <span>Suppression...</span>
                            </span>
                          </Button>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </div>
                </div>
              </TableCell>
            </TableRow>

            <!-- Message si aucune section -->
            <TableRow v-if="!filteredItems || filteredItems.length === 0">
              <TableCell :colspan="4" class="text-center py-8 text-gray-500">
                <div v-if="loading">Chargement des sections...</div>
                <div v-else-if="error">Erreur lors du chargement: {{ error }}</div>
                <div v-else>Aucune section trouvée</div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <TabPagination
        v-if="items.length"
        v-model="page"
        :perPage="perPage"
        :totalItems="totalItems"
        @update:perPage="(val) => (perPage = val)"
      />
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
