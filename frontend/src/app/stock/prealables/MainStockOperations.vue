<script setup lang="ts">
import { computed, ref, onMounted, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import { tagStockNavOperations } from '@/components/templates/stock/tags-links.ts'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import ExportDropdown from '@/components/ExportDropdown.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import FilterPopover from '@/components/atoms/FilterPopover.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'

const router = useRouter()

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Stock', href: '/stock' },
    { label: 'Opérations', isActive: true },
  ],
}

const activeTagName = 'operations'

// Search & Filter state
const query = ref('')
const page = ref(1)
const perPageCount = ref(15)
const exportLoading = ref(false)

// Paramètres de filtrage
const filterParams = reactive({
  type: 'all' as string, // 'all', 'entrée', 'sortie'
  date_start: '',
  date_end: '',
  quantite: '' as string | number,
})

const { data: rawData, loading, fetchData, meta } = useGetApi(API_ROUTES.GET_STOCK_OPERATIONS)

const data = computed(() => {
  if (!rawData.value) return []
  if (Array.isArray(rawData.value)) return rawData.value
  return (rawData.value as any).data || []
})

// Filtered data based on search, type, and date filters
const filteredData = computed(() => {
  let result = data.value

  // Filter by search query (Article, Opérateur, Référence, Type)
  if (query.value) {
    const s = query.value.toLowerCase()
    result = result.filter(
      (item: any) =>
        item.article?.toLowerCase().includes(s) ||
        item.operateur?.toLowerCase().includes(s) ||
        item.reference?.toLowerCase().includes(s) ||
        item.type?.toLowerCase().includes(s) ||
        item.quantite?.toString().includes(s),
    )
  }

  // Filter by type
  if (filterParams.type && filterParams.type !== 'all') {
    result = result.filter(
      (item: any) => item.type?.toLowerCase() === filterParams.type.toLowerCase(),
    )
  }

  // Filter by date range
  if (filterParams.date_start) {
    const startDate = new Date(filterParams.date_start)
    result = result.filter((item: any) => {
      const itemDate = new Date(item.date)
      return itemDate >= startDate
    })
  }

  if (filterParams.date_end) {
    const endDate = new Date(filterParams.date_end)
    endDate.setHours(23, 59, 59, 999) // End of day
    result = result.filter((item: any) => {
      const itemDate = new Date(item.date)
      return itemDate <= endDate
    })
  }

  // Filter by quantity
  if (filterParams.quantite) {
    result = result.filter((item: any) => item.quantite == filterParams.quantite)
  }

  return result
})

const total = computed(() => filteredData.value.length || (meta && meta.value?.total) || 0)

// Paginated data
const paginatedData = computed(() => {
  const start = (page.value - 1) * perPageCount.value
  const end = start + perPageCount.value
  return filteredData.value.slice(start, end)
})

// Labels personnalisés pour les badges de filtre
const customLabels: Record<string, (value: any) => string> = {
  type: (value) => `Type: ${value}`,
  date_start: (value) => `Du: ${formatDate(value)}`,
  date_end: (value) => `Au: ${formatDate(value)}`,
  quantite: (value) => `Qté: ${value}`,
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'type') filterParams.type = 'all'
  if (key === 'date_start') filterParams.date_start = ''
  if (key === 'date_end') filterParams.date_end = ''
  if (key === 'quantite') filterParams.quantite = ''
}

// Filtres actifs pour les badges (exclut 'all' pour le type)
const activeFilters = computed(() => {
  const filters: Record<string, any> = {}
  if (filterParams.type && filterParams.type !== 'all') {
    filters.type = filterParams.type
  }
  if (filterParams.date_start) {
    filters.date_start = filterParams.date_start
  }
  if (filterParams.date_end) {
    filters.date_end = filterParams.date_end
  }
  if (filterParams.quantite) {
    filters.quantite = filterParams.quantite
  }
  return filters
})

// Check if any filter is active
const hasActiveFilters = computed(() => {
  return (
    filterParams.type !== 'all' ||
    filterParams.date_start ||
    filterParams.date_end ||
    filterParams.quantite
  )
})

// Watch for filter changes to reset page
watch(
  [
    query,
    () => filterParams.type,
    () => filterParams.date_start,
    () => filterParams.date_end,
    () => filterParams.quantite,
  ],
  () => {
    page.value = 1
  },
)

onMounted(() => {
  fetchData({ page: 1, limit: 100 }) // Fetch more to allow client-side filtering
})

const onPerPageUpdate = (val: number) => {
  page.value = 1
  perPageCount.value = val
}

const handleExport = (format: string) => {
  showCustomToast({
    message: `Export ${format.toUpperCase()} lancé...`,
    type: 'success',
  })
}

// Helper for type styling
const getTypeClass = (type: string) => {
  return type && type.toLowerCase() === 'entrée'
    ? 'bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium'
    : 'bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs font-medium'
}

// Format date for display
const formatDate = (dateStr: string) => {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/stock/operations" module-name="stock">
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Opérations"
        description="Historique des entrées et sorties de stock"
        :tags="tagStockNavOperations"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper class="flex-1 flex flex-col min-h-0">
        <div class="flex items-center gap-3 justify-between">
          <div class="flex flex-1 items-center gap-2">
            <!-- Search input -->
            <div class="relative w-full max-w-xs">
              <Input
                type="text"
                v-model="query"
                placeholder="Rechercher..."
                class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div
                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
              >
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>

            <!-- Filter Popover -->
            <FilterPopover>
              <div class="flex flex-col gap-3.5">
                <div class="flex flex-col space-y-1.5 flex-1">
                  <label for="filter_type" class="text-sm font-medium">Type d'opération</label>
                  <Select v-model="filterParams.type">
                    <SelectTrigger id="filter_type" class="w-full h-10">
                      <SelectValue placeholder="Tous les types" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectGroup>
                        <SelectItem value="all">Tous les types</SelectItem>
                        <SelectItem value="entrée">Entrées</SelectItem>
                        <SelectItem value="sortie">Sorties</SelectItem>
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                </div>

                <div class="flex flex-col space-y-1.5 flex-1">
                  <label for="filter_date_start" class="text-sm font-medium">Date de début</label>
                  <Input
                    id="filter_date_start"
                    type="date"
                    v-model="filterParams.date_start"
                    class="h-10"
                  />
                </div>

                <div class="flex flex-col space-y-1.5 flex-1">
                  <label for="filter_date_end" class="text-sm font-medium">Date de fin</label>
                  <Input
                    id="filter_date_end"
                    type="date"
                    v-model="filterParams.date_end"
                    class="h-10"
                  />
                </div>

                <div class="flex flex-col space-y-1.5 flex-1">
                  <label for="filter_quantite" class="text-sm font-medium">Quantité</label>
                  <Input
                    id="filter_quantite"
                    type="number"
                    min="1"
                    placeholder="Ex: 10"
                    v-model="filterParams.quantite"
                    class="h-10"
                  />
                </div>
              </div>
            </FilterPopover>

            <!-- Filter Badges -->
            <FilterBadges
              :filters="activeFilters"
              :custom-labels="customLabels"
              @remove-filter="removeFilter"
            />
          </div>

          <div class="flex flex-wrap items-center gap-2.5">
            <ExportDropdown :loading="exportLoading" @export="handleExport" />
          </div>
        </div>



        <!-- Loading state -->
        <div
          v-if="loading"
          class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500"
        >
          <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
          <span>Chargement...</span>
        </div>

        <!-- Table -->
        <div
          v-else-if="paginatedData && paginatedData.length"
          class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
        >
          <Table class="rounded-md bg-white">
            <TableHeader>
              <TableRow>
                <TableHead class="w-12">N°</TableHead>
                <TableHead>Date</TableHead>
                <TableHead>Référence</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Item</TableHead>
                <TableHead class="text-center">Qté</TableHead>
                <TableHead>Opérateur</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in paginatedData" :key="item.id">
                <TableCell class="text-gray-400">{{ (page - 1) * perPageCount + Number(index) + 1 }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ formatDate(item.date) }}</TableCell>
                <TableCell class="font-mono text-xs">{{ item.reference }}</TableCell>
                <TableCell>
                  <span :class="getTypeClass(item.type)">{{ item.type }}</span>
                </TableCell>
                <TableCell class="font-medium">{{ item.article }}</TableCell>
                <TableCell class="text-center font-semibold">{{ item.quantite }}</TableCell>
                <TableCell>{{ item.operateur || '—' }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Empty state -->
        <div
          v-else
          class="flex flex-col items-center justify-center h-full py-10 bg-white rounded-md text-gray-500"
        >
          <span class="iconify hugeicons--file-failed text-4xl mb-2"></span>
          <span>{{
            query || hasActiveFilters
              ? 'Aucune opération trouvée avec ces critères.'
              : 'Aucune opération trouvée.'
          }}</span>
        </div>

        <!-- Pagination -->
        <TabPagination
          v-if="filteredData && filteredData.length"
          v-model="page"
          :perPage="perPageCount"
          :totalItems="total"
          @update:perPage="onPerPageUpdate"
        />
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>
