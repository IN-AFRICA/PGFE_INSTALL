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
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import SaisieRhLayout from '@/components/templates/rh/SaisieRhLayout.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'

// Presence widgets
import {
  PresenceToggle,
  PresenceStatusSelect,
  PresenceStatusBadge,
  InitPresenceDialog,
  type PresenceStatus,
} from '@/components/molecules/presence'
import { usePresence, type PresenceItem } from '@/composables/usePresence'

import { computed, ref, reactive, watch, onMounted, onUnmounted } from 'vue'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useGetApi } from '@/composables/useGetApi'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import { eventBus } from '@/utils/eventBus'
import type { PersonPresence } from '@/models/person_presence'

// Presence composable
const {
  loadingPersonnelId,
  recentlyUpdated,
  initializingPresence,
  getPresenceStatus,
  togglePresence,
  changeStatus,
  initializePresence,
} = usePresence()

// Helper: date du jour
const getTodayDate = () => {
  const today = new Date()
  return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
}

// Search and pagination
const { query } = useSearch((params: { search: string }) => {
  setAdditionalParams(params)
  fetchPresences({ ...params, ...queryParams.value })
}, 500)

// Filters
const filterParams = reactive({
  status: undefined as string | undefined,
  date: getTodayDate(),
})

const removeFilter = (key: string) => {
  if (key === 'status') filterParams.status = undefined
  if (key === 'date') filterParams.date = getTodayDate()
  fetchPresences({ ...queryParams.value })
}

const customLabels = {
  status: (value: string) =>
    ({
      present: 'Présent',
      absent: 'Absent Non Justifié',
      justified: 'Absent Justifié',
      sick: 'Malade',
    })[value] || value,
  date: (value: string) => new Date(value).toLocaleDateString('fr-FR'),
}

// API calls
const { data: fonctionsData, fetchData: fetchFonctions } = useGetApi(API_ROUTES.GET_FONCTIONS)
const {
  data: presencesData,
  fetchData: fetchPresences,
  loading: loadingData,
} = useGetApi<PersonPresence[]>(API_ROUTES.GET_PRESENCES)

const queryParams = computed(() => {
  const params: Record<string, string> = {}
  if (filterParams.date) params.date = filterParams.date
  if (filterParams.status) params.status = filterParams.status
  return params
})

// Pagination
const { page, perPageCount, total, setAdditionalParams } = usePagination(
  (params) => fetchPresences({ ...params }),
  1,
  15,
  { pageParam: 'page', perPageParam: 'limit' },
)

// Get fonction name
const getFonctionName = (fonctionId: number | null | undefined) => {
  if (!fonctionId || !fonctionsData.value) return 'N/A'
  const fonctions = Array.isArray(fonctionsData.value)
    ? fonctionsData.value
    : (fonctionsData.value as any)?.data || []
  const fonction = fonctions.find((f: any) => f.id == fonctionId)
  return fonction?.name || fonction?.title || fonction?.libelle || 'N/A'
}

// Init dialog state
const showInitDialog = ref(false)

const handleInitialize = async (date: string) => {
  const result = await initializePresence(date, () => {
    showInitDialog.value = false
  })
  if (result?.date) {
    filterParams.date = result.date
  }
  await fetchPresences({ ...queryParams.value, page: page.value, limit: perPageCount.value })
}

// Watch filters
watch(
  [() => filterParams.date, () => filterParams.status],
  async () => {
    page.value = 1
    if (filterParams.date) {
      await fetchPresences({ ...queryParams.value, page: page.value, limit: perPageCount.value })
    }
  },
  { immediate: false },
)

// Lifecycle
onMounted(async () => {
  await Promise.all([fetchFonctions()])
  await fetchPresences({ ...queryParams.value, page: page.value, limit: perPageCount.value })

  eventBus.on('presenceUpdated', () => {
    void fetchPresences({
      ...queryParams.value,
      page: page.value,
      limit: perPageCount.value,
      search: query.value,
    })
  })
})

onUnmounted(() => eventBus.off('presenceUpdated'))

const onPerPageUpdate = (val: number) => {
  page.value = 1
  perPageCount.value = val
}
</script>

<template>
  <SaisieRhLayout activeBread="Presence" active-tag-name="presence" group="saisie">
    <BoxPanelWrapper>
      <!-- Header -->
      <div class="flex items-center gap-3 justify-between">
        <div class="flex flex-1 items-center gap-2">
          <!-- Search -->
          <div class="relative w-full max-w-xs">
            <Input
              v-model="query"
              type="text"
              placeholder="Rechercher un personnel..."
              class="w-full max-w-xs ps-10 border border-gray-200/40 bg-white h-10 rounded-md"
            />
            <div
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
            >
              <span class="flex iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>

          <!-- Filter Popover -->
          <Popover>
            <PopoverTrigger as-child>
              <Button variant="ghost" size="sm" class="h-10 rounded-md border bg-white">
                <span class="hidden sm:flex">Filtre</span>
                <span class="iconify hugeicons--filter"></span>
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-80">
              <div class="grid gap-4">
                <h4 class="font-medium leading-none">Filtrage</h4>
                <div class="flex flex-col gap-3.5">
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light">Date</Label>
                    <Input type="date" class="h-10 bg-white" v-model="filterParams.date" />
                  </div>
                  <div class="space-y-1.5">
                    <Label class="text-foreground-muted font-light">Statut</Label>
                    <Select v-model="filterParams.status">
                      <SelectTrigger class="h-10 w-full">
                        <SelectValue placeholder="Sélectionner un statut" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="status in [
                              'present',
                              'absent',
                              'absent_justified',
                              'sick',
                            ] as PresenceStatus[]"
                            :key="status"
                            :value="status"
                          >
                            <PresenceStatusBadge :status="status" />
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
            :custom-labels="customLabels"
            @remove-filter="removeFilter"
          />

          <!-- Date Badge -->
          <Badge variant="outline" class="h-10 px-3 flex items-center gap-2 bg-white">
            <span class="iconify hugeicons--calendar-03 text-sm"></span>
            <span class="font-medium">{{
              new Date(filterParams.date).toLocaleDateString('fr-FR')
            }}</span>
          </Badge>
          <Button
            v-if="filterParams.date !== getTodayDate()"
            variant="outline"
            size="sm"
            class="h-10"
            @click="filterParams.date = getTodayDate()"
          >
            <span class="iconify hugeicons--calendar-check-in-02 mr-1"></span>
            Aujourd'hui
          </Button>
        </div>

        <!-- Actions -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="default"
              size="md"
              class="h-10 rounded-md"
              :disabled="initializingPresence"
            >
              <span v-if="initializingPresence" class="flex items-center gap-2">
                <IconifySpinner size="sm" />
                <span>Traitement...</span>
              </span>
              <span v-else class="flex items-center gap-2">
                <span class="iconify hugeicons--arrow-down-01"></span>
                <span>Actions</span>
              </span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuItem
              @click="showInitDialog = true"
              class="flex items-center cursor-pointer"
            >
              <span class="flex mr-1.5 iconify hugeicons--exchange-01"></span>
              Initialiser la fiche
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      <!-- Table -->
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]"><Checkbox class="bg-white scale-70" /></TableHead>
              <TableHead>Nom complet</TableHead>
              <TableHead>Fonction</TableHead>
              <TableHead>Présence</TableHead>
              <TableHead>Statut</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loadingData">
              <TableCell colspan="5" class="text-center py-8">
                <span class="flex justify-center items-center gap-2">
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
                  Chargement des présences...
                </span>
              </TableCell>
            </TableRow>
            <TableRow v-else-if="!presencesData || presencesData.length === 0">
              <TableCell colspan="5" class="text-center py-8 text-gray-500">
                Aucune donnée de présence trouvée pour la date sélectionnée
              </TableCell>
            </TableRow>
            <template v-else>
              <TableRow
                v-for="presence in presencesData"
                :key="presence.id"
                :class="{
                  'bg-primary-50 transition-colors duration-500': recentlyUpdated === presence.id,
                }"
              >
                <TableCell class="w-[40px]"><Checkbox class="bg-white scale-70" /></TableCell>
                <TableCell>{{ presence.personnel.name }}</TableCell>
                <TableCell>{{ getFonctionName(Number(presence.personnel.fonction_id)) }}</TableCell>
                <TableCell>
                  <PresenceToggle
                    :checked="!!presence.presence"
                    :loading="loadingPersonnelId === presence.id"
                    @change="(val) => togglePresence(presence, val, filterParams.date)"
                  />
                </TableCell>
                <TableCell>
                  <PresenceStatusSelect
                    :model-value="getPresenceStatus(presence)"
                    :loading="loadingPersonnelId === presence.id"
                    @update:model-value="(val) => changeStatus(presence, val, filterParams.date)"
                  />
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>
      </div>

      <TabPagination
        v-if="!loadingData"
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />

      <!-- Init Dialog -->
      <InitPresenceDialog
        :open="showInitDialog"
        :loading="initializingPresence"
        :default-date="filterParams.date"
        @update:open="showInitDialog = $event"
        @confirm="handleInitialize"
      />
    </BoxPanelWrapper>
  </SaisieRhLayout>
</template>
