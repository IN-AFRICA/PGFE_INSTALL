<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import { tagInfraNavOperations } from '@/components/templates/infra/tags-links'
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
import { usePagination } from '@/composables/usePagination'
import { eventBus } from '@/utils/eventBus'

const route = useRoute()
const router = useRouter()

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Infrastructure', href: '/infra' },
    { label: 'Opérations', isActive: true },
  ],
}

const activeTagName = computed(() => {
  const currentPath = route.path
  const matchedTag = tagInfraNavOperations.find((tag) => tag.href === currentPath)
  return matchedTag ? matchedTag.name : ''
})

// === API Integration ===
const { data: rawData, loading, fetchData, meta } = useGetApi(API_ROUTES.GET_INFRA_EQUIPMENTS)
const { data: typesRaw, fetchData: fetchTypes } = useGetApi(API_ROUTES.GET_INFRA_TYPES)
const { data: statesRaw, fetchData: fetchStates } = useGetApi(API_ROUTES.GET_INFRA_STATES)
const { deleteItem, deleting, success: deleteSuccess } = useDeleteApi()

const { page, perPageCount } = usePagination(fetchData, 1, 15)
const total = computed(() => meta?.value?.total || 0)

const equipments = computed(() => {
  if (!rawData.value) return []
  if (Array.isArray(rawData.value)) return rawData.value
  return (rawData.value as any).data || []
})

const types = computed(() => {
  if (!typesRaw.value) return []
  if (Array.isArray(typesRaw.value)) return typesRaw.value
  return (typesRaw.value as any).data || []
})

const states = computed(() => {
  if (!statesRaw.value) return []
  if (Array.isArray(statesRaw.value)) return statesRaw.value
  return (statesRaw.value as any).data || []
})

const getTypeName = (typeId: any) => {
  const type = types.value.find((t: any) => t.id.toString() === typeId?.toString())
  return type?.name || '—'
}

const getStateName = (stateId: any) => {
  const state = states.value.find((s: any) => s.id.toString() === stateId?.toString())
  return state?.name || '—'
}

const query = ref('')
const filteredData = computed(() => {
  if (!query.value) return equipments.value
  const s = query.value.toLowerCase()
  return equipments.value.filter(
    (item: any) =>
      item.name?.toLowerCase().includes(s) ||
      item.serial_number?.toLowerCase().includes(s) ||
      item.location?.toLowerCase().includes(s)
  )
})

onMounted(() => {
  fetchData({ page: page.value, limit: perPageCount.value })
  fetchTypes()
  fetchStates()
})

const onPerPageUpdate = (val: number) => {
  page.value = 1
  perPageCount.value = val
}

eventBus.on('infraEquipmentUpdated', () => {
  fetchData({ page: page.value, limit: perPageCount.value })
})

const exportLoading = ref(false)
const handleExport = (format: string) => {
  showCustomToast({
    message: `Export ${format.toUpperCase()} lancé...`,
    type: 'success',
  })
}

const handleDelete = async (id: number) => {
  await deleteItem(API_ROUTES.DELETE_INFRA_EQUIPMENT(id))
  if (deleteSuccess.value) {
    showCustomToast({
      message: 'Enregistrement supprimé avec succès',
      type: 'success',
    })
    fetchData({ page: page.value, limit: perPageCount.value })
  }
}

const handleEdit = (item: any) => {
  router.push({
    path: '/infra/operations/equipements/nouveau',
    query: { id: item.id },
    state: { data: JSON.stringify(item) },
  })
}

const getRowIndex = (index: number) => {
  return (Number(page.value) - 1) * Number(perPageCount.value) + index + 1
}
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/infra/operations" module-name="infra">
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Registre Équipements"
        :tags="tagInfraNavOperations"
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
                  <span class="iconify hugeicons--filter"></span>
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-80">
                <div class="grid gap-4">
                  <div class="space-y-2">
                    <h4 class="font-medium leading-none">Filtrage</h4>
                  </div>
                  <div class="flex flex-col gap-3.5">
                    <div class="text-sm text-gray-500">Filtres (Bientôt disponible)</div>
                  </div>
                </div>
              </PopoverContent>
            </Popover>
          </div>

          <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
            <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" as-child>
              <RouterLink to="/infra/operations/equipements/nouveau">
                <span class="flex iconify hugeicons--plus-sign"></span>
                <span class="hidden sm:flex">Ajouter</span>
              </RouterLink>
            </Button>
            <ExportDropdown :loading="exportLoading" @export="handleExport" />
          </div>
        </div>

        <div
          v-if="loading"
          class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500"
        >
          <IconifySpinner class="text-2xl" />
          <span>Chargement...</span>
        </div>
        <div
          v-else-if="filteredData && filteredData.length"
          class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
        >
          <Table class="rounded-md bg-white">
            <TableHeader>
              <TableRow>
                <TableHead class="w-[20px]">
                  <Checkbox class="bg-white scale-70" />
                </TableHead>
                <TableHead>N°</TableHead>
                <TableHead>Code / SN</TableHead>
                <TableHead>Nom</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Emplacement</TableHead>
                <TableHead>État</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in filteredData" :key="item.id">
                <TableCell class="w-[40px]">
                  <Checkbox class="bg-white scale-70" />
                </TableCell>
                <TableCell>{{ getRowIndex(index as number) }}</TableCell>
                <TableCell>{{ item.serial_number || '—' }}</TableCell>
                <TableCell>{{ item.name }}</TableCell>
                <TableCell>{{ getTypeName(item.type_id) }}</TableCell>
                <TableCell>{{ item.location || '—' }}</TableCell>
                <TableCell>{{ getStateName(item.state_id) }}</TableCell>
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
                          <DialogTitle>Supprimer cet enregistrement ?</DialogTitle>
                          <DialogDescription>
                            Action irréversible. Voulez-vous vraiment supprimer "{{ item.name }}" ?
                          </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                          <DialogClose as-child>
                            <Button variant="outline">Annuler</Button>
                          </DialogClose>
                          <Button
                            variant="destructive"
                            @click="handleDelete(item.id)"
                            :disabled="deleting"
                          >
                            <IconifySpinner v-if="deleting" />
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
          <span class="iconify hugeicons--package-open text-4xl mb-2"></span>
          <span>Aucune donnée trouvée pour le moment.</span>
        </div>

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
