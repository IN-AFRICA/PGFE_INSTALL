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
import SaisieRhLayout from '@/components/templates/rh/SaisieRhLayout.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { ref, onMounted, watch } from 'vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { extractSuccessMessage, isApiSuccess } from '../comptabilite/prealables/utils'
import AlertMessage from '@/components/modals/AlertMessage.vue'

const {
  data: personnelData,
  loading,
  error,
  fetchData,
} = useGetApi(API_ROUTES.GET_ACADEMIC_PERSONALS)
const {
  deleteItem: deletePersonnel,
  deleteResponse: deletePersonnelResponse,
  errorDelete: deletePersonnelError,
  deleting: deletingPersonnel,
} = useDeleteApi<any>()

const handleDelete = async (id: number) => {
  try {
    await deletePersonnel(API_ROUTES.DELETE_ACADEMIC_PERSONAL(id))

    if (deletePersonnelError.value) {
      showCustomToast({ message: deletePersonnelError.value, type: 'error' })
      return
    }

    if (isApiSuccess(deletePersonnelResponse.value, deletePersonnelError.value)) {
      const message = extractSuccessMessage(
        deletePersonnelResponse.value,
        'Personnel supprimé avec succès',
      )
      showCustomToast({ message, type: 'success' })
      await fetchData()
      eventBus.emit('personnelUpdated' as never)
    }
  } catch (err) {
    console.error('Erreur lors de la suppression:', err)
    showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
  }
}
const { query } = useSearch((params: { search: string }) => {
  setAdditionalParams({ search: params.search })
  fetchData({ page: page.value, per_page: perPageCount.value, ...params })
}, 500)

const { page, perPageCount, total, setAdditionalParams } = usePagination(fetchData, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

// Charger les données au montage
onMounted(() => {
  fetchData({ page: page.value, per_page: perPageCount.value })
})

// Écouter l'événement de mise à jour du personnel
eventBus.on('personalUpdated', () => {
  fetchData({
    page: page.value,
    per_page: perPageCount.value,
    search: query.value,
  })
})

// Log pour debug - voir la structure des données
watch(
  personnelData,
  (newData) => {
    if (newData && newData.length > 0) {
      console.log('🔍 Premier élément du personnel:', newData[0])
      console.log('📊 Structure complète:', newData)
    }
  },
  { immediate: true },
)

function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}
</script>

<template>
  <SaisieRhLayout activeBread="Personnel" active-tag-name="personnel" group="saisie">
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher un personnel..."
            v-model="query"
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" as-child>
            <RouterLink to="/rh/saisie/personnel/nouveau">
              <span class="flex iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Nouveau Personnel</span>
            </RouterLink>
          </Button>
        </div>
      </div>

      <!-- Loading state -->
      <div
        v-if="loading"
        class="flex flex-col items-center justify-center py-10 bg-white rounded-md text-gray-500"
      >
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
        <span>Chargement des personnels...</span>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="mt-4 p-8 text-center bg-white rounded-md">
        <div class="flex flex-col items-center gap-2 text-red-600">
          <span class="iconify hugeicons--alert-circle text-3xl"></span>
          <span>Erreur lors du chargement des données</span>
          <Button
            @click.prevent="eventBus.emit('personalUpdated')"
            variant="outline"
            size="sm"
            class="mt-2"
          >
            Réessayer
          </Button>
        </div>
      </div>

      <!-- Data table -->
      <div
        v-else-if="personnelData && personnelData.length"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
      >
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead>Matricule</TableHead>
              <TableHead>Nom complet</TableHead>
              <TableHead>Sexe</TableHead>
              <TableHead>Téléphone</TableHead>
              <TableHead>État civil</TableHead>
              <TableHead>Fonction</TableHead>
              <TableHead>Niveau d'études</TableHead>
              <TableHead> Opérations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in personnelData" :key="item.id">
              <TableCell class="w-[40px]">
                <Checkbox class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{ item.matricule }}</TableCell>
              <TableCell>{{ item.firstname }} {{ item.name }}</TableCell>
              <TableCell>{{ item.gender }}</TableCell>
              <TableCell>{{ item.phone }}</TableCell>
              <TableCell>{{ item.civil_status }}</TableCell>
              <TableCell>{{ item.fonction.name }}</TableCell>
              <TableCell>{{ item.academic_level.name }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <RouterLink :to="`/rh/saisie/personnel/nouveau?id=${item.id}`">
                    <Button variant="outline" size="icon" class="size-8">
                      <span class="iconify hugeicons--edit-02"></span>
                    </Button>
                  </RouterLink>

                  <AlertMessage
                    action="danger"
                    title="Supprimer un personnel"
                    :message="`Vous êtes sur le point de supprimer le personnel '${item.pre_name} ${item.name}'. Êtes-vous sûr de continuer?`"
                  >
                    <template #trigger>
                      <Button
                        variant="destructive"
                        size="icon"
                        class="size-8"
                        :disabled="deletingPersonnel"
                      >
                        <span class="iconify hugeicons--delete-02"></span>
                      </Button>
                    </template>
                    <template #confirm-action-button>
                      <Button
                        variant="destructive"
                        size="sm"
                        class="h-10 px-4"
                        @click.stop="handleDelete(item.id)"
                        :disabled="deletingPersonnel"
                      >
                        Oui, Supprimer
                      </Button>
                    </template>
                  </AlertMessage>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Empty state -->
      <div
        v-else
        class="flex flex-col items-center justify-center h-full py-10 bg-white rounded-md text-gray-500 mt-4"
      >
        <span>Aucun personnel trouvé</span>
      </div>

      <TabPagination
        v-if="!loading && !error"
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />
    </BoxPanelWrapper>
  </SaisieRhLayout>
</template>
