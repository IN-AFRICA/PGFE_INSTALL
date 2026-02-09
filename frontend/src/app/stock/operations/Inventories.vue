<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import { tagStockNavOperations } from '@/components/templates/stock/tags-links'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import TabPagination from '@/components/blocks/TabPagination.vue'
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Stock', href: '/stock' },
    { label: 'Opérations', href: '/stock/operations' },
    { label: 'Inventaires', isActive: true },
  ],
}

const activeTagName = 'inventaires'
const query = ref('')

const {
  data: rawData,
  loading: listLoading,
  fetchData: fetchInventories,
  meta,
} = useGetApi(API_ROUTES.GET_STOCK_INVENTORIES)

// Create/Edit/Delete Logic
const {
  loading: creating,
  success: createSuccess,
  postData: createInventory,
  error: createError,
} = usePostApi()
const {
  loading: updating,
  success: updateSuccess,
  putData: updateInventory,
  error: updateError,
} = usePutApi()
const {
  deleting,
  success: deleteSuccess,
  errorDelete,
  deleteItem: deleteInventoryApi,
} = useDeleteApi()

const inventories = computed(() => {
  if (!rawData.value) return []
  // Handle the wrapped response structure "message", "data", "pagination" or direct array
  // Based on user input: { status: true, message: "...", data: [], pagination: {...} }
  if ((rawData.value as any).data && Array.isArray((rawData.value as any).data)) {
    return (rawData.value as any).data
  }
  if (Array.isArray(rawData.value)) return rawData.value
  return []
})

const filteredInventories = computed(() => {
  if (!query.value) return inventories.value
  // Filter logic can be extended based on displayed fields
  const s = query.value.toLowerCase()
  return inventories.value.filter((item: any) =>
    item.inventory_date?.toLowerCase().includes(s),
  )
})

const total = computed(() => meta.value?.total || (rawData.value as any)?.pagination?.total || filteredInventories.value.length)
const page = ref(1)
const perPageCount = ref(15)

// Modal State
const isModalOpen = ref(false)
const isEditing = ref(false)
const currentId = ref<number | null>(null)
const formData = ref({
  inventory_date: '',
  note: '', // Optional desc
})

const resetForm = () => {
  formData.value = { inventory_date: new Date().toISOString().split('T')[0], note: '' }
  isEditing.value = false
  currentId.value = null
}

const openCreateModal = () => {
  resetForm()
  isModalOpen.value = true
}

const openEditModal = (item: any) => {
  formData.value = {
     inventory_date: item.inventory_date ? item.inventory_date.split('T')[0] : '',
     note: item.note || '' 
  }
  currentId.value = item.id
  isEditing.value = true
  isModalOpen.value = true
}

const handleSubmit = async () => {
  if (!formData.value.inventory_date) {
    showCustomToast({ message: 'La date est obligatoire', type: 'warning' })
    return
  }

  if (isEditing.value && currentId.value) {
    await updateInventory(API_ROUTES.UPDATE_STOCK_INVENTORY(currentId.value), formData.value)
    if (updateSuccess.value) {
      showCustomToast({ message: 'Inventaire mis à jour avec succès', type: 'success' })
      isModalOpen.value = false
      fetchInventories()
    } else {
      showCustomToast({
        message: updateError.value || 'Erreur lors de la mise à jour',
        type: 'error',
      })
    }
  } else {
    await createInventory(API_ROUTES.CREATE_STOCK_INVENTORY, formData.value)
    if (createSuccess.value) {
      showCustomToast({ message: 'Inventaire créé avec succès', type: 'success' })
      isModalOpen.value = false
      fetchInventories()
    } else {
      showCustomToast({
        message: createError.value || 'Erreur lors de la création',
        type: 'error',
      })
    }
  }
}

// Delete Logic
const deletingId = ref<number | null>(null)

const handleDelete = async (id: number) => {
  deletingId.value = id
  await deleteInventoryApi(API_ROUTES.DELETE_STOCK_INVENTORY(id))
  deletingId.value = null

  if (deleteSuccess.value) {
    showCustomToast({ message: 'Inventaire supprimé avec succès', type: 'success' })
    fetchInventories()
  } else {
    showCustomToast({
      message: errorDelete.value || 'Erreur lors de la suppression',
      type: 'error',
    })
  }
}

const onPerPageUpdate = (val: number) => {
  page.value = 1
  perPageCount.value = val
  fetchInventories({ page: page.value, limit: perPageCount.value })
}

onMounted(() => {
  fetchInventories()
})
</script>

<template>
  <DashLayout
    :breadcrumb="breadcrumbItems"
    active-route="/stock/operations"
    module-name="stock"
  >
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Opérations"
        description="Gestion des inventaires stock"
        :tags="tagStockNavOperations"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper class="flex-1 flex flex-col min-h-0">
        <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between mb-4">
          <div class="flex flex-1 items-center gap-2">
            <div class="relative w-full max-w-xs">
              <Input
                type="text"
                v-model="query"
                placeholder="Rechercher..."
                class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div
                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
              >
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>
          </div>

          <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
            <Dialog v-model:open="isModalOpen">
              <DialogTrigger as-child>
                <Button size="md" class="rounded-md" @click="openCreateModal">
                  <span class="flex iconify hugeicons--plus-sign"></span>
                  <span class="hidden sm:flex">Nouvel Inventaire</span>
                </Button>
              </DialogTrigger>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>{{
                    isEditing ? 'Modifier l\'inventaire' : 'Nouvel inventaire'
                  }}</DialogTitle>
                  <DialogDescription>
                    {{
                      isEditing
                        ? 'Modifier les informations de l\'inventaire.'
                        : 'Lancer un nouvel inventaire de stock.'
                    }}
                  </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                  <div class="space-y-2">
                    <Label>Date d'inventaire</Label>
                    <Input type="date" v-model="formData.inventory_date" />
                  </div>
                  <div class="space-y-2">
                    <Label>Note (Optionnel)</Label>
                    <Input v-model="formData.note" placeholder="Note ou description..." />
                  </div>
                </div>
                <DialogFooter>
                  <DialogClose as-child>
                    <Button variant="outline">Annuler</Button>
                  </DialogClose>
                  <Button
                    @click="handleSubmit"
                    :disabled="creating || updating"
                    class="min-w-[120px]"
                  >
                    <IconifySpinner v-if="creating || updating" class="mr-2" />
                    <span v-if="creating || updating"
                      >{{ isEditing ? 'Modification...' : 'Création...' }}</span
                    >
                    <span v-else>{{ isEditing ? 'Modifier' : 'Créer' }}</span>
                  </Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>
          </div>
        </div>

        <!-- Loading State -->
        <div
          v-if="listLoading"
          class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500"
        >
          <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
          <span>Chargement...</span>
        </div>

        <!-- Table -->
        <div
          v-else-if="filteredInventories && filteredInventories.length"
          class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
        >
          <Table class="rounded-md bg-white">
            <TableHeader>
              <TableRow>
                <TableHead class="w-[20px]"><Checkbox class="bg-white scale-70" /></TableHead>
                <TableHead>#</TableHead>
                <TableHead>Date</TableHead>
                <TableHead>Note</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in filteredInventories" :key="item.id">
                <TableCell class="w-[40px]"><Checkbox class="bg-white scale-70" /></TableCell>
                <TableCell>{{ index + 1 }}</TableCell>
                <TableCell>{{ item.inventory_date }}</TableCell>
                <TableCell>{{ item.note || '-' }}</TableCell>
                <TableCell>
                  <div class="flex items-center justify-end gap-2">
                    <Button
                      size="icon"
                      variant="ghost"
                      class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                      @click="openEditModal(item)"
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
                          <DialogTitle>Supprimer cet inventaire ?</DialogTitle>
                          <DialogDescription>
                             Action irréversible. Voulez-vous vraiment supprimer l'inventaire du {{ item.inventory_date }} ?
                          </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                          <DialogClose as-child>
                            <Button variant="outline">Annuler</Button>
                          </DialogClose>
                          <Button
                            variant="destructive"
                            @click="handleDelete(item.id)"
                            :disabled="deletingId === item.id"
                          >
                            <IconifySpinner v-if="deletingId === item.id" class="mr-2" />
                            <span v-if="deletingId === item.id">Suppression...</span>
                            <span v-else>Supprimer</span>
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

        <!-- Empty State -->
        <div
          v-else
          class="flex flex-col items-center justify-center h-full py-10 bg-white rounded-md text-gray-500"
        >
          <span class="iconify hugeicons--file-failed text-4xl mb-2"></span>
          <span>Aucun inventaire trouvé.</span>
        </div>

        <TabPagination
          v-if="filteredInventories && filteredInventories.length"
          v-model="page"
          :perPage="perPageCount"
          :totalItems="total"
          @update:perPage="onPerPageUpdate"
        />
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>
