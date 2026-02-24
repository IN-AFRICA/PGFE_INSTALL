<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
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
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import ExportDropdown from '@/components/ExportDropdown.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import { tagLocationNavPrealables } from '@/components/templates/location/location-tags-links'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import type { BreadcrumbProps } from '@/types'

type ContractEquipment = {
  id: number
  contract_equipment_code: string | null
  uuid: string | null
  rental_contract_id: string
  equipment_id: string
  quantity: string
  price: string
  is_hand_over: string
  school_id: string
  user_id: string
  created_at: string
  updated_at: string
  client_name: string | null
  rental_contract?: {
    id: number
    contract_code: string | null
    client_id: string | null
  }
  equipment?: {
    id: number
    name: string
    equipment_code: string | null
  }
}

const query = ref('')
const exportLoading = ref(false)
const page = ref(1)
const perPageCount = ref(10)

const { data: apiData, loading, fetchData } = useGetApi<ContractEquipment[]>(
  API_ROUTES.GET_RENTAL_CONTRACT_EQUIPMENTS
)
const { deleteItem, deleting, success: deleteSuccess } = useDeleteApi()

onMounted(async () => {
  await fetchData()
})

const allItems = computed(() => apiData.value || [])

const filteredItems = computed(() => {
  const q = query.value.trim().toLowerCase()
  return allItems.value.filter((item) => {
    return (
      q.length === 0 ||
      (item.rental_contract?.contract_code && item.rental_contract.contract_code.toLowerCase().includes(q)) ||
      (item.equipment?.name && item.equipment.name.toLowerCase().includes(q)) ||
      (item.client_name && item.client_name.toLowerCase().includes(q))
    )
  })
})

const total = computed(() => filteredItems.value.length)
const data = computed(() => {
  const start = (page.value - 1) * perPageCount.value
  return filteredItems.value.slice(start, start + perPageCount.value)
})

const onPerPageUpdate = (value: number) => {
  perPageCount.value = value
  page.value = 1
}

const handleDelete = async (id: number) => {
  await deleteItem(API_ROUTES.DELETE_RENTAL_CONTRACT_EQUIPMENT(id))
  if (deleteSuccess.value) {
    showCustomToast({ message: 'Équipement de contrat supprimé avec succès', type: 'success' })
    await fetchData()
  }
}

const handleExport = (format: string) => {
  exportLoading.value = true
  window.setTimeout(() => {
    exportLoading.value = false
    console.info(`Mock export: ${format}`)
  }, 600)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR')
}

const breadcrumb: BreadcrumbProps = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Location', href: '/location' },
    { label: 'Équipements de contrat', isActive: true },
  ],
}
</script>

<template>
  <DashLayout module-name="location" active-route="/location/prealables" :breadcrumb="breadcrumb">
    <div class="pb-6 mx-auto w-full max-w-7xl">
      <DashPageHeader
        title="Location"
        :tags="tagLocationNavPrealables"
        active-tag-name="contract-equipments"
      />
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              type="text"
              v-model="query"
              id="search"
              name="search"
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
          <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" as-child>
            <RouterLink to="/location/prealables/contract-equipments/nouveau">
              <span class="flex iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Nouveau</span>
            </RouterLink>
          </Button>
          <ExportDropdown :loading="exportLoading" @export="handleExport" />
        </div>
      </div>
      <div
        v-if="loading"
        class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500"
      >
        <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
        <span>Chargement des équipements de contrat...</span>
      </div>
      <div
        v-else-if="data && data.length"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
      >
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead class="w-[60px]">N°</TableHead>
              <TableHead>Contrat</TableHead>
              <TableHead>Client</TableHead>
              <TableHead>Équipement</TableHead>
              <TableHead>Quantité</TableHead>
              <TableHead>Date création</TableHead>
              <TableHead>Action</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in data" :key="item.id">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{ (page - 1) * perPageCount + (data.indexOf(item) + 1) }}</TableCell>
              <TableCell>{{ item.rental_contract?.contract_code || '-' }}</TableCell>
              <TableCell>{{ item.client_name || '-' }}</TableCell>
              <TableCell>{{ item.equipment?.name || '-' }}</TableCell>
              <TableCell>{{ item.quantity }}</TableCell>
              <TableCell>{{ formatDate(item.created_at) }}</TableCell>
              <TableCell>
                <div class="group flex items-center justify-between">
                  <div class="flex-1"></div>

                  <button
                    class="ml-2 group-hover:hidden rounded-full size-8 flex items-center justify-center hover:bg-gray-100 transition"
                  >
                    <span class="iconify hugeicons--more-vertical-circle-01"></span>
                  </button>

                  <div class="hidden group-hover:flex items-center gap-2 ml-2">
                    <Button
                      variant="ghost"
                      size="icon"
                      class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                    >
                      <span class="iconify hugeicons--edit-02"></span>
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      class="size-8 justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                    >
                      <span class="iconify hugeicons--eye"></span>
                    </Button>

                    <AlertMessage
                      action="danger"
                      title="Supprimer un équipement de contrat"
                      :message="`Vous êtes sur le point de supprimer cet équipement du contrat. Êtes-vous sûr de continuer?`"
                    >
                      <template #trigger>
                        <Button
                          variant="ghost"
                          size="icon"
                          :disabled="deleting"
                          class="size-8 justify-center text-gray-500 hover:text-red-600 hover:bg-red-50"
                        >
                          <span class="iconify hugeicons--delete-02"></span>
                        </Button>
                      </template>
                      <template #confirm-action-button>
                        <Button
                          variant="destructive"
                          @click="handleDelete(item.id)"
                          size="sm"
                          class="h-10 px-4"
                        >
                          Oui, supprimer
                        </Button>
                      </template>
                    </AlertMessage>
                  </div>
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
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="size-6"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"
          />
        </svg>
        <span>Aucun équipement de contrat trouvé pour le moment.</span>
      </div>

      <TabPagination
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />
    </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>
