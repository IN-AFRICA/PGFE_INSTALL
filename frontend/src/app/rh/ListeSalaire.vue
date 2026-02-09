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
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog'
const router = useRouter()

// API Calls
const {
  data: salairesData,
  fetchData: fetchSalaires,
  loading: loadingSalaires,
  error: errorSalaires,
} = useGetApi(API_ROUTES.GET_SALAIRES)
const { data: personnelsData, fetchData: fetchPersonnels } = useGetApi(API_ROUTES.GET_PERSONALS)
const { data: schoolYearsData, fetchData: fetchSchoolYears } = useGetApi(
  API_ROUTES.GET_SCHOOL_YEARS,
)

// Search functionality
const { query } = useSearch((params: { search: string }) => {
  setAdditionalParams({ search: params.search })
  fetchSalaires({ page: page.value, per_page: perPageCount.value, ...params })
}, 500)

// Pagination functionality
const { page, perPageCount, total, setAdditionalParams } = usePagination(fetchSalaires, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

onMounted(async () => {
  await Promise.all([
    fetchSalaires({ page: page.value, per_page: perPageCount.value }),
    fetchPersonnels(),
    fetchSchoolYears(),
  ])
})

// Handle per page update
function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}

// Liste des mois
const moisNames = [
  '',
  'Janvier',
  'Février',
  'Mars',
  'Avril',
  'Mai',
  'Juin',
  'Juillet',
  'Août',
  'Septembre',
  'Octobre',
  'Novembre',
  'Décembre',
]

// Helper to resolve Personnel Name
const getPersonnelInfo = (id: number) => {
  const personnels = Array.isArray(personnelsData.value)
    ? personnelsData.value
    : (personnelsData.value as any)?.data || []

  const p = personnels.find((p: any) => p.id === id)
  return {
    name: p
      ? `${p.name || p.nom || ''} ${p.postnom || ''} ${p.firstname || p.prenom || ''}`.trim()
      : 'Inconnu',
    matricule: p?.matricule || 'N/A',
  }
}

// Helper to resolve School Year
const getSchoolYear = (id: number) => {
  const v: any = schoolYearsData.value
  const list = Array.isArray(v) ? v : (v?.years ?? v?.data ?? [])
  const year = list.find((y: any) => y.id === id)
  // Essayer plusieurs propriétés possibles pour le nom de l'année
  return (
    year?.name ||
    year?.year ||
    year?.libelle ||
    (year?.start_year ? `${year.start_year}-${year.end_year}` : 'N/A')
  )
}

// Formater le montant en devise
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('fr-CD', {
    style: 'currency',
    currency: 'CDF',
    minimumFractionDigits: 0,
  }).format(amount)
}

// Computed Rows
const rows = computed(() => {
  const data = Array.isArray(salairesData.value)
    ? salairesData.value
    : (salairesData.value as any)?.data || []

  return data.map((item: any) => {
    // Utiliser academic_personal_id si disponible, sinon personal_id
    const personnelId = item.academic_personal_id || item.personal_id
    const personnelInfo = getPersonnelInfo(Number(personnelId))

    return {
      id: item.id,
      matricule: personnelInfo.matricule,
      nom: personnelInfo.name,
      schoolYear: getSchoolYear(Number(item.school_year_id)),
      mois: moisNames[item.mois_id] || `Mois ${item.mois_id}`,
      montant: item.montant || 0,
      description: item.description || '-',
      date: item.created_at ? new Date(item.created_at).toLocaleDateString('fr-FR') : 'N/A',
    }
  })
})

// Delete functionality
const { deleteItem, deleting } = useDeleteApi()

// Handle edit
function handleEdit(id: number) {
  router.push(`/rh/saisie/salaire/modifier-salaire/${id}`)
}

// Handle delete
async function handleDelete(id: number) {
  try {
    await deleteItem(API_ROUTES.DELETE_SALAIRE(id))

    showCustomToast({
      message: 'Paiement supprimé avec succès !',
      type: 'success',
    })

    // Reload the list with current params
    await fetchSalaires({
      page: page.value,
      per_page: perPageCount.value,
      search: query.value,
    })
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    showCustomToast({
      message: 'Erreur lors de la suppression du paiement.',
      type: 'error',
    })
  }
}
</script>

<template>
  <SaisieRhLayout activeBread="Salaires" active-tag-name="salaire" group="saisie">
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
            <RouterLink to="/rh/saisie/salaire/nouveau-salaire">
              <span class="flex iconify hugeicons--plus-sign"></span>
              <span class="hidden sm:flex">Nouveau Salaire</span>
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
                                Exporter PDF
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
              <TableHead>Matricule</TableHead>
              <TableHead>Nom du personnel</TableHead>
              <TableHead>Année scolaire</TableHead>
              <TableHead>Mois</TableHead>
              <TableHead>Montant</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>Date</TableHead>
              <TableHead> Opérations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="loadingSalaires">
              <TableRow>
                <TableCell colspan="9" class="text-center py-8"> Chargement... </TableCell>
              </TableRow>
            </template>
            <template v-else-if="rows.length === 0">
              <TableRow>
                <TableCell colspan="9" class="text-center py-8 text-gray-500">
                  Aucun paiement de salaire trouvé.
                </TableCell>
              </TableRow>
            </template>
            <template v-else>
              <TableRow v-for="item in rows" :key="item.id">
                <TableCell class="w-[40px]">
                  <Checkbox class="bg-white scale-70" />
                </TableCell>
                <TableCell class="font-medium">{{ item.matricule }}</TableCell>
                <TableCell class="font-medium">{{ item.nom }}</TableCell>
                <TableCell>{{ item.schoolYear }}</TableCell>
                <TableCell>{{ item.mois }}</TableCell>
                <TableCell class="font-semibold text-green-700">{{
                  formatCurrency(item.montant)
                }}</TableCell>
                <TableCell class="max-w-xs truncate" :title="item.description">{{
                  item.description
                }}</TableCell>
                <TableCell>{{ item.date }}</TableCell>

                <TableCell>
                  <div class="flex items-center gap-2 w-max">
                    <Button
                      variant="outline"
                      size="icon"
                      class="size-8"
                      @click="handleEdit(item.id)"
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
                          <AlertDialogTitle>Supprimer ce salaire?</AlertDialogTitle>
                          <AlertDialogDescription>
                            Action irréversible. Si des éléments dépendent de ce salaire, la
                            suppression peut être refusée.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Annuler</AlertDialogCancel>
                          <Button
                            variant="destructive"
                            :disabled="deleting"
                            @click="handleDelete(item.id)"
                          >
                            <span
                              v-if="deleting"
                              class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-white"
                            ></span>
                            <span v-else class="iconify hugeicons--delete-02"></span>
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
        v-if="!loadingSalaires && !errorSalaires"
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />
    </BoxPanelWrapper>
  </SaisieRhLayout>
</template>
