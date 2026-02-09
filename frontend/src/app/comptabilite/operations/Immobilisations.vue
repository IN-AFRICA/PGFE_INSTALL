<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
// No direct usage of router/route in this component; RouterLink is used in template
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
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { eventBus } from '@/utils/eventBus'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'

// Interface pour les immobilisations
interface Immobilisation {
  id?: number | string
  name: string
  code: string
  school_id?: number
  user_id?: number
}

// Variables
// const router = useRouter(); // Not needed - RouterLink used in template
const searchQuery = ref('')
const page = ref(1)
const perPage = ref(15)
const selectedItems = ref<Array<number | string>>([])

// Note: route/router usage not needed here; RouterLink provides navigation in template

const {
  data: immobilisationsData,
  loading,
  error,
  fetchData,
  meta,
  refresh,
} = useGetApi<Immobilisation[]>(API_ROUTES.GET_IMMOBILISATION)
const { deleteItem } = useDeleteApi()

// Filtrer
const filteredImmobilisations = computed(() => {
  const items = immobilisationsData.value ?? []
  if (!searchQuery.value) return items
  const q = searchQuery.value.toLowerCase()
  return items.filter(
    (item) =>
      (item.name ?? '').toLowerCase().includes(q) || (item.code ?? '').toLowerCase().includes(q),
  )
})

// Pagination
// Server returns paginated result, use server data; if client filters, paginate sliced client-side
const paginatedImmobilisations = computed(() => {
  const items = filteredImmobilisations.value
  if (!searchQuery.value) return items
  const start = (page.value - 1) * perPage.value
  const end = start + perPage.value
  return items.slice(start, end)
})

const total = computed(() => (meta?.value?.total ?? filteredImmobilisations.value.length) as number)

// Load initial data
onMounted(async () => {
  await fetchData({ page: page.value, per_page: perPage.value })
  eventBus.on(
    'immobilisationUpdated',
    async () => await fetchData({ page: page.value, per_page: perPage.value }),
  )
})

onBeforeUnmount(() => {
  eventBus.off('immobilisationUpdated')
})

// Watch page/perPage to fetch
watch([page, perPage], ([p, per]) => {
  fetchData({ page: p ?? 1, per_page: per ?? 15 })
})

// CRUD
const deleteImmobilisation = async (id: number | string, name: string) => {
  if (!confirm(`Supprimer "${name}" ?`)) return
  const endpoint = API_ROUTES.DELETE_IMMOBILISATION_ENTRY(id)
  const res = await deleteItem(endpoint)
  if (res) {
    showCustomToast({ message: 'Immobilisation supprimée avec succès', type: 'success' })
    // Refresh the list
    await refresh()
  } else {
    showCustomToast({ message: 'Erreur lors de la suppression', type: 'error' })
  }
}

const deleteSelectedItems = async () => {
  if (selectedItems.value.length === 0) return
  const count = selectedItems.value.length
  if (!confirm(`Supprimer ${count} immobilisation(s) sélectionnée(s) ?`)) return
  // Delete one by one
  for (const id of selectedItems.value) {
    const endpoint = API_ROUTES.DELETE_IMMOBILISATION_ENTRY(id)
    await deleteItem(endpoint)
  }
  selectedItems.value = []
  await refresh()
  showCustomToast({ message: `${count} immobilisation(s) supprimée(s)`, type: 'success' })
}

const toggleSelectAll = () => {
  if (selectedItems.value.length === paginatedImmobilisations.value.length) {
    selectedItems.value = []
  } else {
    selectedItems.value = paginatedImmobilisations.value.map((item) => item.id ?? item.code)
  }
}

// No date/amount/etat helpers are needed for name/code display
</script>

<template>
  <ComptaLayout activeBread="immobilisations" active-tag-name="immobilisations" group="operations">
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            v-model="searchQuery"
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher par nom ou code ..."
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <Button
            v-if="selectedItems.length > 0"
            variant="destructive"
            size="md"
            @click="deleteSelectedItems"
          >
            <span class="iconify hugeicons--delete-02 mr-1"></span>
            Supprimer ({{ selectedItems.length }})
          </Button>

          <RouterLink to="/comptabilite/saisie-operations/nouvelle-immobilisation">
            <Button size="md">
              <span class="iconify hugeicons--add-01 mr-2"></span>
              Nouvelle immobilisation
            </Button>
          </RouterLink>
        </div>
      </div>

      <div v-if="loading" class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8">
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--loading-03 animate-spin text-4xl text-blue-500"></span>
          <p class="text-sm text-foreground-muted">Chargement des immobilisations...</p>
        </div>
      </div>

      <!-- État d'erreur -->
      <div v-else-if="error" class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8">
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--alert-circle text-4xl text-red-500"></span>
          <p class="text-sm text-foreground-muted">{{ error }}</p>
          <Button @click="() => fetchData()" size="sm" variant="outline">
            <span class="iconify hugeicons--refresh mr-1"></span>
            Réessayer
          </Button>
        </div>
      </div>

      <!-- Liste vide -->
      <div
        v-else-if="!paginatedImmobilisations || paginatedImmobilisations.length === 0"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
      >
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--notebook text-4xl text-foreground-muted/50"></span>
          <p class="text-sm text-foreground-muted">
            {{
              searchQuery
                ? 'Aucune immobilisation trouvée pour cette recherche'
                : 'Aucune immobilisation enregistrée'
            }}
          </p>
        </div>
      </div>

      <div v-else class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox
                  :checked="
                    selectedItems.length === paginatedImmobilisations.length &&
                    paginatedImmobilisations.length > 0
                  "
                  @update:checked="toggleSelectAll"
                />
              </TableHead>
              <TableHead>Nom</TableHead>
              <TableHead>Code</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in paginatedImmobilisations" :key="item.id ?? item.code">
              <TableCell>
                <Checkbox
                  :checked="selectedItems.includes(item.id ?? item.code)"
                  @update:checked="
                    (checked: boolean) => {
                      const idOrCode = item.id ?? item.code
                      if (checked) selectedItems.push(idOrCode)
                      else selectedItems = selectedItems.filter((id) => id !== idOrCode)
                    }
                  "
                />
              </TableCell>
              <TableCell class="font-medium">{{ item.name }}</TableCell>
              <TableCell>{{ item.code }}</TableCell>
              <TableCell class="text-right">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon">
                      <span class="iconify hugeicons--more-vertical"></span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <RouterLink
                      :to="`/comptabilite/saisie-operations/upate-immobilisation?id=${item.id ?? item.code}`"
                    >
                      <DropdownMenuItem>
                        <span class="iconify hugeicons--pencil-edit-01 mr-2"></span>
                        Modifier
                      </DropdownMenuItem>
                    </RouterLink>
                    <DropdownMenuItem
                      class="text-red-600"
                      @click="deleteImmobilisation(item.id ?? item.code, item.name)"
                    >
                      <span class="iconify hugeicons--delete-02 mr-2"></span>
                      Supprimer
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <TabPagination
        v-if="!loading && !error && paginatedImmobilisations.length > 0"
        v-model="page"
        v-model:perPage="perPage"
        :totalItems="total"
        class="mt-4"
      />
    </BoxPanelWrapper>
  </ComptaLayout>
</template>
