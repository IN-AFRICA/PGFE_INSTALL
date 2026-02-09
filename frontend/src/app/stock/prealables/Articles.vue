<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import { tagStockNavPrealables } from '@/components/templates/stock/tags-links'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import ExportDropdown from '@/components/ExportDropdown.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { showCustomToast } from '@/utils/widgets/custom_toast'
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
import { eventBus } from '@/utils/eventBus'
import { usePagination } from '@/composables/usePagination'




const router = useRouter()

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Stock', href: '/stock' },
    { label: 'Préalables', isActive: true },
  ],
}

const activeTagName = 'articles'

const query = ref('')
const { data: rawData, loading, fetchData, meta } = useGetApi(API_ROUTES.GET_STOCK_ARTICLES)
const { deleting, errorDelete, deleteItem } = useDeleteApi()

const { page, perPageCount } = usePagination(fetchData, 1, 15)
const total = computed(() => meta?.value?.total || 0)

const articles = computed(() => {
  if (!rawData.value) return []
  if (Array.isArray(rawData.value)) return rawData.value
  return (rawData.value as any).data || []
})

const filteredArticles = computed(() => {
  if (!query.value) return articles.value
  const s = query.value.toLowerCase()
  return articles.value.filter((a: any) => a.name?.toLowerCase().includes(s))
})

onMounted(() => {
  fetchData({ page: page.value, limit: perPageCount.value })
})

eventBus.on('stockArticleUpdated', () => {
  fetchData({ page: page.value, limit: perPageCount.value })
})

const handleAddArticle = () => {
  router.push('/stock/prealables/articles/nouveau')
}

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

const deletingId = ref<number | null>(null)
const handleDelete = async (id: number) => {
  deletingId.value = id
  const url = API_ROUTES.DELETE_STOCK_ARTICLE(id)
  await deleteItem(url)
  deletingId.value = null

  if (errorDelete.value) {
    showCustomToast({
      message: errorDelete.value,
      type: 'error',
    })
  } else {
    showCustomToast({
      message: 'Item supprimé avec succès',
      type: 'success',
    })
    fetchData({ page: page.value, limit: perPageCount.value })
  }
}



const handleEdit = (item: any) => {
  router.push({
    path: '/stock/prealables/articles/nouveau',
    query: { id: item.id },
    state: { data: JSON.stringify(item) },
  })
}

const getRowIndex = (index: number) => {
  return (Number(page.value) - 1) * Number(perPageCount.value) + index + 1
}

const exportLoading = ref(false)
</script>

<template>
  <DashLayout
    :breadcrumb="breadcrumbItems"
    active-route="/stock/prealables/articles"
    module-name="stock"
  >
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Gestion des Items"
        :tags="tagStockNavPrealables"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper class="flex-1 flex flex-col min-h-0">
        <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between mb-4">
          <div class="flex flex-1 items-center gap-2">
            <div class="relative w-full max-w-xs">
              <Input
                type="text"
                v-model="query"
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
                  <div class="text-sm text-gray-500">Filtres (Simulation)</div>
                </div>
              </PopoverContent>
            </Popover>
          </div>

          <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
            <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" @click="handleAddArticle">
              <span class="flex iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Ajouter Item</span>
            </Button>
            <ExportDropdown :loading="exportLoading" @export="handleExport" />
          </div>
        </div>

        <div
          v-if="loading"
          class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500"
        >
          <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
          <span>Chargement...</span>
        </div>

        <div
          v-else-if="filteredArticles && filteredArticles.length"
          class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
        >
          <Table class="rounded-md bg-white">
            <TableHeader>
              <TableRow>
                <TableHead class="w-[20px]"><Checkbox class="bg-white scale-70" /></TableHead>
                <TableHead>N°</TableHead>
                <TableHead>Désignation</TableHead>
                <TableHead>Stock actuel</TableHead>
                <TableHead>Seuil Min</TableHead>
                <TableHead>Seuil Max</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in filteredArticles" :key="item.id">
                <TableCell class="w-[40px]"><Checkbox class="bg-white scale-70" /></TableCell>
                <TableCell>{{ getRowIndex(index as number) }}</TableCell>
                <TableCell>{{ item.name }}</TableCell>
                <TableCell>{{ item.quantity || '0' }}</TableCell>
                <TableCell>{{ item.min_threshold || '—' }}</TableCell>
                <TableCell>{{ item.max_threshold || '—' }}</TableCell>
                <TableCell>
                  <div class="flex items-center justify-end gap-2">
                    <Button
                      size="icon"
                      variant="ghost"
                      class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                      @click="handleEdit(item)"
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
                          <DialogTitle>Supprimer cet item ?</DialogTitle>
                          <DialogDescription
                            >Action irréversible. Voulez-vous vraiment supprimer "{{
                              item.name
                            }}" ?</DialogDescription
                          >
                        </DialogHeader>
                        <DialogFooter>
                          <DialogClose as-child
                            ><Button variant="outline">Annuler</Button></DialogClose
                          >
                          <Button
                            variant="destructive"
                            :disabled="deletingId === item.id"
                            @click="handleDelete(item.id)"
                          >
                            <span v-if="deletingId === item.id" class="flex items-center gap-2"
                              ><IconifySpinner size="sm" /><span>Suppression...</span></span
                            >
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

        <div
          v-else
          class="flex flex-col items-center justify-center h-full py-10 bg-white rounded-md text-gray-500"
        >
          <span class="iconify hugeicons--file-failed text-4xl mb-2"></span>
          <span>Aucun item trouvé.</span>
        </div>

        <TabPagination
          v-if="articles && articles.length"
          v-model="page"
          :perPage="perPageCount"
          :totalItems="total"
          @update:perPage="onPerPageUpdate"
        />
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>
