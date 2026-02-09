<script setup lang="ts">
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
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
import { Checkbox } from '@/components/ui/checkbox'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import ListCurrency from '@/utils/widgets/vues/ListCurrency.vue'
import ListFeeType from '@/utils/widgets/vues/ListFeeType.vue'
import ListExchangeRate from '@/utils/widgets/vues/ListExchangeRate.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { usePostApi } from '@/composables/usePostApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { onMounted, ref, computed, watch } from 'vue'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { useForm, Field } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'

// Interface pour les frais scolaires
interface SchoolFee {
  id: number
  amount: number
  currency_id: number
  fee_type_id: number
  student_id: number
  effective_date: string
  exchange_rate_id?: number
  created_at?: string
  updated_at?: string
  // Relations pour l'affichage
  currency?: { name: string; code: string }
  fee_type?: { name: string }
  student?: { name: string; firstname?: string; matricule?: string }
  exchange_rate?: { id: number; rate: number; currency?: { name: string; code: string } }
}

// Interface pour la réponse API
interface SchoolFeesApiResponse {
  success: boolean
  fees: SchoolFee[] // Changé de 'data' à 'fees'
  message?: string
}

// Schéma de validation Zod avec transformations
const schoolFeeSchema = z.object({
  amount: z.coerce.number().min(0, 'Le montant doit être positif'),
  // currency_id requis: string non vide transformée en number OU number direct, et > 0
  currency_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'La devise est requise')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'La devise est requise'),
  // fee_type_id requis: mêmes règles
  fee_type_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'Le type de frais est requis')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'Le type de frais est requis'),
  effective_date: z.string().min(1, "La date d'effet est requise"),
  // exchange_rate_id optionnel: autoriser '' -> null, sinon number
  exchange_rate_id: z
    .union([z.literal(''), z.string().transform((v) => Number(v)), z.number()])
    .optional()
    .transform((v) => (v === '' ? null : (v as number | null))),
})

type SchoolFeeFormData = z.infer<typeof schoolFeeSchema>

// Variables réactives pour les formulaires
const searchQuery = ref('')
const selectedItems = ref<number[]>([])
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingItem = ref<SchoolFee | null>(null)
const selectedFees = ref<number[]>([])

// APIs
const {
  data: feesData,
  loading,
  error,
  fetchData,
} = useGetApi<SchoolFeesApiResponse>(API_ROUTES.GET_SCHOOL_FEES)
// Charger aussi les taux de change pour résoudre exchange_rate_id -> taux affichable
const {
  data: exchangeRatesData,
  loading: ratesLoading,
  error: ratesError,
  fetchData: fetchRates,
} = useGetApi<any>(API_ROUTES.GET_EXCHANGE_RATES)
const { postData: createFee, loading: createLoading } = usePostApi()
const { putData: updateFee, loading: updateLoading } = usePutApi()
const { deleteItem: deleteFee, deleting: deleteLoading } = useDeleteApi()

// Formulaire pour création/modification
const { handleSubmit, resetForm, setValues } = useForm({
  validationSchema: toTypedSchema(schoolFeeSchema),
})

// Computed pour les frais filtrés
const filteredFees = computed(() => {
  console.log('🔍 GestionFraisScolaires - filteredFees computed')
  console.log('📊 feesData.value:', feesData.value)
  console.log('⚠️ error.value:', error.value)
  console.log('⏳ loading.value:', loading.value)

  if (!feesData.value) {
    console.log('❌ Pas de feesData.value')
    return []
  }

  if (!feesData.value.fees) {
    console.log('❌ Pas de feesData.value.fees')
    console.log('📋 Structure de feesData.value:', Object.keys(feesData.value))
    return []
  }

  console.log('✅ Données trouvées:', feesData.value.fees.length, 'frais')
  console.log('📝 Premier frais:', feesData.value.fees[0])

  if (!searchQuery.value) {
    return feesData.value.fees
  }

  const query = searchQuery.value.toLowerCase()
  const filtered = feesData.value.fees.filter((fee) => {
    const studentName = fee.student
      ? `${fee.student.firstname || ''} ${fee.student.name}`.trim().toLowerCase()
      : ''
    const matricule = fee.student?.matricule?.toLowerCase() || ''
    return (
      fee.fee_type?.name?.toLowerCase().includes(query) ||
      studentName.includes(query) ||
      matricule.includes(query)
    )
  })

  console.log('🔎 Recherche "' + query + '" - Résultats:', filtered.length)
  return filtered
})

// Fonction pour formater le montant
const formatAmount = (amount: number) => {
  return new Intl.NumberFormat('fr-CD', {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

// Fonction pour formater le type de frais
const formatFeeType = (fee: SchoolFee) => {
  return fee.fee_type?.name || 'Non défini'
}

// Fonction pour formater la devise
const formatCurrency = (fee: SchoolFee) => {
  return fee.currency?.code
}

// Fonction pour formater l'élève qui paie
const formatStudent = (fee: SchoolFee) => {
  if (!fee.student) return '-'
  const fullName = `${fee.student.firstname || ''} ${fee.student.name || ''}`.trim()
  const matricule = fee.student.matricule ? ` (${fee.student.matricule})` : ''
  return fullName ? `${fullName}${matricule}` : '-'
}

// Fonction pour formater la date
const formatDate = (dateString?: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR')
}

// Normaliser les taux de change depuis l'API en Map id -> { rate:number, code?:string }
const exchangeRatesMap = computed(() => {
  const v: any = exchangeRatesData.value
  const map = new Map<number, { rate: number; code?: string }>()
  if (!v) return map

  let list: any[] = []
  if (Array.isArray(v)) list = v
  else if (Array.isArray(v?.exchange_rates)) list = v.exchange_rates
  else if (Array.isArray(v?.data)) list = v.data
  else if (v && typeof v === 'object')
    list = Object.values(v).filter((it: any) => it && typeof it === 'object' && 'id' in it)

  list.forEach((item: any) => {
    if (!item || !item.id) return
    const rateNum = Number(item.rate)
    if (!Number.isFinite(rateNum)) return
    const code = item.currency?.code
    map.set(Number(item.id), { rate: rateNum, code })
  })
  return map
})

// Fonction pour formater le taux de change
const formatExchangeRate = (fee: SchoolFee) => {
  // 1) Si la relation est présente
  if (fee.exchange_rate && typeof fee.exchange_rate.rate !== 'undefined') {
    const currencyCode = fee.exchange_rate.currency?.code || 'N/A'
    const rateNum = Number(fee.exchange_rate.rate)
    return `${currencyCode} ${Number.isFinite(rateNum) ? Math.round(rateNum) : ''}`.trim()
  }
  // 2) Sinon, tenter via exchange_rate_id
  if (fee.exchange_rate_id) {
    const found = exchangeRatesMap.value.get(Number(fee.exchange_rate_id))
    if (found) {
      const currencyCode = found.code || 'N/A'
      return `${currencyCode} ${Math.round(found.rate)}`
    }
  }
  return '-'
}

// Fonction pour fermer le dialog de création de manière sécurisée
const closeCreateDialog = () => {
  try {
    resetForm()
    isCreateDialogOpen.value = false
  } catch (err) {
    console.warn('Erreur lors de la fermeture du dialog de création:', err)
    isCreateDialogOpen.value = false
  }
}

// Fonction pour créer un nouveau frais
const onSubmitCreate = handleSubmit(
  async (values: SchoolFeeFormData) => {
    console.log('🚀 Form submission started')
    console.log('📝 Form values:', values)
    console.log('🔗 API Route:', API_ROUTES.CREATE_SCHOOL_FEE)

    try {
      console.log('📤 Sending data to API...')
      const result = await createFee(API_ROUTES.CREATE_SCHOOL_FEE, values)
      console.log('✅ API Response:', result)

      showCustomToast({
        message: 'Frais scolaire créé avec succès',
        type: 'success',
      })

      closeCreateDialog()
      await fetchData()
      console.log('🔄 Data refreshed')
    } catch (err) {
      console.error('❌ Error during form submission:', err)
      showCustomToast({
        message: 'Erreur lors de la création du frais scolaire',
        type: 'error',
      })
    }
  },
  (errors) => {
    console.log('❌ Form validation errors:', errors)
    showCustomToast({
      message: 'Veuillez corriger les erreurs dans le formulaire',
      type: 'error',
    })
  },
)

// Fonction pour fermer le dialog de modification de manière sécurisée
const closeEditDialog = () => {
  try {
    resetForm()
    editingItem.value = null
    isEditDialogOpen.value = false
  } catch (err) {
    console.warn('Erreur lors de la fermeture du dialog:', err)
    // Forcer la fermeture même en cas d'erreur
    isEditDialogOpen.value = false
    editingItem.value = null
  }
}

// Fonction pour modifier un frais
const onSubmitEdit = handleSubmit(async (values: SchoolFeeFormData) => {
  if (!editingItem.value) return

  try {
    await updateFee(API_ROUTES.UPDATE_SCHOOL_FEE(editingItem.value.id), values)

    showCustomToast({
      message: 'Frais scolaire modifié avec succès',
      type: 'success',
    })

    closeEditDialog()
    await fetchData()
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la modification du frais scolaire',
      type: 'error',
    })
  }
})

// Fonction pour ouvrir le dialog de modification
const openEditDialog = (fee: SchoolFee) => {
  editingItem.value = fee
  setValues({
    amount: fee.amount,
    currency_id: fee.currency_id?.toString() || '',
    fee_type_id: fee.fee_type_id?.toString() || '',
    effective_date: fee.effective_date.split('T')[0],
    exchange_rate_id: fee.exchange_rate_id?.toString() || '',
  })
  isEditDialogOpen.value = true
}

// Fonction pour supprimer un frais
const handleDelete = async (feeId: number) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce frais scolaire ?')) {
    return
  }

  try {
    await deleteFee(API_ROUTES.DELETE_SCHOOL_FEE(feeId))

    showCustomToast({
      message: 'Frais scolaire supprimé avec succès',
      type: 'success',
    })

    await fetchData()
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la suppression du frais scolaire',
      type: 'error',
    })
  }
}

// Fonction pour gérer la sélection multiple
const toggleSelection = (feeId: number) => {
  const index = selectedFees.value.indexOf(feeId)
  if (index > -1) {
    selectedFees.value.splice(index, 1)
  } else {
    selectedFees.value.push(feeId)
  }
}

// Fonction pour sélectionner/désélectionner tout
const toggleSelectAll = () => {
  if (selectedFees.value.length === filteredFees.value.length) {
    selectedFees.value = []
  } else {
    selectedFees.value = filteredFees.value.map((fee) => fee.id)
  }
}

// Fonction pour supprimer les frais sélectionnés
const deleteSelectedFees = async () => {
  if (selectedFees.value.length === 0) return

  if (
    !confirm(`Êtes-vous sûr de vouloir supprimer ${selectedFees.value.length} frais scolaire(s) ?`)
  ) {
    return
  }

  try {
    for (const feeId of selectedFees.value) {
      await deleteFee(API_ROUTES.DELETE_SCHOOL_FEE(feeId))
    }

    showCustomToast({
      message: `${selectedFees.value.length} frais scolaire(s) supprimé(s) avec succès`,
      type: 'success',
    })

    selectedFees.value = []
    await fetchData()
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la suppression des frais scolaires',
      type: 'error',
    })
  }
}

// Charger les données au montage
onMounted(async () => {
  console.log('🚀 GestionFraisScolaires - Chargement initial des données')
  console.log('🔗 API Route:', API_ROUTES.GET_SCHOOL_FEES)

  try {
    await Promise.all([fetchData(), fetchRates()])
    console.log('✅ Données chargées:', feesData.value)
    console.log('❌ Erreur:', error.value)
    console.log('⏳ Loading:', loading.value)
  } catch (err) {
    console.error('💥 Erreur lors du chargement:', err)
  }
})

// Empêcher l'ouverture simultanée des deux dialogs
watch(isCreateDialogOpen, (open) => {
  if (open) {
    isEditDialogOpen.value = false
    // Réinitialiser le formulaire à l'ouverture pour éviter les valeurs résiduelles
    setValues({
      amount: 0,
      currency_id: '',
      fee_type_id: '',
      effective_date: '',
      exchange_rate_id: '',
    } as any)
  }
})

watch(isEditDialogOpen, (open) => {
  if (open) {
    isCreateDialogOpen.value = false
  }
})
</script>

<template>
  <ComptaLayout activeBread="Frais Scolaires" active-tag-name="frais-scolaires" group="frais">
    <BoxPanelWrapper>
      <!-- Barre d'actions -->
      <div class="flex items-center gap-3 justify-between mb-4">
        <div class="relative flex-1 max-w-md">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher un frais scolaire..."
            v-model="searchQuery"
            class="pl-10"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <!-- Actions groupées -->
          <Button
            v-if="selectedFees.length > 0"
            variant="destructive"
            size="md"
            @click="deleteSelectedFees"
            :disabled="deleteLoading"
          >
            <span class="iconify hugeicons--delete-02 mr-2"></span>
            Supprimer ({{ selectedFees.length }})
          </Button>

          <!-- Menu d'export -->
          <!-- <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="md" class="bg-white border border-border rounded-md">
                  Exporter
                  <span class="iconify hugeicons--arrow-down-01 ml-2"></span>
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

          <!-- Bouton nouveau frais -->
          <Dialog v-model:open="isCreateDialogOpen">
            <DialogTrigger as-child>
              <Button size="md" class="bg-primary text-primary-foreground">
                <span class="iconify hugeicons--add-01 mr-2"></span>
                <span class="hidden sm:flex">Nouveau Frais</span>
              </Button>
            </DialogTrigger>
            <DialogContent class="sm:max-w-[600px]">
              <DialogHeader>
                <DialogTitle>Créer un nouveau frais scolaire</DialogTitle>
                <DialogDescription>
                  Remplissez les informations du nouveau frais scolaire.
                </DialogDescription>
              </DialogHeader>
              <form @submit.prevent="onSubmitCreate" class="space-y-6">
                <!-- Informations principales -->
                <div class="space-y-4">
                  <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <Label for="amount">Montant </Label>
                      <Field name="amount" v-slot="{ field, errorMessage }">
                        <Input
                          id="amount"
                          v-bind="field"
                          type="number"
                          placeholder="0"
                          step="0.01"
                          class="w-full"
                        />
                        <span v-if="errorMessage" class="text-sm text-red-500">{{
                          errorMessage
                        }}</span>
                      </Field>
                    </div>
                    <div class="space-y-2">
                      <Label for="currency_id">Devise </Label>
                      <Field name="currency_id" v-slot="{ field, errorMessage }">
                        <ListCurrency
                          :model-value="field.value"
                          @update:model-value="field.onChange"
                          placeholder="Sélectionner une devise"
                        />
                        <span v-if="errorMessage" class="text-sm text-red-500">{{
                          errorMessage
                        }}</span>
                      </Field>
                    </div>
                  </div>
                </div>

                <!-- Configuration du frais -->
                <div class="space-y-4">
                  <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <Label for="fee_type_id">Type de frais </Label>
                      <Field name="fee_type_id" v-slot="{ field, errorMessage }">
                        <ListFeeType
                          :model-value="field.value"
                          @update:model-value="field.onChange"
                          placeholder="Sélectionner un type"
                        />
                        <span v-if="errorMessage" class="text-sm text-red-500">{{
                          errorMessage
                        }}</span>
                      </Field>
                    </div>
                    <div class="space-y-2">
                      <Label for="exchange_rate_id"
                        >Taux de change
                        <span class="text-gray-500 text-sm">- Optionnel</span></Label
                      >
                      <Field name="exchange_rate_id" v-slot="{ field, errorMessage }">
                        <ListExchangeRate
                          :model-value="field.value"
                          @update:model-value="field.onChange"
                          placeholder="Sélectionner un taux de change"
                        />
                        <span v-if="errorMessage" class="text-sm text-red-500">{{
                          errorMessage
                        }}</span>
                      </Field>
                    </div>
                  </div>
                </div>

                <!-- Informations complémentaires -->
                <div class="space-y-4">
                  <div class="space-y-2">
                    <Label for="effective_date">Date d'effet </Label>
                    <Field name="effective_date" v-slot="{ field, errorMessage }">
                      <Input id="effective_date" v-bind="field" type="date" class="w-full" />
                      <span v-if="errorMessage" class="text-sm text-red-500">{{
                        errorMessage
                      }}</span>
                    </Field>
                  </div>
                </div>

                <DialogFooter>
                  <Button type="button" variant="outline" @click="closeCreateDialog">
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

      <!-- Tableau des frais -->
      <div class="rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="selectedFees.length === filteredFees.length && filteredFees.length > 0"
                  @click="toggleSelectAll"
                />
              </TableHead>

              <TableHead>Type de frais</TableHead>
              <TableHead>Montant</TableHead>
              <TableHead>Taux</TableHead>
              <TableHead>Devise</TableHead>
              <TableHead>Date d'effet</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <!-- État de chargement -->
            <TableRow v-if="loading">
              <TableCell :colspan="10" class="text-center py-8">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--loading-03 animate-spin"></span>
                  Chargement des frais scolaires...
                </div>
              </TableCell>
            </TableRow>

            <!-- État d'erreur -->
            <TableRow v-else-if="error">
              <TableCell :colspan="10" class="text-center py-8 text-red-500">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--alert-circle"></span>
                  Erreur lors du chargement : {{ error }}
                </div>
              </TableCell>
            </TableRow>

            <!-- Liste vide -->
            <TableRow v-else-if="filteredFees.length === 0">
              <TableCell :colspan="10" class="text-center py-8 text-gray-500">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--wallet-03"></span>
                  {{
                    searchQuery
                      ? 'Aucun frais trouvé pour cette recherche'
                      : 'Aucun frais scolaire configuré'
                  }}
                </div>
              </TableCell>
            </TableRow>

            <!-- Données -->
            <TableRow v-else v-for="fee in filteredFees" :key="fee.id">
              <TableCell class="w-[40px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="selectedFees.includes(fee.id)"
                  @click="toggleSelection(fee.id)"
                />
              </TableCell>

              <TableCell>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                  {{ formatFeeType(fee) }}
                </span>
              </TableCell>
              <TableCell class="font-medium">{{ formatAmount(fee.amount) }}</TableCell>
              <TableCell>
                <span class="text-sm font-medium text-blue-600">
                  {{ formatExchangeRate(fee) }}
                </span>
              </TableCell>
              <TableCell>
                <span class="text-sm font-medium">{{ formatCurrency(fee) }}</span>
              </TableCell>
              <TableCell>{{ formatDate(fee.effective_date) }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <Button
                    variant="outline"
                    size="icon"
                    class="size-8"
                    @click="openEditDialog(fee)"
                    title="Modifier le frais"
                  >
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>
                  <Button
                    variant="destructive"
                    size="icon"
                    class="size-8"
                    @click="handleDelete(fee.id)"
                    title="Supprimer le frais"
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
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Modifier le frais scolaire</DialogTitle>
          <DialogDescription> Modifiez les informations du frais scolaire. </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="onSubmitEdit" class="space-y-6">
          <!-- Informations principales -->
          <div class="space-y-4">
            <div class="grid grid-cols-1 gap-4"></div>

            <div class="grid grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label for="edit_amount">Montant *</Label>
                <Field name="amount" v-slot="{ field, errorMessage }">
                  <Input
                    id="edit_amount"
                    v-bind="field"
                    type="number"
                    placeholder="0"
                    step="0.01"
                    class="w-full"
                  />
                  <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
                </Field>
              </div>
              <div class="space-y-2">
                <Label for="edit_currency_id">Devise *</Label>
                <Field name="currency_id" v-slot="{ field, errorMessage }">
                  <ListCurrency
                    :model-value="field.value"
                    @update:model-value="field.onChange"
                    placeholder="Sélectionner une devise"
                  />
                  <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
                </Field>
              </div>
            </div>
          </div>

          <!-- Configuration du frais -->
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label for="edit_fee_type_id">Type de frais *</Label>
                <Field name="fee_type_id" v-slot="{ field, errorMessage }">
                  <ListFeeType
                    :model-value="field.value"
                    @update:model-value="field.onChange"
                    placeholder="Sélectionner un type"
                  />
                  <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
                </Field>
              </div>
              <div class="space-y-2">
                <Label for="edit_exchange_rate_id"
                  >Taux de change <span class="text-gray-500 text-sm">- Optionnel</span></Label
                >
                <Field name="exchange_rate_id" v-slot="{ field, errorMessage }">
                  <ListExchangeRate
                    :model-value="field.value"
                    @update:model-value="field.onChange"
                    placeholder="Sélectionner un taux de change"
                  />
                  <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
                </Field>
              </div>
            </div>
          </div>

          <!-- Informations complémentaires -->
          <div class="space-y-4">
            <div class="space-y-2">
              <Label for="edit_effective_date">Date d'effet *</Label>
              <Field name="effective_date" v-slot="{ field, errorMessage }">
                <Input id="edit_effective_date" v-bind="field" type="date" class="w-full" />
                <span v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</span>
              </Field>
            </div>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="closeEditDialog"> Annuler </Button>
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
