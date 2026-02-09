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
import { ref, onMounted, computed } from 'vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { extractSuccessMessage, isApiSuccess } from '../comptabilite/prealables/utils'
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

// API Calls
const {
  data: affectationsData,
  fetchData: fetchAffectations,
  loading: loadingAffectations,
  error: errorAffectations,
} = useGetApi(API_ROUTES.GET_PERSONNEL_AFFECTATION)
const { data: personnelsData, fetchData: fetchPersonnels } = useGetApi(API_ROUTES.GET_PERSONALS)
const { data: schoolYearsData, fetchData: fetchSchoolYears } = useGetApi(
  API_ROUTES.GET_SCHOOL_YEARS,
)

// Delete API
const { deleteItem, deleting, errorDelete: delError, deleteResponse } = useDeleteApi<any>()
const deletingId = ref<number | null>(null)

const onDeleteAffectation = async (id: number) => {
  deletingId.value = id
  try {
    await deleteItem(API_ROUTES.DELETE_PERSONNEL_AFFECTATION(id))

    if (delError.value) {
      showCustomToast({ message: delError.value, type: 'error' })
      deletingId.value = null
      return
    }

    if (isApiSuccess(deleteResponse.value, delError.value)) {
      const message = extractSuccessMessage(
        deleteResponse.value,
        'Affectation supprimée avec succès',
      )
      showCustomToast({ message, type: 'success' })
      await fetchAffectations({
        page: page.value,
        per_page: perPageCount.value,
        search: query.value,
      })
    }
  } catch (err) {
    console.error('Erreur lors de la suppression:', err)
    showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
  }
  deletingId.value = null
}

// Search functionality
const { query } = useSearch((params: { search: string }) => {
  setAdditionalParams({ search: params.search })
  fetchAffectations({ page: page.value, per_page: perPageCount.value, ...params })
}, 500)

// Pagination
const { page, perPageCount, total, setAdditionalParams } = usePagination(fetchAffectations, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

// Charger les données au montage
onMounted(async () => {
  await Promise.all([
    fetchAffectations({ page: page.value, per_page: perPageCount.value }),
    fetchPersonnels(),
    fetchSchoolYears(),
  ])
})

// Function to update perPage
function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}

// Helper to resolve Personnel Name
const getPersonnelName = (id: number) => {
  const personnels = Array.isArray(personnelsData.value)
    ? personnelsData.value
    : (personnelsData.value as any)?.data || []

  const p = personnels.find((p: any) => p.id === Number(id))
  return p
    ? `${p.name || p.nom || ''} ${p.postnom || ''} ${p.firstname || p.prenom || ''}`.trim()
    : 'Inconnu'
}

// Helper to resolve School Year
const getSchoolYearLabel = (id: number) => {
  const v: any = schoolYearsData.value
  const years = Array.isArray(v) ? v : (v?.years ?? [])
  const y = years.find((y: any) => y.id === Number(id))
  return y ? y.name || y.label || 'N/A' : 'N/A'
}

// Computed Rows
const rows = computed(() => {
  const data = Array.isArray(affectationsData.value)
    ? affectationsData.value
    : (affectationsData.value as any)?.data || []

  return data.map((item: any) => ({
    id: item.id,
    date: item.created_at ? new Date(item.created_at).toLocaleDateString('fr-FR') : 'N/A',
    annee: getSchoolYearLabel(item.school_year_id),
    personnel: getPersonnelName(item.academic_personal_id),
    lieu: item.lieu_affectation || 'N/A',
    duree: item.durree_jours ? `${item.durree_jours} jours` : 'N/A',
    description: item.description || '-',
  }))
})
</script>

<template>
  <SaisieRhLayout
    activeBread="Mise en place"
    active-tag-name="mise-en-place-personnel"
    group="saisie"
  >
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher une affectation..."
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
              to="/rh/saisie/mise-en-place-personnel/nouvelle-affectation"
              class="rounded-md max-sm:flex-1 sm:w-max flex items-center gap-2"
            >
              <span class="flex iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Nouvelle affectation</span>
            </RouterLink>
          </Button>
        </div>
      </div>
      <div class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox class="bg-white scale-70" />
              </TableHead>
              <TableHead>Date création</TableHead>
              <TableHead>Année Scolaire</TableHead>
              <TableHead>Personnel</TableHead>
              <TableHead>Lieu d'affectation</TableHead>
              <TableHead>Durée</TableHead>
              <TableHead>Description</TableHead>
              <TableHead> Opérations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="loadingAffectations">
              <TableRow>
                <TableCell colspan="8" class="text-center py-8"> Chargement... </TableCell>
              </TableRow>
            </template>
            <template v-else-if="rows.length === 0">
              <TableRow>
                <TableCell colspan="8" class="text-center py-8 text-gray-500">
                  Aucune affectation trouvée.
                </TableCell>
              </TableRow>
            </template>
            <template v-else>
              <TableRow v-for="item in rows" :key="item.id">
                <TableCell class="w-[40px]">
                  <Checkbox class="bg-white scale-70" />
                </TableCell>
                <TableCell>{{ item.date }}</TableCell>
                <TableCell>{{ item.annee }}</TableCell>
                <TableCell>{{ item.personnel }}</TableCell>
                <TableCell>{{ item.lieu }}</TableCell>
                <TableCell>{{ item.duree }}</TableCell>
                <TableCell class="max-w-xs truncate" :title="item.description"
                  >{{ item.description }}
                </TableCell>

                <TableCell>
                  <div class="flex items-center gap-2 w-max">
                    <Button variant="outline" size="icon" class="size-8">
                      <RouterLink
                        :to="`/rh/saisie/mise-en-place-personnel/nouvelle-affectation?mode=view&id=${item.id}`"
                        class="rounded-md max-sm:flex-1 sm:w-max flex items-center gap-2"
                        ><span class="iconify hugeicons--view"></span>
                      </RouterLink>
                    </Button>
                    <Button variant="outline" size="icon" class="size-8">
                      <RouterLink
                        :to="`/rh/saisie/mise-en-place-personnel/nouvelle-affectation?mode=edit&id=${item.id}`"
                        class="rounded-md max-sm:flex-1 sm:w-max flex items-center gap-2"
                        ><span class="iconify hugeicons--edit-02"></span>
                      </RouterLink>
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
                          <AlertDialogTitle>Supprimer cette affectation ?</AlertDialogTitle>
                          <AlertDialogDescription>
                            Cette action est irréversible. Si des données dépendent de cette
                            affectation, la suppression sera refusée.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Annuler</AlertDialogCancel>
                          <Button
                            variant="destructive"
                            :disabled="deleting && deletingId === item.id"
                            @click="onDeleteAffectation(item.id)"
                          >
                            <span
                              v-if="deleting && deletingId === item.id"
                              class="flex items-center gap-2"
                            >
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
        v-if="!loadingAffectations && !errorAffectations"
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />
    </BoxPanelWrapper>
  </SaisieRhLayout>
</template>
