<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
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
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  DialogClose,
} from '@/components/ui/dialog'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { eventBus } from '@/utils/eventBus'

const router = useRouter()

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Stock', href: '/stock' },
    { label: 'Opérations', href: '/stock/operations' },
    { label: 'Entrées & Sorties', isActive: true },
  ],
}

const activeTagName = 'entrees-sorties'

// Entrées (entries)
const queryEntrees = ref('')
const { data: rawEntriesData, loading: loadingEntrees, fetchData: fetchEntrees } = useGetApi(API_ROUTES.GET_STOCK_ENTRIES)
const { deleting: deletingEntry, errorDelete: errorDeleteEntry, deleteItem: deleteEntry } = useDeleteApi()
const deletingEntryId = ref<number | null>(null)

// Sorties (exits)
const querySorties = ref('')
const { data: rawExitsData, loading: loadingSorties, fetchData: fetchSorties } = useGetApi(API_ROUTES.GET_STOCK_EXITS)
const { deleting: deletingExit, errorDelete: errorDeleteExit, deleteItem: deleteExit } = useDeleteApi()
const deletingExitId = ref<number | null>(null)

// Articles for name lookup
const { data: articlesRaw, fetchData: fetchArticles } = useGetApi(API_ROUTES.GET_STOCK_ARTICLES)

const articles = computed(() => {
  if (!articlesRaw.value) return []
  if (Array.isArray(articlesRaw.value)) return articlesRaw.value
  return (articlesRaw.value as any).data || []
})

const getArticleName = (articleId: string | number) => {
  const article = articles.value.find((a: any) => a.id?.toString() === articleId?.toString())
  return article?.name || '—'
}

// Computed data for entries
const entries = computed(() => {
  if (!rawEntriesData.value) return []
  if (Array.isArray(rawEntriesData.value)) return rawEntriesData.value
  return (rawEntriesData.value as any).data || []
})

const filteredEntries = computed(() => {
  if (!queryEntrees.value) return entries.value
  const s = queryEntrees.value.toLowerCase()
  return entries.value.filter(
    (item: any) =>
      item.note?.toLowerCase().includes(s) ||
      getArticleName(item.article_id)?.toLowerCase().includes(s),
  )
})

// Computed data for exits
const exits = computed(() => {
  if (!rawExitsData.value) return []
  if (Array.isArray(rawExitsData.value)) return rawExitsData.value
  return (rawExitsData.value as any).data || []
})

const filteredExits = computed(() => {
  if (!querySorties.value) return exits.value
  const s = querySorties.value.toLowerCase()
  return exits.value.filter(
    (item: any) =>
      item.reason?.toLowerCase().includes(s) ||
      getArticleName(item.article_id)?.toLowerCase().includes(s),
  )
})

// Event handlers
const handleAddEntree = () => {
  router.push('/stock/operations/entrees/nouveau')
}

const handleAddSortie = () => {
  router.push('/stock/operations/sorties/nouveau')
}

const handleEditEntry = (item: any) => {
  router.push({
    path: '/stock/operations/entrees/nouveau',
    query: { id: item.id },
    state: { data: JSON.stringify(item) },
  })
}

const handleEditExit = (item: any) => {
  router.push({
    path: '/stock/operations/sorties/nouveau',
    query: { id: item.id },
    state: { data: JSON.stringify(item) },
  })
}

const handleDeleteEntry = async (id: number) => {
  deletingEntryId.value = id
  await deleteEntry(API_ROUTES.DELETE_STOCK_ENTRY(id))
  deletingEntryId.value = null

  if (errorDeleteEntry.value) {
    showCustomToast({ message: errorDeleteEntry.value, type: 'error' })
  } else {
    showCustomToast({ message: 'Entrée supprimée avec succès', type: 'success' })
    fetchEntrees()
  }
}

const handleDeleteExit = async (id: number) => {
  deletingExitId.value = id
  await deleteExit(API_ROUTES.DELETE_STOCK_EXIT(id))
  deletingExitId.value = null

  if (errorDeleteExit.value) {
    showCustomToast({ message: errorDeleteExit.value, type: 'error' })
  } else {
    showCustomToast({ message: 'Sortie supprimée avec succès', type: 'success' })
    fetchSorties()
  }
}

// Format date helper
const formatDate = (dateString?: string) => {
  if (!dateString) return '—'
  return new Date(dateString).toLocaleDateString('fr-FR')
}

onMounted(() => {
  fetchEntrees()
  fetchSorties()
  fetchArticles()
})

// Listen for updates from forms
eventBus.on('stockEntryUpdated', () => fetchEntrees())
eventBus.on('stockExitUpdated', () => fetchSorties())
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/stock/operations" module-name="stock">
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Entrées & Sorties de Stock"
        description="Gérez les mouvements de stock"
        :tags="tagStockNavOperations"
        :active-tag-name="activeTagName"
      />

      <div class="grid lg:grid-cols-2 gap-7 flex-1">
        <!-- Section Entrées -->
        <BoxPanelWrapper>
          <span class="text-gray-500 my-1.5 text-xl font-medium">Entrées de Stock</span>
          <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between mb-4 gap-3">
            <div class="relative w-full max-w-xs">
              <Input
                type="text"
                v-model="queryEntrees"
                placeholder="Rechercher une entrée..."
                class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>
            <Button size="md" class="rounded-md" @click="handleAddEntree">
              <span class="flex iconify hugeicons--plus-sign mr-1"></span>
              Nouvelle Entrée
            </Button>
          </div>

          <!-- Loading state -->
          <div v-if="loadingEntrees" class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
            <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
            <span>Chargement...</span>
          </div>

          <!-- Table -->
          <div v-else-if="filteredEntries.length" class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
            <Table class="rounded-md bg-white">
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Item</TableHead>
                  <TableHead>Qté</TableHead>
                  <TableHead>Note</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="item in filteredEntries" :key="item.id">
                  <TableCell>{{ formatDate(item.entry_date) }}</TableCell>
                  <TableCell>{{ getArticleName(item.article_id) }}</TableCell>
                  <TableCell>{{ item.quantity }}</TableCell>
                  <TableCell>{{ item.note || '—' }}</TableCell>
                  <TableCell>
                    <div class="flex items-center justify-end gap-2">
                      <Button
                        size="icon"
                        variant="ghost"
                        class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                        @click="handleEditEntry(item)"
                      >
                        <span class="iconify hugeicons--edit-01"></span>
                      </Button>
                      <Dialog>
                        <DialogTrigger as-child>
                          <Button
                            size="icon"
                            variant="ghost"
                            class="size-8 text-gray-500 hover:text-red-600 hover:bg-red-50"
                          >
                            <span class="iconify hugeicons--delete-02"></span>
                          </Button>
                        </DialogTrigger>
                        <DialogContent>
                          <DialogHeader>
                            <DialogTitle>Supprimer cette entrée ?</DialogTitle>
                            <DialogDescription>
                              Action irréversible. Voulez-vous vraiment supprimer cette entrée ?
                            </DialogDescription>
                          </DialogHeader>
                          <DialogFooter>
                            <DialogClose as-child>
                              <Button variant="outline">Annuler</Button>
                            </DialogClose>
                            <Button
                              variant="destructive"
                              :disabled="deletingEntryId === item.id"
                              @click="handleDeleteEntry(item.id)"
                            >
                              <span v-if="deletingEntryId === item.id" class="flex items-center gap-2">
                                <IconifySpinner size="sm" /><span>Suppression...</span>
                              </span>
                              <span v-else>Confirmer</span>
                            </Button>
                          </DialogFooter>
                        </DialogContent>
                      </Dialog>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Empty state -->
          <div v-else class="flex flex-col items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
            <span class="iconify hugeicons--package text-4xl mb-2"></span>
            <span>{{ queryEntrees ? 'Aucune entrée trouvée' : 'Aucune entrée enregistrée' }}</span>
          </div>
        </BoxPanelWrapper>

        <!-- Section Sorties -->
        <BoxPanelWrapper>
          <span class="text-gray-500 my-1.5 text-xl font-medium">Sorties de Stock</span>
          <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between mb-4 gap-3">
            <div class="relative w-full max-w-xs">
              <Input
                type="text"
                v-model="querySorties"
                placeholder="Rechercher une sortie..."
                class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>
            <Button size="md" class="rounded-md" @click="handleAddSortie">
              <span class="flex iconify hugeicons--plus-sign mr-1"></span>
              Nouvelle Sortie
            </Button>
          </div>

          <!-- Loading state -->
          <div v-if="loadingSorties" class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
            <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
            <span>Chargement...</span>
          </div>

          <!-- Table -->
          <div v-else-if="filteredExits.length" class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
            <Table class="rounded-md bg-white">
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Item</TableHead>
                  <TableHead>Qté</TableHead>
                  <TableHead>Motif</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="item in filteredExits" :key="item.id">
                  <TableCell>{{ formatDate(item.exit_date) }}</TableCell>
                  <TableCell>{{ getArticleName(item.article_id) }}</TableCell>
                  <TableCell>{{ item.quantity }}</TableCell>
                  <TableCell>{{ item.reason || '—' }}</TableCell>
                  <TableCell>
                    <div class="flex items-center justify-end gap-2">
                      <Button
                        size="icon"
                        variant="ghost"
                        class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                        @click="handleEditExit(item)"
                      >
                        <span class="iconify hugeicons--edit-01"></span>
                      </Button>
                      <Dialog>
                        <DialogTrigger as-child>
                          <Button
                            size="icon"
                            variant="ghost"
                            class="size-8 text-gray-500 hover:text-red-600 hover:bg-red-50"
                          >
                            <span class="iconify hugeicons--delete-02"></span>
                          </Button>
                        </DialogTrigger>
                        <DialogContent>
                          <DialogHeader>
                            <DialogTitle>Supprimer cette sortie ?</DialogTitle>
                            <DialogDescription>
                              Action irréversible. Voulez-vous vraiment supprimer cette sortie ?
                            </DialogDescription>
                          </DialogHeader>
                          <DialogFooter>
                            <DialogClose as-child>
                              <Button variant="outline">Annuler</Button>
                            </DialogClose>
                            <Button
                              variant="destructive"
                              :disabled="deletingExitId === item.id"
                              @click="handleDeleteExit(item.id)"
                            >
                              <span v-if="deletingExitId === item.id" class="flex items-center gap-2">
                                <IconifySpinner size="sm" /><span>Suppression...</span>
                              </span>
                              <span v-else>Confirmer</span>
                            </Button>
                          </DialogFooter>
                        </DialogContent>
                      </Dialog>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Empty state -->
          <div v-else class="flex flex-col items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
            <span class="iconify hugeicons--package-out text-4xl mb-2"></span>
            <span>{{ querySorties ? 'Aucune sortie trouvée' : 'Aucune sortie enregistrée' }}</span>
          </div>
        </BoxPanelWrapper>
      </div>
    </div>
  </DashLayout>
</template>
