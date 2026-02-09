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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { ref, computed, onMounted, watch, watchEffect } from 'vue'
import { useRoute } from 'vue-router'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { usePostApi } from '@/composables/usePostApi'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import { toSqlDatetime } from '@/utils/utils'
import { usePutApi } from '@/composables/usePutApi'

const route = useRoute()

const {
  data: amortissementData,
  loading: amortissementLoading,
  error: amortissementError,
  fetchData: fetchAmortissementEntries,
} = useGetApi<Amortissement[]>(API_ROUTES.GET_AMORTISSEMENT)
const isOnJournalPage = computed(() => route.path.includes('/comptabilite/journal'))
const isOnAmortissementsPage = computed(() => route.path.includes('/comptabilite/amortissements'))
const isOnDepensesPage = computed(() => route.path.includes('/comptabilite/depenses'))

interface Amortissement {
  id: number
  name: string
  code: string
  model: string
  amount: number
  purchase_date: string
  number_years: number
  immo_account_id: number
  immo_sub_account_id: number
  school_id: number
  user_id: number
}

const searchQuery = ref('')
const page = ref(1)
const perPage = ref(15)
const selectedItems = ref<number[]>([])
const isEditDialogOpen = ref(false)
const editingItem = ref<Amortissement | null>(null)

const formData = ref({
  asset_name: '',
  asset_code: '',
  acquisition_date: '',
  original_value: 0,
  duration_years: 5,
  method: 'lineaire' as 'lineaire' | 'degressif',
  account_code: '',
})

const reactiveAmortissementData = ref<Amortissement[]>([])

watchEffect(() => {
  reactiveAmortissementData.value = amortissementData.value
})

const filteredItems = computed(() => {
  if (amortissementData.value === null) return []
  if (!searchQuery.value) return amortissementData.value
  const query = searchQuery.value.toLowerCase()
  return amortissementData.value.filter(
    (item: Amortissement) =>
      item.name?.toLowerCase().includes(query) || item.code?.toLowerCase().includes(query),
  )
})

const paginatedItems = computed(() => {
  const start = (page.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredItems.value.slice(start, end)
})

const total = computed(() => filteredItems.value.length)

const calculateAnnualDepreciation = (
  originalValue: number,
  durationYears: number,
  method: string,
) => {
  if (method === 'lineaire') {
    return originalValue / durationYears
  } else {
    return (originalValue * 2.5) / durationYears
  }
}

const resetForm = () => {
  formData.value = {
    asset_name: '',
    asset_code: '',
    acquisition_date: '',
    original_value: 0,
    duration_years: 5,
    method: 'lineaire',
    account_code: '',
  }
}

const openEditDialog = (item: Amortissement) => {
  editingItem.value = item
  formData.value = {
    asset_name: item.name,
    asset_code: item.code,
    acquisition_date: item.purchase_date.split('T')[0],
    original_value: item.amount,
    duration_years: item.number_years,
    method: item.model as 'lineaire' | 'degressif',
    account_code: item.immo_account_id.toString() || '',
  }
  isEditDialogOpen.value = true
}

const onSubmitEdit = async () => {
  if (amortissementData.value === null) return
  if (!editingItem.value) return

  try {
    // Préparer les données pour l'API
    const payload = {
      name: formData.value.asset_name,
      code: formData.value.asset_code,
      model: formData.value.method,
      amount: formData.value.original_value,
      purchase_date: toSqlDatetime(formData.value.acquisition_date),
      number_years: formData.value.duration_years,
      immo_account_id: formData.value.account_code
        ? parseInt(formData.value.account_code)
        : editingItem.value.immo_account_id,
      immo_sub_account_id: editingItem.value.immo_sub_account_id,
      school_id: editingItem.value.school_id,
      user_id: editingItem.value.user_id,
    }

    // Appel API
    const endpoint = API_ROUTES.UPDATE_AMORTISSEMENT(editingItem.value.id)
    await updateData(endpoint, payload)

    if (updateSuccess.value && updateResponse.value) {
      // Mettre à jour les données locales avec la réponse de l'API
      const index = amortissementData.value.findIndex(
        (i: Amortissement) => i.id === editingItem.value!.id,
      )
      if (index !== -1) {
        amortissementData.value[index] = updateResponse.value
      }

      showCustomToast({ message: 'Amortissement modifié avec succès', type: 'success' })
      isEditDialogOpen.value = false
      editingItem.value = null
      resetForm()
    } else if (updateError.value) {
      showCustomToast({ message: updateError.value, type: 'error' })
    }
  } catch (error) {
    showCustomToast({ message: "Erreur lors de la modification de l'amortissement", type: 'error' })
  }
}
// API pour supprimer un amortissement
const { deleteItem, deleting } = useDeleteApi()

// API pour modifier un amortissement
const {
  loading: updating,
  error: updateError,
  response: updateResponse,
  putData: updateData,
  success: updateSuccess,
} = usePutApi<Amortissement>()

const deleteAmortissement = async (id: number) => {
  try {
    const endpoint = API_ROUTES.DELETE_AMORTISSEMENT(id)
    const result = await deleteItem(endpoint)
    if (result === null) throw new Error('Suppresion échoué')

    showCustomToast({
      message: 'Amortissement supprimé avec succès',
      type: 'success',
    })

    // Rafraîchir la liste
    await fetchAmortissementEntries({ page: page.value, perPage: perPage.value })
  } catch (error) {
    showCustomToast({
      message: "Erreur lors de la suppression de l'amortissement",
      type: 'error',
    })
  }
}

const deleteSelectedItems = () => {
  if (selectedItems.value.length === 0) {
    showCustomToast({ message: 'Aucun amortissement sélectionné', type: 'error' })
    return
  }
  const count = selectedItems.value.length
  if (!confirm(`Supprimer ${count} amortissement(s) ?`)) return
  reactiveAmortissementData.value = reactiveAmortissementData.value.filter(
    (i: Amortissement) => !selectedItems.value.includes(i.id),
  )
  selectedItems.value = []
  showCustomToast({ message: `${count} amortissement(s) supprimé(s)`, type: 'success' })
}

const toggleSelectAll = () => {
  if (selectedItems.value.length === paginatedItems.value.length) {
    selectedItems.value = []
  } else {
    selectedItems.value = paginatedItems.value.map((i: Amortissement) => i.id)
  }
}

const formatDate = (dateString?: string) => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleDateString('fr-FR')
  } catch {
    return dateString
  }
}

const formatAmount = (amount: number) => {
  if (!amount && amount !== 0) return '-'
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'CDF',
    minimumFractionDigits: 2,
  }).format(amount)
}

const formatPercentage = (value: number, total: number) => {
  if (!total) return '0%'
  return `${((value / total) * 100).toFixed(1)}%`
}

onMounted(() => {
  fetchAmortissementEntries({ page: page.value, perPage: perPage.value })
})

watch([page, perPage], () => {
  fetchAmortissementEntries({ page: page.value, perPage: perPage.value })
})
</script>

<template>
  <ComptaLayout activeBread="amortissements" active-tag-name="amortissements" group="operations">
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            v-model="searchQuery"
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher par nom, code, compte..."
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <Button
            v-if="selectedItems.length > 0"
            variant="destructive"
            size="md"
            @click="deleteSelectedItems"
          >
            <span class="iconify hugeicons--delete-02 mr-1"></span>
            Supprimer ({{ selectedItems.length }})
          </Button>
          <RouterLink to="/comptabilite/saisie-operations/nouvel-amortissement">
            <Button size="md">
              <span class="iconify hugeicons--add-01 mr-2"></span>
              Nouvel amortissement
            </Button>
          </RouterLink>
          <!-- <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="md" class="bg-white border border-border rounded-md">
                Exporter
                <span class="iconify hugeicons--arrow-down-01" />
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
      <!-- État de chargement -->
      <div
        v-if="amortissementLoading"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
      >
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--loading-03 animate-spin text-4xl text-blue-500"></span>
          <p class="text-sm text-foreground-muted">Chargement des écritures de journal...</p>
        </div>
      </div>

      <!-- État d'erreur -->
      <div
        v-else-if="amortissementError"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
      >
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--alert-circle text-4xl text-red-500"></span>
          <p class="text-sm text-foreground-muted">{{ amortissementError }}</p>
          <Button @click="() => {}" size="sm" variant="outline">
            <span class="iconify hugeicons--refresh mr-1"></span>
            Réessayer
          </Button>
        </div>
      </div>

      <!-- Liste vide -->
      <div
        v-if="!paginatedItems || paginatedItems.length === 0"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
      >
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--calculator text-4xl text-foreground-muted/50"></span>
          <p class="text-sm text-foreground-muted">
            {{
              searchQuery
                ? 'Aucun amortissement trouvé pour cette recherche'
                : 'Aucun amortissement enregistré'
            }}
          </p>
        </div>
      </div>

      <!-- Tableau des amortissements -->
      <div v-else class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox
                  :checked="
                    selectedItems.length === paginatedItems.length && paginatedItems.length > 0
                  "
                  @update:checked="toggleSelectAll"
                />
              </TableHead>
              <TableHead>Immobilisation</TableHead>
              <TableHead>Code</TableHead>
              <TableHead>Date acquisition</TableHead>
              <TableHead class="text-right">Valeur origine</TableHead>
              <TableHead class="text-center">Durée (ans)</TableHead>
              <TableHead>Méthode</TableHead>
              <TableHead class="text-right">Dotation annuelle</TableHead>
              <TableHead class="text-right">Cumul amort.</TableHead>
              <TableHead class="text-right">VNC</TableHead>
              <TableHead class="text-center">% amort.</TableHead>
              <TableHead class="text-center">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in paginatedItems" :key="item.id">
              <TableCell>
                <Checkbox
                  :checked="selectedItems.includes(item.id)"
                  @update:checked="
                    (checked: any) => {
                      if (checked) {
                        selectedItems.push(item.id)
                      } else {
                        selectedItems = selectedItems.filter((id) => id !== item.id)
                      }
                    }
                  "
                />
              </TableCell>
              <TableCell class="font-medium">{{ item.name }}</TableCell>
              <TableCell>
                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">{{
                  item.code
                }}</span>
              </TableCell>
              <TableCell>{{ formatDate(item.purchase_date) }}</TableCell>
              <TableCell class="text-right font-medium">{{ formatAmount(item.amount) }}</TableCell>
              <TableCell class="text-center">{{ item.number_years }}</TableCell>
              <TableCell>
                <span
                  :class="
                    item.model === 'lineaire'
                      ? 'bg-green-100 text-green-700'
                      : 'bg-purple-100 text-purple-700'
                  "
                  class="px-2 py-1 rounded text-xs capitalize"
                >
                  {{ item.model }}
                </span>
              </TableCell>
              <TableCell class="text-right">{{ formatAmount(item.annual_depreciation) }}</TableCell>
              <TableCell class="text-right text-orange-600">{{
                formatAmount(item.cumulative_depreciation)
              }}</TableCell>
              <TableCell class="text-right font-semibold text-blue-600">{{
                formatAmount(item.net_book_value)
              }}</TableCell>
              <TableCell class="text-center">
                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                  {{ formatPercentage(item.cumulative_depreciation, item.original_value) }}
                </span>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <Button
                    variant="outline"
                    size="icon"
                    @click="openEditDialog(item)"
                    class="size-8"
                  >
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>
                  <AlertMessage
                    action="danger"
                    title="Supprimer un amortissement"
                    :message="`Vous êtes sur le point de supprimer l'amortissement '${item.name}'. Êtes-vous sûr de continuer?`"
                  >
                    <template #trigger>
                      <Button variant="outline" size="icon" class="size-8">
                        <span class="iconify hugeicons--delete-02"></span>
                      </Button>
                    </template>
                    <template #confirm-action-button>
                      <Button
                        variant="destructive"
                        size="sm"
                        class="h-10 px-4"
                        @click.stop="deleteAmortissement(item.id)"
                        :disabled="deleting"
                      >
                        {{ deleting ? 'Suppression...' : 'Oui, Supprimer' }}
                      </Button>
                    </template>
                  </AlertMessage>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <TabPagination
        v-if="paginatedItems.length > 0"
        v-model="page"
        :perPage="perPage"
        :totalItems="total"
        @update:perPage="(val) => (perPage = val)"
        class="mt-4"
      />
    </BoxPanelWrapper>

    <!-- Dialog d'édition -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Modifier l'amortissement</DialogTitle>
          <DialogDescription>Modifier les informations de l'immobilisation</DialogDescription>
        </DialogHeader>
        <form @submit.prevent="onSubmitEdit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_asset_name">Nom de l'immobilisation *</Label>
              <Input id="edit_asset_name" v-model="formData.asset_name" required />
            </div>
            <div class="space-y-2">
              <Label for="edit_asset_code">Code *</Label>
              <Input id="edit_asset_code" v-model="formData.asset_code" required />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_acquisition_date">Date d'acquisition *</Label>
              <Input
                id="edit_acquisition_date"
                v-model="formData.acquisition_date"
                type="date"
                required
              />
            </div>
            <div class="space-y-2">
              <Label for="edit_original_value">Valeur d'origine (CDF) *</Label>
              <Input
                id="edit_original_value"
                v-model.number="formData.original_value"
                type="number"
                min="0"
                step="0.01"
                required
              />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_duration_years">Durée (années) *</Label>
              <Input
                id="edit_duration_years"
                v-model.number="formData.duration_years"
                type="number"
                min="1"
                required
              />
            </div>
            <div class="space-y-2">
              <Label for="edit_method">Méthode *</Label>
              <Select v-model="formData.method">
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="lineaire">Linéaire</SelectItem>
                  <SelectItem value="degressif">Dégressive</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          <div class="space-y-2">
            <Label for="edit_account_code">Code compte (optionnel)</Label>
            <Input id="edit_account_code" v-model="formData.account_code" placeholder="Ex: 2183" />
          </div>
          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              @click="isEditDialogOpen = false"
              :disabled="updating"
              >Annuler</Button
            >
            <Button type="submit" :disabled="updating">
              {{ updating ? 'Modification...' : 'Modifier' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </ComptaLayout>
</template>
