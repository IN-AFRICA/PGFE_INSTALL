<script setup lang="ts">
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
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

// Interface pour les devises
interface Currency {
  id: number
  code: string
  name: string
  symbol?: string | null
  is_default: boolean
  created_at?: string
  updated_at?: string
}

// Interface pour la réponse API
interface CurrenciesApiResponse {
  success: boolean
  data?: Currency[]
  currencies?: Currency[]
  message?: string
}

// Schéma de validation Zod pour les devises
const currencySchema = z.object({
  code: z
    .string()
    .min(3, 'Le code doit faire exactement 3 caractères')
    .max(3, 'Le code doit faire exactement 3 caractères')
    .transform((val) => val.toUpperCase()),
  name: z
    .string()
    .min(1, 'Le nom est requis')
    .max(100, 'Le nom ne peut pas dépasser 100 caractères'),
  symbol: z.string().optional().nullable(),
  is_default: z.boolean().default(false),
})

type CurrencyFormData = z.infer<typeof currencySchema>

// Variables réactives pour les formulaires
const searchQuery = ref('')
const selectedItems = ref<number[]>([])
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingItem = ref<Currency | null>(null)

// Variables pour la sélection multiple
const selectedCurrencies = ref<number[]>([])

// APIs
const {
  data: currenciesData,
  loading,
  error,
  fetchData,
} = useGetApi<CurrenciesApiResponse>(API_ROUTES.GET_CURRENCIES)
const {
  postData: createCurrency,
  loading: createLoading,
  response: createResponse,
  success: createSuccess,
  error: createError,
} = usePostApi()
const { putData: updateCurrency, loading: updateLoading } = usePutApi()
const { deleteItem: deleteCurrency, deleting: deleteLoading } = useDeleteApi()

// Formulaire pour création/modification
const { handleSubmit, resetForm, setValues } = useForm({
  validationSchema: toTypedSchema(currencySchema),
})

// Liste normalisée des devises quelle que soit la structure de réponse
const normalizedCurrencies = computed<Currency[]>(() => {
  const v: any = currenciesData.value
  if (!v) return []

  // Cas 1: tableau direct
  if (Array.isArray(v)) return v

  // Cas 2: { currencies: [...] } ou { data: [...] }
  if (Array.isArray(v?.currencies)) return v.currencies
  if (Array.isArray(v?.data)) return v.data

  // Cas 3: objet avec propriétés numériques (ids comme clés)
  if (v && typeof v === 'object') {
    const arr = Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && 'id' in item,
    )
    if (arr.length > 0) return arr as Currency[]
  }

  return []
})

// Computed pour les devises filtrées
const filteredCurrencies = computed(() => {
  if (!normalizedCurrencies.value || normalizedCurrencies.value.length === 0) return []

  if (!searchQuery.value) {
    return normalizedCurrencies.value
  }

  const query = searchQuery.value.toLowerCase()
  return normalizedCurrencies.value.filter(
    (currency) =>
      currency.code.toLowerCase().includes(query) ||
      currency.name.toLowerCase().includes(query) ||
      (currency.symbol && currency.symbol.toLowerCase().includes(query)),
  )
})

// Fonction pour formater le statut par défaut
const formatDefaultStatus = (isDefault: boolean) => {
  return isDefault ? 'Oui' : 'Non'
}

// Fonction pour formater la date
const formatDate = (dateString?: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR')
}

// Fonction pour créer une nouvelle devise
const onSubmitCreate = handleSubmit(async (values: CurrencyFormData) => {
  try {
    console.log('🔍 Données envoyées pour création de devise:', values)
    console.log('🔍 Route utilisée:', API_ROUTES.CREATE_CURRENCY)

    await createCurrency(API_ROUTES.CREATE_CURRENCY, values)
    console.log('✅ Réponse de création:', createResponse.value)

    // Vérifier si la création a réussi
    if (createSuccess.value && !createError.value) {
      showCustomToast({
        message: (createResponse.value as any)?.message || 'Devise créée avec succès',
        type: 'success',
      })

      resetForm()
      isCreateDialogOpen.value = false

      // Rafraîchir les données avec un délai pour s'assurer que l'API est à jour
      console.log('🔄 Rafraîchissement des données...')
      await fetchData()

      // Double vérification après un court délai
      setTimeout(async () => {
        console.log('🔄 Double vérification des données...')
        await fetchData()
        console.log('📊 Données après rafraîchissement:', currenciesData.value)
      }, 1000)
    } else {
      throw new Error(createError.value || 'Échec de la création de la devise')
    }
  } catch (err: any) {
    let errorMessage = 'Erreur lors de la création de la devise'
    if (err.response?.data?.message) {
      errorMessage = err.response.data.message
    } else if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      const errorList = Object.values(errors).flat()
      errorMessage = `Erreurs de validation: ${errorList.join(', ')}`
    } else if (err.message) {
      errorMessage = err.message
    }

    showCustomToast({
      message: errorMessage,
      type: 'error',
    })
  }
})

// Fonction pour modifier une devise
const onSubmitEdit = handleSubmit(async (values: CurrencyFormData) => {
  if (!editingItem.value) return

  try {
    await updateCurrency(API_ROUTES.UPDATE_CURRENCY(editingItem.value.id), values)

    showCustomToast({
      message: 'Devise modifiée avec succès',
      type: 'success',
    })

    resetForm()
    isEditDialogOpen.value = false
    editingItem.value = null
    await fetchData()
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la modification de la devise',
      type: 'error',
    })
  }
})

// Fonction pour ouvrir le dialog de modification
const openEditDialog = (currency: Currency) => {
  editingItem.value = currency
  setValues({
    code: currency.code,
    name: currency.name,
    symbol: currency.symbol || '',
    is_default: currency.is_default,
  })
  isEditDialogOpen.value = true
}

// Fonction pour supprimer une devise
const handleDelete = async (currencyId: number) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette devise ?')) {
    return
  }

  try {
    await deleteCurrency(API_ROUTES.DELETE_CURRENCY(currencyId))

    showCustomToast({
      message: 'Devise supprimée avec succès',
      type: 'success',
    })

    await fetchData()
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la suppression de la devise',
      type: 'error',
    })
  }
}

// Fonction pour gérer la sélection multiple
const toggleSelection = (currencyId: number) => {
  if (selectedCurrencies.value.includes(currencyId)) {
    selectedCurrencies.value = selectedCurrencies.value.filter((id) => id !== currencyId)
  } else {
    selectedCurrencies.value.push(currencyId)
  }
}

// Fonction pour sélectionner/désélectionner tout
const toggleSelectAll = () => {
  if (
    selectedCurrencies.value.length === filteredCurrencies.value.length &&
    filteredCurrencies.value.length > 0
  ) {
    selectedCurrencies.value = []
  } else {
    selectedCurrencies.value = filteredCurrencies.value.map((currency) => currency.id)
  }
}

// Fonction pour supprimer les devises sélectionnées
const deleteSelectedCurrencies = async () => {
  if (selectedCurrencies.value.length === 0) {
    showCustomToast({
      message: 'Aucune devise sélectionnée',
      type: 'warning',
    })
    return
  }

  if (
    !confirm(`Êtes-vous sûr de vouloir supprimer ${selectedCurrencies.value.length} devise(s) ?`)
  ) {
    return
  }

  try {
    for (const currencyId of selectedCurrencies.value) {
      await deleteCurrency(API_ROUTES.DELETE_CURRENCY(currencyId))
    }

    showCustomToast({
      message: `${selectedCurrencies.value.length} devise(s) supprimée(s) avec succès`,
      type: 'success',
    })

    selectedCurrencies.value = []
    await fetchData()
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la suppression des devises',
      type: 'error',
    })
  }
}

// Charger les données au montage
onMounted(async () => {
  console.log('🚀 Chargement initial des devises...')
  await fetchData()
  console.log('📊 Devises chargées au montage:', currenciesData.value)

  // Analyse détaillée de la structure
  if (currenciesData.value) {
    console.log('🔍 Type de currenciesData.value:', typeof currenciesData.value)
    console.log('🔍 Clés disponibles:', Object.keys(currenciesData.value))
    console.log('🔍 Contenu complet:', JSON.stringify(currenciesData.value, null, 2))

    // Vérifier différentes structures possibles
    if (currenciesData.value.data) {
      console.log('✅ Structure avec .data - Nombre:', currenciesData.value.data.length)
    } else if (Array.isArray(currenciesData.value)) {
      console.log('✅ Structure tableau direct - Nombre:', currenciesData.value.length)
    } else if (currenciesData.value.currencies) {
      console.log('✅ Structure avec .currencies - Nombre:', currenciesData.value.currencies.length)
    } else {
      console.log('⚠️ Structure inconnue')
    }

    // Afficher les devises normalisées
    console.log('🔄 Devises normalisées:', normalizedCurrencies.value)
    console.log('✅ Nombre de devises normalisées:', normalizedCurrencies.value.length)

    if (normalizedCurrencies.value.length > 0) {
      console.log('📊 Première devise:', normalizedCurrencies.value[0])
    }
  } else {
    console.log('❌ currenciesData.value est null/undefined')
  }
})
</script>

<template>
  <ComptaLayout activeBread="Devises" active-tag-name="devises" group="frais">
    <BoxPanelWrapper>
      <!-- Barre d'actions -->
      <div class="flex items-center gap-3 justify-between mb-4">
        <div class="relative flex-1 max-w-md">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher par code, nom ou symbole..."
            v-model="searchQuery"
            class="pl-10"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Actions groupées -->
          <Button
            v-if="selectedCurrencies.length > 0"
            variant="destructive"
            size="sm"
            @click="deleteSelectedCurrencies"
            :disabled="deleteLoading"
          >
            <span class="iconify hugeicons--delete-02 mr-2"></span>
            Supprimer ({{ selectedCurrencies.length }})
          </Button>

          <!-- Bouton d'export -->
          <!-- <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" size="sm">
                  <span class="iconify hugeicons--download-01 mr-2"></span>
                  Exporter
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent>
                <DropdownMenuItem>
                  <span class="iconify hugeicons--file-02 mr-2"></span>
                  Excel
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <span class="iconify hugeicons--file-02 mr-2"></span>
                  PDF
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu> -->

          <!-- Dialog de création -->
          <Dialog v-model:open="isCreateDialogOpen">
            <DialogTrigger as-child>
              <Button size="md" class="bg-primary text-primary-foreground">
                <span class="iconify hugeicons--add-01 mr-2"></span>
                <span class="hidden sm:flex">Nouvelle Devise</span>
              </Button>
            </DialogTrigger>
            <DialogContent class="sm:max-w-[500px]">
              <DialogHeader>
                <DialogTitle>Créer une nouvelle devise</DialogTitle>
                <DialogDescription>
                  Remplissez les informations de la nouvelle devise.
                </DialogDescription>
              </DialogHeader>
              <form @submit="onSubmitCreate" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label for="code">Code devise *</Label>
                    <Field name="code" v-slot="{ field, errorMessage }">
                      <Input
                        id="code"
                        v-bind="field"
                        placeholder="USD"
                        maxlength="3"
                        class="uppercase"
                        :class="{ 'border-red-500': errorMessage }"
                      />
                      <p v-if="errorMessage" class="text-sm text-red-500 mt-1">
                        {{ errorMessage }}
                      </p>
                    </Field>
                  </div>
                  <div class="space-y-2">
                    <Label for="symbol">Symbole</Label>
                    <Field name="symbol" v-slot="{ field, errorMessage }">
                      <Input
                        id="symbol"
                        v-bind="field"
                        placeholder="$"
                        :class="{ 'border-red-500': errorMessage }"
                      />
                      <p v-if="errorMessage" class="text-sm text-red-500 mt-1">
                        {{ errorMessage }}
                      </p>
                    </Field>
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="name">Nom de la devise *</Label>
                  <Field name="name" v-slot="{ field, errorMessage }">
                    <Input
                      id="name"
                      v-bind="field"
                      placeholder="Dollar américain"
                      :class="{ 'border-red-500': errorMessage }"
                    />
                    <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
                  </Field>
                </div>

                <div class="flex items-center space-x-2">
                  <Field name="is_default" v-slot="{ field }">
                    <Checkbox id="is_default" v-bind="field" />
                  </Field>
                  <Label for="is_default">Devise par défaut</Label>
                </div>

                <DialogFooter>
                  <Button type="button" variant="outline" @click="isCreateDialogOpen = false">
                    Annuler
                  </Button>
                  <Button type="submit" :disabled="createLoading">
                    <span
                      v-if="createLoading"
                      class="iconify hugeicons--loading-03 animate-spin mr-2"
                    ></span>
                    Créer
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>
      </div>

      <!-- Tableau des devises -->
      <div class="border rounded-lg overflow-hidden flex flex-1 bg-white">
        <Table>
          <TableHeader>
            <TableRow class="bg-white">
              <TableHead class="w-[40px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="
                    selectedCurrencies.length === filteredCurrencies.length &&
                    filteredCurrencies.length > 0
                  "
                  @click="toggleSelectAll"
                />
              </TableHead>
              <TableHead>Code</TableHead>
              <TableHead>Nom</TableHead>
              <TableHead>Symbole</TableHead>
              <TableHead>Par défaut</TableHead>
              <TableHead>Date de création</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <!-- État de chargement -->
            <TableRow v-if="loading">
              <TableCell :colspan="7" class="text-center py-8">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--loading-03 animate-spin"></span>
                  Chargement des devises...
                </div>
              </TableCell>
            </TableRow>

            <!-- État d'erreur -->
            <TableRow v-else-if="error">
              <TableCell :colspan="7" class="text-center py-8 text-red-500">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--alert-circle"></span>
                  Erreur lors du chargement : {{ error }}
                </div>
              </TableCell>
            </TableRow>

            <!-- Liste vide -->
            <TableRow v-else-if="filteredCurrencies.length === 0">
              <TableCell :colspan="7" class="text-center py-8 text-gray-500">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--coins-01"></span>
                  {{
                    searchQuery
                      ? 'Aucune devise trouvée pour cette recherche'
                      : 'Aucune devise configurée'
                  }}
                </div>
              </TableCell>
            </TableRow>

            <!-- Données -->
            <TableRow v-else v-for="currency in filteredCurrencies" :key="currency.id">
              <TableCell class="w-[40px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="selectedCurrencies.includes(currency.id)"
                  @click="toggleSelection(currency.id)"
                />
              </TableCell>
              <TableCell class="font-mono font-medium">{{ currency.code }}</TableCell>
              <TableCell class="font-medium">{{ currency.name }}</TableCell>
              <TableCell>
                <span class="text-lg">{{ currency.symbol || '-' }}</span>
              </TableCell>
              <TableCell>
                <span :class="currency.is_default ? 'text-green-600 font-medium' : 'text-gray-500'">
                  {{ formatDefaultStatus(currency.is_default) }}
                </span>
              </TableCell>
              <TableCell>{{ formatDate(currency.created_at) }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <Button
                    variant="outline"
                    size="icon"
                    class="size-8"
                    @click="openEditDialog(currency)"
                    title="Modifier la devise"
                  >
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>
                  <Button
                    variant="destructive"
                    size="icon"
                    class="size-8"
                    @click="handleDelete(currency.id)"
                    title="Supprimer la devise"
                    :disabled="deleteLoading"
                  >
                    <span class="iconify hugeicons--delete-02"></span>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </BoxPanelWrapper>

    <!-- Dialog de modification -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Modifier la devise</DialogTitle>
          <DialogDescription> Modifiez les informations de la devise. </DialogDescription>
        </DialogHeader>
        <form @submit="onSubmitEdit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_code">Code devise *</Label>
              <Field name="code" v-slot="{ field, errorMessage }">
                <Input
                  id="edit_code"
                  v-bind="field"
                  placeholder="USD"
                  maxlength="3"
                  class="uppercase"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="edit_symbol">Symbole</Label>
              <Field name="symbol" v-slot="{ field, errorMessage }">
                <Input
                  id="edit_symbol"
                  v-bind="field"
                  placeholder="$"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="edit_name">Nom de la devise *</Label>
            <Field name="name" v-slot="{ field, errorMessage }">
              <Input
                id="edit_name"
                v-bind="field"
                placeholder="Dollar américain"
                :class="{ 'border-red-500': errorMessage }"
              />
              <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
            </Field>
          </div>

          <div class="flex items-center space-x-2">
            <Field name="is_default" v-slot="{ field }">
              <Checkbox id="edit_is_default" v-bind="field" />
            </Field>
            <Label for="edit_is_default">Devise par défaut</Label>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="isEditDialogOpen = false">
              Annuler
            </Button>
            <Button type="submit" :disabled="updateLoading">
              <span
                v-if="updateLoading"
                class="iconify hugeicons--loading-03 animate-spin mr-2"
              ></span>
              Modifier
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </ComptaLayout>
</template>
