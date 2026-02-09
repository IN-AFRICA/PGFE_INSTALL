<script setup lang="ts">
import { computed, watch } from 'vue'
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
import NewSousCompte from '@/components/modals/compta/NewSousCompte.vue'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import ExportDropdown from '@/components/ExportDropdown.vue'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import type { SubAccount } from '@/models/sub_account'

interface Props {
  subAccounts: SubAccount[]
  loading: boolean
  error: string | null
  deleting: boolean
  exportLoading: boolean
  meta: any
  onFetchData: (params: any) => void
  onDelete: (id: number | string) => void
  onExport: (format: 'pdf' | 'excel') => void
}

const props = defineProps<Props>()

// Search with debounce
const { query } = useSearch((params: { search: string }) => {
  props.onFetchData({ page: page.value, per_page: perPageCount.value, ...params })
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

// Handle per-page update
function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}

function formatDate(dateStr: string | null): string {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return '-'

  return d.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}
</script>

<template>
  <BoxPanelWrapper>
    <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between">
      <span class="text-gray-500 my-1.5 text-xl">Sous-compte</span>
    </div>

    <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
      <div class="relative flex-1">
        <Input
          v-model="query"
          type="text"
          placeholder="Rechercher un sous-compte..."
          class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
        />
        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
          <span class="flex iconify hugeicons--search-01 text-sm"></span>
        </div>
      </div>

      <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
        <NewSousCompte />
        <ExportDropdown :loading="exportLoading" :onExport="onExport" />
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
            <TableHead>Compte parent</TableHead>
            <TableHead>Créé le</TableHead>
            <TableHead>Opérations</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="6" class="text-center py-6">
              Chargement des sous-comptes...
            </TableCell>
          </TableRow>
          <TableRow v-else-if="error">
            <TableCell colspan="6" class="text-center py-6 text-red-500">
              {{ error }}
            </TableCell>
          </TableRow>
          <TableRow v-else-if="subAccounts.length === 0">
            <TableCell colspan="6" class="text-center py-6"> Aucun sous-compte trouvé </TableCell>
          </TableRow>
          <TableRow v-else v-for="(item, index) in subAccounts" :key="item.id" class="group">
            <TableCell class="w-[40px]">
              <Checkbox class="bg-white scale-70" />
            </TableCell>
            <TableCell class="font-semibold">{{ item.code }}</TableCell>
            <TableCell>{{ item.name }}</TableCell>
            <TableCell>{{ item.account_plan.name }}</TableCell>
            <TableCell>{{ formatDate(item.created_at) }}</TableCell>
            <TableCell>
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" size="sm">
                    <span class="iconify hugeicons--more-vertical text-lg"></span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem>
                    <span class="iconify hugeicons--edit-02 mr-2"></span>
                    Modifier
                  </DropdownMenuItem>
                  <AlertMessage
                    action="danger"
                    title="Supprimer un sous-compte"
                    :message="`Vous êtes sur le point de supprimer le sous-compte '${item.name}'. Êtes-vous sûr de continuer?`"
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
