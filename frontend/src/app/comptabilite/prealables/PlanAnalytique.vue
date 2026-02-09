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

import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { computed, onMounted, onBeforeUnmount } from 'vue'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import NewPlanAnalytique from '@/components/modals/compta/NewPlanAnalytique.vue'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'

// Chargement des plans analytiques depuis l'API
const { data, error, loading, fetchData } = useGetApi<any[]>(API_ROUTES.GET_ANALYTICS_PLAN)

function normalizeList(raw: any): any[] {
  if (!raw) return []
  if (Array.isArray(raw)) return raw
  if (raw?.data && Array.isArray(raw.data)) return raw.data
  if (raw?.items && Array.isArray(raw.items)) return raw.items
  if (typeof raw === 'object')
    return Object.values(raw).filter((v: any) => v && typeof v === 'object')
  return []
}

const analytics = computed(() =>
  normalizeList(data.value).map((a: any) => ({
    id: a.id,
    code: a.code,
    name: a.name ?? a.label ?? `Plan #${a.id}`,
    created_at: a.created_at ?? null,
  })),
)

// Search functionality
const { query } = useSearch((params: { search: string }) => {
  setAdditionalParams({ search: params.search })
  fetchData({ page: page.value, per_page: perPageCount.value, ...params })
}, 500)

// Pagination functionality
const { page, perPageCount, total, setAdditionalParams } = usePagination(fetchData, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

// Handle per page update
function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}

// Suppression
const { deleting, errorDelete: deleteError, deleteItem } = useDeleteApi()
async function handleDelete(id: number | string, name: string) {
  try {
    const url = (API_ROUTES.DELETE_ANALYTICS_PLAN as any)(id)
    await deleteItem(url)
    if (deleteError.value) {
      showCustomToast({ message: deleteError.value as unknown as string, type: 'error' })
      return
    }
    showCustomToast({ message: 'Plan analytique supprimé avec succès.', type: 'success' })
    // Reload with current params
    await fetchData({
      page: page.value,
      per_page: perPageCount.value,
      search: query.value,
    })
  } catch (e) {
    showCustomToast({ message: 'Erreur lors de la suppression du plan analytique.', type: 'error' })
  }
}

onMounted(async () => {
  await fetchData({ page: page.value, per_page: perPageCount.value })
  eventBus.on('analyticsPlanUpdated', () => {
    fetchData({ page: page.value, per_page: perPageCount.value, search: query.value })
  })
})

onBeforeUnmount(() => {
  eventBus.off('analyticsPlanUpdated')
})
</script>

<template>
  <ComptaLayout activeBread="Plan analytique" active-tag-name="plan-analytique" group="saisie">
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            v-model="query"
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher un centre analytique..."
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <NewPlanAnalytique />
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead>Code</TableHead>
              <TableHead>Nom</TableHead>
              <TableHead> Opérations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="4" class="text-center py-6"
                >Chargement des plans analytiques...</TableCell
              >
            </TableRow>
            <TableRow v-else-if="error">
              <TableCell colspan="4" class="text-center py-6 text-red-500">{{ error }}</TableCell>
            </TableRow>
            <TableRow v-else-if="analytics.length === 0">
              <TableCell colspan="4" class="text-center py-6"
                >Aucun plan analytique trouvé</TableCell
              >
            </TableRow>
            <TableRow v-else v-for="item in analytics" :key="item.id">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell class="font-semibold">{{ item.code }}</TableCell>
              <TableCell>{{ item.name }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <Button variant="outline" size="icon" class="size-8">
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>
                  <AlertMessage
                    action="danger"
                    title="Supprimer un plan analytique"
                    :message="`Vous êtes sur le point de supprimer le plan '${item.name}'. Êtes-vous sûr de continuer ?`"
                  >
                    <template #trigger>
                      <Button variant="destructive" size="icon" class="size-8">
                        <span class="iconify hugeicons--delete-02"></span>
                      </Button>
                    </template>
                    <template #confirm-action-button>
                      <Button
                        variant="destructive"
                        size="sm"
                        aria-label="Supprimer"
                        @click="handleDelete(item.id, item.name)"
                        :disabled="deleting"
                      >
                        {{ deleting ? 'Suppression...' : 'Oui, supprimer' }}
                      </Button>
                    </template>
                  </AlertMessage>
                </div>
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
  </ComptaLayout>
</template>
