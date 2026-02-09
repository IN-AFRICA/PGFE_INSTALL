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
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

import { Checkbox } from '@/components/ui/checkbox'
import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { eventBus } from '@/utils/eventBus'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import AlertMessage from '@/components/modals/AlertMessage.vue'
// import NewAcademicLevel from '@/components/modals/NewAcademicLevel.vue';
// import { Edit } from 'lucide-vue-next';
// import EditNiveauAcademique from '@/components/modals/EditAcademicLevel.vue';
const { data, loading, error, fetchData } = useGetApi(API_ROUTES.GET_ACADEMIC_LEVEL_LIST)
const { deleting, errorDelete, deleteItem } = useDeleteApi()

const { query } = useSearch(fetchData, 500)

const { page, perPageCount } = usePagination(fetchData, 1, 5)
fetchData({ page: page.value, limit: perPageCount.value })

eventBus.on('niveauAcademicUpdated', () => {
  fetchData({ page: page.value, limit: perPageCount.value })
})

//handleEdit
const handleEdit = (item: any) => {
  eventBus.emit('editNiveauAcademique', item)
}

const handleDelete = async (id: number) => {
  const url = API_ROUTES.DELETE_ACADEMIC_LEVEL.replace(':academicLevel', String(id))
  await deleteItem(url)
  eventBus.emit('niveauAcademicUpdated')
  if (errorDelete.value) {
    showCustomToast({
      message: errorDelete.value,
      type: 'error',
    })
    return
  } else {
    showCustomToast({
      message: 'Niveau scolaire supprimé avec succès.',
      type: 'success',
    })
  }
}
</script>

<template>
  <LayoutSaisieOperation group="saisie" active-tag-name="niveau-academique">
    <BoxPanelWrapper>
      <div class="flex items-center gap-3 justify-between">
        <div class="relative flex-1">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher un cours..."
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
          <!-- <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="md" class="bg-white border border-border rounded-md">
                Exporter
                <span class="iconify hugeicons--arrow-down-01 " />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
              <DropdownMenuItem class="flex items-center">
                <span class="flex mr-1.5 iconify hugeicons--pdf-02"></span>
                Exporter pdf
              </DropdownMenuItem>
              <DropdownMenuItem class="flex items-center">
                <span class="flex mr-1.5 iconify hugeicons--ai-sheets"></span>
                Exporter Excel
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu> -->
          <NewAcademicLevel />
        </div>
      </div>
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
        <span>Chargement des classes...</span>
      </div>
      <div v-else-if="data.length" class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader class="bg-primary text-white text-xs uppercase">
            <TableRow>
              <TableHead rowspan="2" class="w-14">
                <Checkbox :checked="true" class="bg-white scale-70" />
              </TableHead>
              <TableHead rowspan="2">Niveau</TableHead>
              <TableHead rowspan="2">Operations</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="(item, index) in data" :key="index">
              <TableCell class="w-14">
                <Checkbox :checked="true" class="bg-white scale-70" />
              </TableCell>
              <TableCell>{{ item.name }}</TableCell>

              <TableCell class="px-4">
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
                      @click="handleEdit(item)"
                    >
                      <span class="iconify hugeicons--edit-02"></span>
                    </Button>

                    <AlertMessage
                      action="danger"
                      title="Supprimer un niveau scolaire"
                      :message="`Vous êtes sur le point de supprimer le niveau scolaire '${item.name}'. Êtes-vous sûr de continuer?`"
                    >
                      <template #trigger>
                        <Button
                          variant="ghost"
                          size="icon"
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
                          Oui, Supprimer
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
        class="flex flex-col items-center justify-center py-10 bg-white rounded-md text-gray-500"
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
        <span>Aucune filière trouvée pour le moment.</span>
      </div>
    </BoxPanelWrapper>
  </LayoutSaisieOperation>
</template>
