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
import { onMounted, computed } from 'vue'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useRouter } from 'vue-router'
import {
  AlertDialog,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const router = useRouter()

// API Calls
const {
  data: formationsData,
  fetchData: fetchFormations,
  loading: loadingFormations,
  error: errorFormations,
} = useGetApi(API_ROUTES.GET_FORMATIONS_CONTINUES)
const { data: personnelsData, fetchData: fetchPersonnels } = useGetApi(API_ROUTES.GET_PERSONALS)

// Search functionality
const { query } = useSearch((params: { search: string }) => {
  setAdditionalParams({ search: params.search })
  fetchFormations({ page: page.value, per_page: perPageCount.value, ...params })
}, 500)

// Pagination functionality
const { page, perPageCount, total, setAdditionalParams } = usePagination(fetchFormations, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

onMounted(async () => {
  await Promise.all([
    fetchFormations({ page: page.value, per_page: perPageCount.value }),
    fetchPersonnels(),
  ])
})

// Helper to resolve Personnel Name
/* Champ non renvoyé par l'API pour le moment
const getPersonnelName = (id: number) => {
  const personnels = Array.isArray(personnelsData.value)
    ? personnelsData.value
    : (personnelsData.value as any)?.data || [];

  const p = personnels.find((p: any) => p.id === id);
  return p ? `${p.name || p.nom || ''} ${p.postnom || ''} ${p.firstname || p.prenom || ''}`.trim() : 'Inconnu';
};
*/

// Computed Rows
const rows = computed(() => {
  const data = Array.isArray(formationsData.value)
    ? formationsData.value
    : (formationsData.value as any)?.data || []

  return data.map((item: any) => ({
    id: item.id,
    // nom: getPersonnelName(item.academic_personal_id), // Non disponible
    nomFormation: item.title || '-',
    lieu_endroit: item.location || '-',
    dateStart: item.start_date ? new Date(item.start_date).toLocaleDateString('fr-FR') : 'N/A',
    dateEnd: item.end_date ? new Date(item.end_date).toLocaleDateString('fr-FR') : '-',
    // Calcul de la durée si les deux dates sont présentes
    delay:
      item.start_date && item.end_date
        ? Math.ceil(
            (new Date(item.end_date).getTime() - new Date(item.start_date).getTime()) /
              (1000 * 60 * 60 * 24),
          ) + ' jours'
        : '-',
    description: item.description || '-',
  }))
})

// Delete functionality
const { deleteItem, deleting } = useDeleteApi()

// Handle edit
function handleEdit(id: number) {
  router.push(`/rh/saisie/formation-continue/modifier-formation/${id}`)
}

// Handle delete
async function handleDelete(id: number) {
  try {
    await deleteItem(API_ROUTES.DELETE_FORMATION_CONTINUE(id))

    showCustomToast({
      message: 'Formation supprimée avec succès !',
      type: 'success',
    })

    // Reload the list with current params
    await fetchFormations({
      page: page.value,
      per_page: perPageCount.value,
      search: query.value,
    })
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    showCustomToast({
      message: 'Erreur lors de la suppression de la formation.',
      type: 'error',
    })
  }
}

// Handle per page update
function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}
</script>

<template>
  <SaisieRhLayout activeBread="Formations Personnel" active-tag-name="formation" group="saisie">
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher une formation..."
            v-model="query"
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <Button size="md" class="" as-child>
            <RouterLink
              to="/rh/saisie/formation-continue/nouvelle-formation"
              class="rounded-md max-sm:flex-1 sm:w-max flex items-center gap-2"
            >
              <span class="flex iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Nouvelle formation</span>
            </RouterLink>
          </Button>
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
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <!-- <TableHead>Nom</TableHead> -->
              <TableHead>Nom formation</TableHead>
              <TableHead>Lieu/Endroit</TableHead>
              <TableHead>Date début</TableHead>
              <TableHead>Date fin</TableHead>
              <TableHead>Durée</TableHead>
              <TableHead>Description</TableHead>
              <TableHead> Opérations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="loadingFormations">
              <TableRow>
                <TableCell colspan="8" class="text-center py-8"> Chargement... </TableCell>
              </TableRow>
            </template>
            <template v-else-if="rows.length === 0">
              <TableRow>
                <TableCell colspan="8" class="text-center py-8 text-gray-500">
                  Aucune formation trouvée.
                </TableCell>
              </TableRow>
            </template>
            <template v-else>
              <TableRow v-for="formation in rows" :key="formation.id">
                <TableCell class="w-[40px]">
                  <Checkbox class="bg-white scale-70" />
                </TableCell>
                <!--
                                <TableCell class="font-medium">
                                    {{ formation.nom }}
                                </TableCell>
                                -->
                <TableCell class="font-medium">
                  {{ formation.nomFormation }}
                </TableCell>
                <TableCell class="font-medium">
                  {{ formation.lieu_endroit }}
                </TableCell>
                <TableCell class="font-medium">
                  {{ formation.dateStart }}
                </TableCell>
                <TableCell class="font-medium">
                  {{ formation.dateEnd }}
                </TableCell>
                <TableCell class="font-medium">
                  {{ formation.delay }}
                </TableCell>
                <TableCell class="font-medium max-w-xs truncate" :title="formation.description">
                  {{ formation.description }}
                </TableCell>
                <TableCell>
                  <div class="flex items-center gap-2 w-max">
                    <Button
                      variant="outline"
                      size="icon"
                      class="size-8"
                      @click="handleEdit(formation.id)"
                      :disabled="deleting"
                    >
                      <span class="iconify hugeicons--edit-02"></span>
                    </Button>
                    <AlertDialog>
                      <AlertDialogTrigger as-child>
                        <Button size="sm" variant="destructive" class="h-8">
                          <span class="iconify hugeicons--delete-02"></span>
                          <span class="sr-only">Supprimer</span>
                        </Button>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Supprimer cette formation ?</AlertDialogTitle>
                          <AlertDialogDescription>
                            Cette action est irréversible. Si des données dépendent de cette
                            formation, la suppression sera refusée.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Annuler</AlertDialogCancel>
                          <Button
                            variant="destructive"
                            :disabled="deleting"
                            @click="handleDelete(formation.id)"
                          >
                            <span v-if="deleting" class="flex items-center gap-2">
                              <IconifySpinner size="sm" />
                              <span>Suppression...</span>
                            </span>
                            <span v-else>Confirmer</span>
                          </Button>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </div>
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>
      </div>
      <TabPagination
        v-if="!loadingFormations && !errorFormations"
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />
    </BoxPanelWrapper>
  </SaisieRhLayout>
</template>
