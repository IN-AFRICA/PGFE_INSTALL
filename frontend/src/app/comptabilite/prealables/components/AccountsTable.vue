<script setup lang="ts">
import { computed, ref, reactive, watch } from 'vue'
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
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import NewPlanComptable from '@/components/modals/compta/NewPlanComptable.vue'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import ExportDropdown from '@/components/ExportDropdown.vue'
import { eventBus } from '@/utils/eventBus'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import type { Account } from '@/models/account'

interface Props {
  accounts: Account[]
  loading: boolean
  error: string | null
  deleting: boolean
  classNameById: Map<string, string>
  categoryNameById: Map<string, string>
  meta: any
  exportLoading: boolean
  onFetchData: (params: any) => void
  onDelete: (id: number | string) => void
  onExport: (format: 'pdf' | 'excel') => void
}

const props = defineProps<Props>()

// Filter parameters
const filterParams = reactive({
  class_comptability_id: undefined as number | undefined,
  category_comptability_id: undefined as number | undefined,
})

// Reference data for filter badges
const referenceData = computed(() => ({
  class_comptability_id: props.classes || [],
  category_comptability_id: props.categories || [],
}))

// Custom labels for filter badges
const customLabels = {
  class_comptability_id: (value: any, data: any[]) => {
    const item = data?.find((c: any) => c.id === value)
    return item ? item.name : value
  },
  category_comptability_id: (value: any, data: any[]) => {
    const item = data?.find((c: any) => c.id === value)
    return item ? item.name : value
  },
}

// Remove filter function
const removeFilter = (key: string) => {
  if (key === 'class_comptability_id') filterParams.class_comptability_id = undefined
  if (key === 'category_comptability_id') filterParams.category_comptability_id = undefined
}

// Search with debounce
const { query } = useSearch((params: { search: string }) => {
  props.onFetchData({ page: page.value, per_page: perPageCount.value, ...filterParams, ...params })
}, 500)

// Pagination
const { page, perPageCount, total } = usePagination(props.onFetchData, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

// Watch meta changes to update pagination
watch(
  () => props.meta,
  (m) => {
    if (!m) return
    if (typeof m.total === 'number') total.value = m.total
    if (typeof m.per_page === 'number' && perPageCount.value !== m.per_page) {
      perPageCount.value = m.per_page
    }
    if (typeof m.current_page === 'number' && page.value !== m.current_page) {
      page.value = m.current_page
    }
  },
)

// Watch filters
watch(
  [() => filterParams.class_comptability_id, () => filterParams.category_comptability_id],
  () => {
    const params: any = {
      page: page.value,
      per_page: perPageCount.value,
      search: query.value,
    }
    if (filterParams.class_comptability_id)
      params.class_comptability_id = filterParams.class_comptability_id
    if (filterParams.category_comptability_id)
      params.category_comptability_id = filterParams.category_comptability_id

    props.onFetchData(params)
  },
  { deep: true },
)

// Handle per-page update
function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}

// Export filters
const exportFilters = ref<Record<string, any>>({})

const syncExportFilters = () => {
  exportFilters.value = {
    ...(filterParams.class_comptability_id
      ? { class_comptability_id: filterParams.class_comptability_id }
      : {}),
    ...(filterParams.category_comptability_id
      ? { category_comptability_id: filterParams.category_comptability_id }
      : {}),
    ...(query.value ? { search: query.value } : {}),
  }
}
</script>

<template>
  <BoxPanelWrapper>
    <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between">
      <span class="text-gray-500 my-1.5 text-xl">Compte</span>
    </div>

    <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
      <div class="flex flex-1 items-center gap-2">
        <div class="relative w-full max-w-xs">
          <Input
            type="text"
            v-model="query"
            id="search"
            name="search"
            placeholder="Rechercher un compte..."
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
        <NewPlanComptable />
        <ExportDropdown
          :loading="exportLoading"
          :onExport="
            (format) => {
              syncExportFilters()
              onExport(format)
            }
          "
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
            <TableHead class="w-[60px]">N°</TableHead>
            <TableHead>Code</TableHead>
            <TableHead>Nom</TableHead>
            <TableHead>Classe</TableHead>
            <TableHead>Catégorie</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="6" class="text-center py-6"> Chargement des comptes... </TableCell>
          </TableRow>
          <TableRow v-else-if="error">
            <TableCell colspan="6" class="text-center py-6 text-red-500">
              {{ error }}
            </TableCell>
            
          </TableRow>
          <TableRow v-else-if="accounts.length === 0">
            <TableCell colspan="6" class="text-center py-6"> Aucun compte trouvé </TableCell>
          </TableRow>
          <TableRow v-else v-for="(item, index) in accounts" :key="item.id" class="group">
            <TableCell class="w-[40px]">
              <Checkbox class="bg-white scale-70" />
            </TableCell>
            <TableCell>{{ (page - 1) * perPageCount + index + 1 }}</TableCell>
            <TableCell class="font-semibold">{{ item.code }}</TableCell>
            <TableCell>{{ item.name }}</TableCell>
            <TableCell>
              {{
                classNameById.get(String(item.class_comptability_id)) || item.class_comptability_id
              }}
            </TableCell>
            <TableCell>
              {{
                categoryNameById.get(String(item.category_comptability_id)) ||
                item.category_comptability_id ||
                '-'
              }}
            </TableCell>
            <TableCell>
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" size="sm">
                    <span class="iconify hugeicons--more-vertical text-lg"></span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem @click="eventBus.emit('editAccountNumber', item.id)">
                    <span class="iconify hugeicons--edit-02 mr-2"></span>
                    Modifier
                  </DropdownMenuItem>
                  <AlertMessage
                    action="danger"
                    title="Supprimer un compte"
                    :message="`Vous êtes sur le point de supprimer le compte '${item.name}'. Êtes-vous sûr de continuer?`"
                  >
                    <template #trigger>
                      <DropdownMenuItem @select.prevent>
                        <span class="iconify hugeicons--delete-02 mr-2"></span>
                        Supprimer
                      </DropdownMenuItem>
                    </template>
                    <template #confirm-action-button>
                      <Button
                        variant="destructive"
                        size="sm"
                        class="h-10 px-4"
                        @click.stop="onDelete(item.id)"
                        :disabled="deleting"
                      >
                        Oui, Supprimer
                      </Button>
                    </template>
                  </AlertMessage>
                </DropdownMenuContent>
              </DropdownMenu>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <TabPagination
      v-if="!loading && !error"
      v-model="page"
      :perPage="perPageCount"
      :totalItems="total"
      @update:perPage="onPerPageUpdate"
    />
  </BoxPanelWrapper>
</template>
