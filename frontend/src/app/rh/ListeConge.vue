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
import { onMounted, computed } from 'vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
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
  data: congesData,
  fetchData: fetchConges,
  loading: loadingConges,
  error: errorConges,
} = useGetApi(API_ROUTES.GET_PERSONNEL_CONGE)
const { data: personnelsData, fetchData: fetchPersonnels } = useGetApi(API_ROUTES.GET_PERSONALS)
const { data: schoolYearsData, fetchData: fetchSchoolYears } = useGetApi(
  API_ROUTES.GET_SCHOOL_YEARS,
)

// Search functionality
const { query } = useSearch((params: { search: string }) => {
  setAdditionalParams({ search: params.search })
  fetchConges({ page: page.value, per_page: perPageCount.value, ...params })
}, 500)

// Pagination functionality
const { page, perPageCount, total, setAdditionalParams } = usePagination(fetchConges, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

onMounted(async () => {
  await Promise.all([
    fetchConges({ page: page.value, per_page: perPageCount.value }),
    fetchPersonnels(),
    fetchSchoolYears(),
  ])
})

// Handle per page update
function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}

// Helper to resolve Personnel Name
const getPersonnelName = (id: number) => {
  const personnels = Array.isArray(personnelsData.value)
    ? personnelsData.value
    : (personnelsData.value as any)?.data || []

  const p = personnels.find((p: any) => p.id === id)
  return p
    ? `${p.name || p.nom || ''} ${p.postnom || ''} ${p.firstname || p.prenom || ''}`.trim()
    : 'Inconnu'
}

// Helper to resolve School Year
const getSchoolYearLabel = (id: number) => {
  const v: any = schoolYearsData.value
  const list = Array.isArray(v) ? v : (v?.years ?? v?.data ?? [])
  const year = list.find((y: any) => y.id === id)
  return (
    year?.name || year?.year || year?.libelle || `${year?.start_year}-${year?.end_year}` || 'N/A'
  )
}

// Computed Rows
const rows = computed(() => {
  const data = Array.isArray(congesData.value)
    ? congesData.value
    : (congesData.value as any)?.data || []

  return data.map((item: any) => ({
    id: item.id,
    date: item.creer_a ? new Date(item.creer_a).toLocaleDateString('fr-FR') : 'N/A',
    nom: getPersonnelName(Number(item.academic_personal_id)),
    year: getSchoolYearLabel(Number(item.school_year_id)),
    dayReq: item.jour_demand || 0,
    raison: item.description || '-',
  }))
})

// Delete functionality
const { deleteItem, deleting } = useDeleteApi()

// Handle edit
function handleEdit(id: number) {
  router.push(`/rh/saisie/conge/modifier-conge/${id}`)
}

// Handle delete
async function handleDelete(id: number) {
  try {
    await deleteItem(API_ROUTES.DELETE_PERSONNEL_CONGE(id))

    showCustomToast({
      message: 'Congé supprimé avec succès !',
      type: 'success',
    })

    // Reload the list with current params
    await fetchConges({
      page: page.value,
      per_page: perPageCount.value,
      search: query.value,
    })
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    showCustomToast({
      message: 'Erreur lors de la suppression du congé.',
      type: 'error',
    })
  }
}
</script>

<template>
  <SaisieRhLayout activeBread="Conge" active-tag-name="conge" group="saisie">
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
          <Button size="md" class="rounded-md" as-child>
            <RouterLink to="/rh/saisie/conge/nouveau-conge" class="">
              <span class="iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Nouveau congé</span>
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
              <TableHead class="w-50">Nom</TableHead>
              <TableHead>Jours demandés</TableHead>
              <TableHead>Date demande</TableHead>
              <TableHead>Année</TableHead>
              <TableHead>Raison</TableHead>
              <TableHead> Opérations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="loadingConges">
              <TableRow>
                <TableCell colspan="7" class="text-center py-8"> Chargement... </TableCell>
              </TableRow>
            </template>
            <template v-else-if="rows.length === 0">
              <TableRow>
                <TableCell colspan="7" class="text-center py-8 text-gray-500">
                  Aucune demande de congé trouvée.
                </TableCell>
              </TableRow>
            </template>
            <template v-else>
              <TableRow v-for="conge in rows" :key="conge.id">
                <TableCell class="w-[40px]">
                  <Checkbox class="bg-white scale-70" />
                </TableCell>
                <TableCell class="font-medium">
                  {{ conge.nom }}
                </TableCell>
                <TableCell class="font-medium">
                  {{ conge.dayReq }}
                </TableCell>
                <TableCell class="font-medium">
                  {{ conge.date }}
                </TableCell>
                <TableCell class="font-medium">
                  {{ conge.year }}
                </TableCell>
                <TableCell class="font-medium max-w-xs truncate" :title="conge.raison">
                  {{ conge.raison }}
                </TableCell>
                <TableCell>
                  <div class="flex items-center gap-2 w-max">
                    <Button
                      variant="outline"
                      size="icon"
                      class="size-8"
                      @click="handleEdit(conge.id)"
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
                          <AlertDialogTitle>Supprimer ce congé ?</AlertDialogTitle>
                          <AlertDialogDescription>
                            Cette action est irréversible. Si des données dépendent de ce congé, la
                            suppression sera refusée.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Annuler</AlertDialogCancel>
                          <Button
                            variant="destructive"
                            :disabled="deleting"
                            @click="handleDelete(conge.id)"
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
        v-if="!loadingConges && !errorConges"
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />
    </BoxPanelWrapper>
  </SaisieRhLayout>
</template>
