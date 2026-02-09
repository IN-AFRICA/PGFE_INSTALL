<script setup lang="ts">
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import ListCurrency from '@/utils/widgets/vues/ListCurrency.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { usePostApi } from '@/composables/usePostApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { onMounted, ref, computed } from 'vue'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { useForm, Field } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'

// Interface pour les taux de change
interface ExchangeRate {
  id: number
  currency_id: number
  rate: number
  date_effective?: string | null
  is_active: boolean
  created_at?: string
  updated_at?: string
  currency?: { name: string; code: string }
}

// Interface pour la réponse API
interface ExchangeRatesApiResponse {
  success: boolean
  message?: string
  exchange_rate?: string
  data?: ExchangeRate[]
}

// Schéma de validation Zod pour les taux de change
const exchangeRateSchema = z.object({
  currency_id: z
    .union([z.string().transform((val) => (val === '' ? null : Number(val))), z.number()])
    .nullable()
    .refine((val) => val !== null && val !== 0, 'La devise est requise'),
  rate: z.coerce.number().min(0, 'Le taux doit être supérieur ou égal à 0'),
  date_effective: z.string().optional().nullable(),
  is_active: z.boolean().default(true),
})

type ExchangeRateFormData = z.infer<typeof exchangeRateSchema>

// Variables réactives pour les formulaires
const searchQuery = ref('')
const selectedItems = ref<number[]>([])
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingItem = ref<ExchangeRate | null>(null)

// Configuration du formulaire avec Vee-Validate
const { handleSubmit, resetForm, errors, values, setFieldValue } = useForm<ExchangeRateFormData>({
  validationSchema: toTypedSchema(exchangeRateSchema),
})

// Composables pour les API
const {
  data: exchangeRatesData,
  error: exchangeRatesError,
  loading: exchangeRatesLoading,
  fetchData,
} = useGetApi<any>(API_ROUTES.GET_EXCHANGE_RATES)

// Computed pour normaliser les données des taux de change
const normalizedExchangeRates = computed<ExchangeRate[]>(() => {
  const v: any = exchangeRatesData.value
  if (!v) return []

  console.log('Gestion_taux_echange - Structure de réponse API:', v)

  // Cas 1: tableau direct
  if (Array.isArray(v)) {
    console.log('Gestion_taux_echange - Format tableau direct')
    return v
  }

  // Cas 2: { exchange_rates: [...] } ou { data: [...] }
  if (Array.isArray(v?.exchange_rates)) {
    console.log('Gestion_taux_echange - Format avec exchange_rates')
    return v.exchange_rates
  }
  if (Array.isArray(v?.data)) {
    console.log('Gestion_taux_echange - Format avec data')
    return v.data
  }

  // Cas 3: objet avec propriétés numériques (ids comme clés)
  if (v && typeof v === 'object') {
    const arr = Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && 'id' in item,
    )
    if (arr.length > 0) {
      console.log('Gestion_taux_echange - Format objet avec IDs comme clés')
      return arr as ExchangeRate[]
    }
  }

  console.log('Gestion_taux_echange - Format non reconnu, retour tableau vide')
  return []
})
const {
  postData: createExchangeRate,
  loading: createLoading,
  error: createError,
  response: createResponse,
} = usePostApi<ExchangeRatesApiResponse>()
const {
  putData: updateExchangeRate,
  loading: updateLoading,
  error: updateError,
  response: updateResponse,
} = usePutApi<ExchangeRatesApiResponse>()
const { deleteItem: deleteExchangeRate, deleting: deleteLoading } =
  useDeleteApi<ExchangeRatesApiResponse>()

// Computed pour filtrer les données
const filteredExchangeRates = computed(() => {
  if (!normalizedExchangeRates.value) return []

  if (!searchQuery.value) {
    return normalizedExchangeRates.value
  }

  const query = searchQuery.value.toLowerCase()
  return normalizedExchangeRates.value.filter(
    (rate) =>
      (rate.currency?.name && rate.currency.name.toLowerCase().includes(query)) ||
      (rate.currency?.code && rate.currency.code.toLowerCase().includes(query)) ||
      rate.rate.toString().includes(query),
  )
})

// Gestion de la sélection
const isAllSelected = computed(() => {
  return (
    filteredExchangeRates.value.length > 0 &&
    selectedItems.value.length === filteredExchangeRates.value.length
  )
})

// Computed pour vérifier si des données sont disponibles
const hasData = computed(() => {
  return normalizedExchangeRates.value && normalizedExchangeRates.value.length > 0
})

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedItems.value = []
  } else {
    selectedItems.value = filteredExchangeRates.value.map((rate) => rate.id)
  }
}

const toggleSelectItem = (id: number) => {
  const index = selectedItems.value.indexOf(id)
  if (index > -1) {
    selectedItems.value.splice(index, 1)
  } else {
    selectedItems.value.push(id)
  }
}

// Fonctions pour les opérations CRUD
const openCreateDialog = () => {
  resetForm()
  setFieldValue('is_active', true)
  isCreateDialogOpen.value = true
}

const openEditDialog = (rate: ExchangeRate) => {
  editingItem.value = rate
  setFieldValue('currency_id', rate.currency_id)
  setFieldValue('rate', rate.rate)
  setFieldValue('date_effective', rate.date_effective || '')
  setFieldValue('is_active', rate.is_active)
  isEditDialogOpen.value = true
}

const onSubmitCreate = handleSubmit(async (values: ExchangeRateFormData) => {
  try {
    // Préparer les données pour l'API
    const apiData = {
      currency_id: values.currency_id,
      rate: values.rate,
      date_effective: values.date_effective || null,
      is_active: values.is_active,
    }

    await createExchangeRate(API_ROUTES.CREATE_EXCHANGE_RATE, apiData)

    if (createError.value) {
      showCustomToast({ message: createError.value, type: 'error' })
      return
    }

    if (createResponse.value && createResponse.value.success) {
      showCustomToast({
        message: createResponse.value.message || 'Taux de change créé avec succès',
        type: 'success',
      })
      resetForm()
      isCreateDialogOpen.value = false
      await fetchData()
    } else {
      showCustomToast({ message: 'Erreur lors de la création du taux de change', type: 'error' })
    }
  } catch (err) {
    console.error('Erreur lors de la création:', err)
    showCustomToast({ message: 'Erreur lors de la création du taux de change', type: 'error' })
  }
})

const onSubmitEdit = handleSubmit(async (values: ExchangeRateFormData) => {
  if (!editingItem.value) return

  try {
    // Préparer les données pour l'API
    const apiData = {
      currency_id: values.currency_id,
      rate: values.rate,
      date_effective: values.date_effective || null,
      is_active: values.is_active,
    }

    await updateExchangeRate(API_ROUTES.UPDATE_EXCHANGE_RATE(editingItem.value.id), apiData)

    if (updateError.value) {
      showCustomToast({ message: updateError.value, type: 'error' })
      return
    }

    if (updateResponse.value && updateResponse.value.success) {
      showCustomToast({
        message: updateResponse.value.message || 'Taux de change modifié avec succès',
        type: 'success',
      })
      resetForm()
      isEditDialogOpen.value = false
      editingItem.value = null
      await fetchData()
    } else {
      showCustomToast({
        message: 'Erreur lors de la modification du taux de change',
        type: 'error',
      })
    }
  } catch (err) {
    console.error('Erreur lors de la modification:', err)
    showCustomToast({ message: 'Erreur lors de la modification du taux de change', type: 'error' })
  }
})

const deleteSelectedItems = async () => {
  if (selectedItems.value.length === 0) return

  const itemNames = selectedItems.value
    .map((id) => {
      const rate = filteredExchangeRates.value.find((r) => r.id === id)
      return rate ? `${rate.currency?.name || 'Devise'} (${formatRate(rate.rate)})` : null
    })
    .filter(Boolean)
    .join(', ')

  if (!confirm(`Êtes-vous sûr de vouloir supprimer ces taux de change : ${itemNames} ?`)) {
    return
  }

  try {
    for (const id of selectedItems.value) {
      await deleteExchangeRate(API_ROUTES.DELETE_EXCHANGE_RATE(id))
    }

    showCustomToast({
      message: `${selectedItems.value.length} taux de change supprimé(s) avec succès`,
      type: 'success',
    })
    selectedItems.value = []
    await fetchData()
  } catch (err) {
    console.error('Erreur lors de la suppression:', err)
    showCustomToast({ message: 'Erreur lors de la suppression des taux de change', type: 'error' })
  }
}

const deleteSingleItem = async (rate: ExchangeRate) => {
  const rateName = `${rate.currency?.name || 'Devise'} (${formatRate(rate.rate)})`
  if (!confirm(`Êtes-vous sûr de vouloir supprimer le taux de change "${rateName}" ?`)) {
    return
  }

  try {
    await deleteExchangeRate(API_ROUTES.DELETE_EXCHANGE_RATE(rate.id))
    showCustomToast({ message: 'Taux de change supprimé avec succès', type: 'success' })
    await fetchData()
  } catch (err) {
    console.error('Erreur lors de la suppression:', err)
    showCustomToast({ message: 'Erreur lors de la suppression du taux de change', type: 'error' })
  }
}

// Fonctions utilitaires
const formatDate = (dateString?: string | null) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR')
}

const formatRate = (rate: number) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 6,
  }).format(rate)
}

// Charger les données au montage du composant
onMounted(async () => {
  console.log('Gestion_taux_echange - Chargement des données...')
  await fetchData()
  console.log('Gestion_taux_echange - Données chargées:', {
    data: exchangeRatesData.value,
    error: exchangeRatesError.value,
    loading: exchangeRatesLoading.value,
  })

  if (exchangeRatesError.value) {
    console.error('Gestion_taux_echange - Erreur API:', exchangeRatesError.value)
  }

  if (exchangeRatesData.value) {
    console.log('Gestion_taux_echange - Nombre de taux récupérés:', exchangeRatesData.value.length)
  }
})
</script>

<template>
  <ComptaLayout activeBread="Taux de Change" active-tag-name="taux-de-change" group="frais">
    <BoxPanelWrapper>
      <!-- Barre d'outils -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-4">
          <!-- Recherche -->
          <div class="relative">
            <Input
              v-model="searchQuery"
              placeholder="Rechercher par devise ou taux..."
              class="pl-10 w-80"
            />
            <span
              class="iconify hugeicons--search-01 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
            ></span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Bouton de suppression multiple -->
          <Button
            v-if="selectedItems.length > 0"
            @click="deleteSelectedItems"
            variant="destructive"
            size="sm"
            :disabled="deleteLoading"
          >
            <span class="iconify hugeicons--delete-02 mr-2"></span>
            Supprimer ({{ selectedItems.length }})
          </Button>

          <!-- Bouton de création -->
          <Dialog v-model:open="isCreateDialogOpen">
            <DialogTrigger asChild>
              <Button @click="openCreateDialog" size="sm">
                <span class="iconify hugeicons--add-01 mr-2"></span>
                Nouveau taux
              </Button>
            </DialogTrigger>
            <DialogContent class="max-w-md">
              <DialogHeader>
                <DialogTitle>Créer un nouveau taux de change</DialogTitle>
                <DialogDescription>
                  Définissez un nouveau taux de change pour une devise.
                </DialogDescription>
              </DialogHeader>
              <form @submit.prevent="onSubmitCreate" class="space-y-4">
                <!-- Devise -->
                <div class="space-y-2">
                  <Label for="currency_id">Devise *</Label>
                  <Field name="currency_id" v-slot="{ field }">
                    <ListCurrency :model-value="field.value" @update:model-value="field.onChange" />
                  </Field>
                  <p v-if="errors.currency_id" class="text-sm text-red-500">
                    {{ errors.currency_id }}
                  </p>
                </div>

                <!-- Taux -->
                <div class="space-y-2">
                  <Label for="rate">Taux de change *</Label>
                  <Field name="rate" v-slot="{ field }">
                    <Input
                      v-bind="field"
                      type="number"
                      step="0.000001"
                      min="0"
                      placeholder="Ex: 1.25"
                    />
                  </Field>
                  <p v-if="errors.rate" class="text-sm text-red-500">{{ errors.rate }}</p>
                </div>

                <!-- Date d'effet -->
                <div class="space-y-2">
                  <Label for="date_effective">Date d'effet</Label>
                  <Field name="date_effective" v-slot="{ field }">
                    <Input v-bind="field" type="datetime-local" />
                  </Field>
                  <p v-if="errors.date_effective" class="text-sm text-red-500">
                    {{ errors.date_effective }}
                  </p>
                </div>

                <!-- Actif -->
                <div class="flex items-center space-x-2">
                  <Field name="is_active" v-slot="{ field }">
                    <Checkbox
                      :checked="field.value"
                      @update:checked="field.onChange"
                      id="is_active"
                    />
                  </Field>
                  <Label for="is_active">Taux actif</Label>
                </div>

                <DialogFooter>
                  <Button type="button" @click="isCreateDialogOpen = false" variant="outline">
                    Annuler
                  </Button>
                  <Button type="submit" :disabled="createLoading">
                    <span
                      v-if="createLoading"
                      class="iconify hugeicons--loading-03 mr-2 animate-spin"
                    ></span>
                    Créer
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>
      </div>

      <!-- Tableau des taux de change -->
      <div class="border rounded-lg overflow-hidden flex flex-1 bg-white">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[50px]">
                <Checkbox :checked="isAllSelected" @update:checked="toggleSelectAll" />
              </TableHead>
              <TableHead class="w-[250px]">Devise</TableHead>
              <TableHead class="w-[150px]">Taux</TableHead>
              <TableHead class="w-[120px]">Statut</TableHead>
              <TableHead class="w-[150px]">Créé le</TableHead>
              <TableHead class="w-[100px] text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <!-- État de chargement -->
            <TableRow v-if="exchangeRatesLoading">
              <TableCell colspan="7" class="text-center py-8">
                <div class="flex items-center justify-center">
                  <span class="iconify hugeicons--loading-03 mr-2 animate-spin"></span>
                  Chargement des taux de change...
                </div>
              </TableCell>
            </TableRow>

            <!-- État d'erreur -->
            <TableRow v-else-if="exchangeRatesError">
              <TableCell colspan="7" class="text-center py-8">
                <div class="flex items-center justify-center text-red-500">
                  <span class="iconify hugeicons--alert-triangle mr-2"></span>
                  Erreur lors du chargement des taux de change
                </div>
              </TableCell>
            </TableRow>

            <!-- Liste vide -->
            <TableRow v-else-if="!filteredExchangeRates.length">
              <TableCell colspan="7" class="text-center py-8">
                <div class="flex flex-col items-center justify-center text-gray-500">
                  <span class="iconify hugeicons--exchange-01 text-4xl mb-2"></span>
                  <p v-if="searchQuery">Aucun taux de change ne correspond à votre recherche.</p>
                  <p v-else>Aucun taux de change défini pour le moment.</p>
                </div>
              </TableCell>
            </TableRow>

            <!-- Données -->
            <TableRow v-else v-for="rate in filteredExchangeRates" :key="rate.id">
              <TableCell>
                <Checkbox
                  :checked="selectedItems.includes(rate.id)"
                  @update:checked="() => toggleSelectItem(rate.id)"
                />
              </TableCell>
              <TableCell>
                <div class="flex items-center">
                  <Badge variant="outline" class="mr-2">
                    {{ rate.currency?.code || 'N/A' }}
                  </Badge>
                  {{ rate.currency?.name || 'Devise inconnue' }}
                </div>
              </TableCell>
              <TableCell class="font-mono">{{ formatRate(rate.rate) }}</TableCell>
              <TableCell>
                <Badge :variant="rate.is_active ? 'default' : 'secondary'">
                  {{ rate.is_active ? 'Actif' : 'Inactif' }}
                </Badge>
              </TableCell>
              <TableCell>{{ formatDate(rate.created_at) }}</TableCell>
              <TableCell class="text-right">
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="sm">
                      <span class="iconify hugeicons--more-horizontal"></span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="openEditDialog(rate)">
                      <span class="iconify hugeicons--edit-02 mr-2"></span>
                      Modifier
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="deleteSingleItem(rate)" class="text-red-600">
                      <span class="iconify hugeicons--delete-02 mr-2"></span>
                      Supprimer
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </BoxPanelWrapper>

    <!-- Dialog de modification -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Modifier le taux de change</DialogTitle>
          <DialogDescription>
            Modifiez les informations du taux de change sélectionné.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="onSubmitEdit" class="space-y-4">
          <!-- Devise -->
          <div class="space-y-2">
            <Label for="currency_id">Devise *</Label>
            <Field name="currency_id" v-slot="{ field }">
              <ListCurrency :model-value="field.value" @update:model-value="field.onChange" />
            </Field>
            <p v-if="errors.currency_id" class="text-sm text-red-500">{{ errors.currency_id }}</p>
          </div>
          <!-- Taux -->
          <div class="space-y-2">
            <Label for="rate">Taux de change *</Label>
            <Field name="rate" v-slot="{ field }">
              <Input v-bind="field" type="number" step="0.000001" min="0" placeholder="Ex: 1.25" />
            </Field>
            <p v-if="errors.rate" class="text-sm text-red-500">{{ errors.rate }}</p>
          </div>

          <!-- Date d'effet -->
          <div class="space-y-2">
            <Label for="date_effective">Date d'effet</Label>
            <Field name="date_effective" v-slot="{ field }">
              <Input v-bind="field" type="datetime-local" />
            </Field>
            <p v-if="errors.date_effective" class="text-sm text-red-500">
              {{ errors.date_effective }}
            </p>
          </div>

          <!-- Actif -->
          <div class="flex items-center space-x-2">
            <Field name="is_active" v-slot="{ field }">
              <Checkbox
                :checked="field.value"
                @update:checked="field.onChange"
                id="is_active_edit"
              />
            </Field>
            <Label for="is_active_edit">Taux actif</Label>
          </div>

          <DialogFooter>
            <Button type="submit" :disabled="updateLoading">
              <span
                v-if="updateLoading"
                class="iconify hugeicons--loading-03 mr-2 animate-spin"
              ></span>
              <span v-else class="iconify hugeicons--checkmark-circle-02 mr-2"></span>
              Modifier
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </ComptaLayout>
</template>
