<script setup lang="ts">
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import { Textarea } from '@/components/ui/textarea'
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import ListStudentWithSearch from '@/utils/widgets/vues/ListStudentWithSearch.vue'
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
import { eventBus } from '@/utils/eventBus.ts'

// Interface pour les paiements
interface PaymentData {
  id: number
  school_id?: string | null
  user_id: number
  fee_id: number
  payment_method_id: number
  payment_motif_id: number
  currency_id: number
  exchange_rate_id: number
  account_type_id: number
  amount: number
  amount_foreign: number
  remaining_amount?: number | null
  details?: string | null
  paid_at?: string | null
  status: 'pending' | 'confirmed' | 'refunded'
  confirmed_at?: string | null
  refunded_at?: string | null
  currency?: string
  payment_method?: string
  created_at?: string
  updated_at?: string
}

// Interface pour la réponse API
interface PaymentsApiResponse {
  success: boolean
  data: PaymentData[]
  message?: string
}

// Schéma de validation Zod pour les paiements
const paymentSchema = z.object({
  school_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'string' ? val : val.toString()
    })
    .nullable(),
  user_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'number' ? val : Number(val)
    })
    .refine((val) => val !== null && !isNaN(val as number), "L'utilisateur est requis"),
  fee_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'number' ? val : Number(val)
    })
    .refine((val) => val !== null && !isNaN(val as number), 'Le frais est requis'),
  payment_method_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'number' ? val : Number(val)
    })
    .refine((val) => val !== null && !isNaN(val as number), 'La méthode de paiement est requise'),
  payment_motif_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'number' ? val : Number(val)
    })
    .refine((val) => val !== null && !isNaN(val as number), 'Le motif de paiement est requis'),
  currency_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'number' ? val : Number(val)
    })
    .refine((val) => val !== null && !isNaN(val as number), 'La devise est requise'),
  exchange_rate_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'number' ? val : Number(val)
    })
    .refine((val) => val !== null && !isNaN(val as number), 'Le taux de change est requis'),
  account_type_id: z
    .union([z.string(), z.number()])
    .transform((val) => {
      if (val === '' || val === null || val === undefined) return null
      return typeof val === 'number' ? val : Number(val)
    })
    .refine((val) => val !== null && !isNaN(val as number), 'Le type de compte est requis'),
  amount: z.coerce.number().min(0, 'Le montant doit être positif'),
  amount_foreign: z.coerce.number().min(0).optional().nullable(),
  remaining_amount: z.coerce.number().min(0).optional().nullable(),
  details: z.string().optional().nullable(),
  paid_at: z.string().optional().nullable(),
  status: z.enum(['pending', 'confirmed', 'refunded']).default('pending'),
  confirmed_at: z.string().optional().nullable(),
  refunded_at: z.string().optional().nullable(),
})

type PaymentFormData = z.infer<typeof paymentSchema>

// Variables réactives pour les formulaires
const searchQuery = ref('')
const selectedItems = ref<number[]>([])
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingItem = ref<PaymentData | null>(null)

// Variables pour la sélection multiple
const selectedPayments = ref<number[]>([])

// Variables pour les filtres
const filterStatus = ref<string>('all')
const filterCurrency = ref<string>('all')
const filterMethod = ref<string>('all')
const filterDateFrom = ref<string>('')
const filterDateTo = ref<string>('')

// Composables pour les opérations CRUD
const {
  data: payments,
  error,
  loading,
  fetchData,
} = useGetApi<PaymentData[]>(API_ROUTES.GET_PAYMENTS)
const {
  response: createResponse,
  error: createError,
  loading: creating,
  postData: createPayment,
} = usePostApi<PaymentsApiResponse>()
const {
  response: updateResponse,
  error: updateError,
  loading: updating,
  putData: updatePayment,
} = usePutApi<PaymentsApiResponse>()
const { deleteResponse, errorDelete, deleting, deleteItem } = useDeleteApi<PaymentsApiResponse>()

// Composables pour les données des sélecteurs
const {
  data: paymentMethods,
  loading: loadingMethods,
  fetchData: fetchPaymentMethods,
} = useGetApi<any[]>(API_ROUTES.GET_PAYMENT_METHODE)
const {
  data: paymentMotifs,
  loading: loadingMotifs,
  fetchData: fetchPaymentMotifs,
} = useGetApi<any[]>(API_ROUTES.GET_PAYMENT_MOTIFS)
const {
  data: schoolFees,
  loading: loadingFees,
  fetchData: fetchSchoolFees,
} = useGetApi<any[]>(API_ROUTES.GET_SCHOOL_FEES)
const {
  data: feeTypes,
  loading: loadingFeeTypes,
  fetchData: fetchFeeTypes,
} = useGetApi<any[]>(API_ROUTES.GET_FEE_TYPES)
const { data: users, loading: loadingUsers, fetchData: fetchUsers } = useGetApi<any[]>('/users')
const {
  data: exchangeRates,
  loading: loadingRates,
  fetchData: fetchExchangeRates,
} = useGetApi<any[]>('/exchange-rates')
const {
  data: accountTypes,
  loading: loadingAccountTypes,
  fetchData: fetchAccountTypes,
} = useGetApi<any[]>('/account-types')

// Formulaire pour création/modification
const { handleSubmit, resetForm, setValues } = useForm({
  validationSchema: toTypedSchema(paymentSchema),
})

// Variables calculées pour l'affichage
const filteredPayments = computed(() => {
  console.log('🔍 Gestion_paiements - payments.value:', payments.value)
  console.log('🔍 Gestion_paiements - Type de payments.value:', typeof payments.value)
  console.log('🔍 Gestion_paiements - Est un tableau?', Array.isArray(payments.value))

  if (!payments.value) return []

  // Gérer différentes structures de réponse API
  let paymentsList: PaymentData[] = []

  // Cas 1: Tableau direct
  if (Array.isArray(payments.value)) {
    paymentsList = payments.value
    console.log('✅ Format: Tableau direct')
  }
  // Cas 2: Structure avec 'data'
  else if ((payments.value as any).data && Array.isArray((payments.value as any).data)) {
    paymentsList = (payments.value as any).data
    console.log('✅ Format: Objet avec propriété "data"')
  }
  // Cas 3: Structure avec 'payments' ou 'transactions'
  else if ((payments.value as any).payments && Array.isArray((payments.value as any).payments)) {
    paymentsList = (payments.value as any).payments
    console.log('✅ Format: Objet avec propriété "payments"')
  } else if (
    (payments.value as any).transactions &&
    Array.isArray((payments.value as any).transactions)
  ) {
    paymentsList = (payments.value as any).transactions
    console.log('✅ Format: Objet avec propriété "transactions"')
  }
  // Cas 4: Objet avec propriétés numériques (IDs comme clés)
  else if (payments.value && typeof payments.value === 'object' && !Array.isArray(payments.value)) {
    paymentsList = Object.values(payments.value).filter(
      (item: any) => item && typeof item === 'object' && item.id,
    ) as PaymentData[]
    console.log('✅ Format: Objet avec IDs comme clés')
  }

  console.log('📊 Nombre de paiements extraits:', paymentsList.length)

  // Filtrer les paiements
  const filtered = paymentsList.filter((payment: PaymentData) => {
    // Filtre de recherche
    const searchLower = searchQuery.value.toLowerCase()
    const matchesSearch =
      !searchQuery.value ||
      payment.amount?.toString().includes(searchLower) ||
      payment.payment_method?.toLowerCase().includes(searchLower) ||
      payment.currency?.toLowerCase().includes(searchLower) ||
      payment.status?.toLowerCase().includes(searchLower)

    // Filtre par statut
    const matchesStatus = filterStatus.value === 'all' || payment.status === filterStatus.value

    // Filtre par devise
    const matchesCurrency =
      filterCurrency.value === 'all' ||
      payment.currency_id?.toString() === filterCurrency.value ||
      payment.currency?.toLowerCase().includes(filterCurrency.value.toLowerCase())

    // Filtre par méthode de paiement
    const matchesMethod =
      filterMethod.value === 'all' ||
      payment.payment_method_id?.toString() === filterMethod.value ||
      payment.payment_method?.toLowerCase().includes(filterMethod.value.toLowerCase())

    // Filtre par date
    let matchesDate = true
    if (filterDateFrom.value && payment.paid_at) {
      matchesDate = matchesDate && new Date(payment.paid_at) >= new Date(filterDateFrom.value)
    }
    if (filterDateTo.value && payment.paid_at) {
      matchesDate = matchesDate && new Date(payment.paid_at) <= new Date(filterDateTo.value)
    }

    return matchesSearch && matchesStatus && matchesCurrency && matchesMethod && matchesDate
  })

  console.log('🔎 Nombre de paiements après filtrage:', filtered.length)

  return filtered
})

// Fonctions utilitaires
const formatAmount = (amount: number | null | undefined): string => {
  if (!amount) return '0.00'
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

const formatDate = (dateString?: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getStatusLabel = (status: string): string => {
  const labels: Record<string, string> = {
    pending: 'En attente',
    confirmed: 'Confirmé',
    refunded: 'Remboursé',
  }
  return labels[status] || status
}

const getStatusVariant = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' => {
  const variants: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
    pending: 'secondary',
    confirmed: 'default',
    refunded: 'destructive',
  }
  return variants[status] || 'secondary'
}

// Fonction pour créer un nouveau paiement - CORRIGÉE
const onSubmitCreate = handleSubmit(async (values: PaymentFormData) => {
  try {
    console.log('Soumission du formulaire de paiement:', values)
    await createPayment(API_ROUTES.CREATE_PAYMENT, values)

    console.log('Réponse de création:', createResponse.value)
    console.log('Erreur de création:', createError.value)

    // Vérifier si l'opération a réussi
    if (createError.value) {
      showCustomToast({
        message: createError.value,
        type: 'error',
      })
      return // Ne pas fermer le dialog en cas d'erreur
    }

    if (createResponse.value && createResponse.value.success) {
      showCustomToast({
        message: createResponse.value.message || 'Paiement créé avec succès',
        type: 'success',
      })

      // Fermeture IMMÉDIATE du dialog
      isCreateDialogOpen.value = false

      // Nettoyage et rafraîchissement
      resetForm()
      await fetchData()
      eventBus.emit('paymentUpdated')
    } else {
      showCustomToast({
        message: 'Erreur lors de la création du paiement',
        type: 'error',
      })
    }
  } catch (err) {
    console.error('Erreur lors de la soumission:', err)
    showCustomToast({
      message: 'Erreur lors de la création du paiement',
      type: 'error',
    })
  }
})

// Fonction pour modifier un paiement - CORRIGÉE
const onSubmitEdit = handleSubmit(async (values: PaymentFormData) => {
  if (!editingItem.value) return

  try {
    console.log('Modification du paiement:', values)
    await updatePayment(API_ROUTES.UPDATE_PAYMENT(editingItem.value.id), values)

    console.log('Réponse de modification:', updateResponse.value)
    console.log('Erreur de modification:', updateError.value)

    // Vérifier si l'opération a réussi
    if (updateError.value) {
      showCustomToast({
        message: updateError.value,
        type: 'error',
      })
      return // Ne pas fermer le dialog en cas d'erreur
    }

    if (updateResponse.value && updateResponse.value.success) {
      showCustomToast({
        message: updateResponse.value.message || 'Paiement modifié avec succès',
        type: 'success',
      })

      // Fermeture IMMÉDIATE du dialog
      isEditDialogOpen.value = false
      editingItem.value = null

      // Nettoyage et rafraîchissement
      resetForm()
      await fetchData()
      eventBus.emit('paymentUpdated')
    } else {
      showCustomToast({
        message: 'Erreur lors de la modification du paiement',
        type: 'error',
      })
    }
  } catch (err) {
    console.error('Erreur lors de la modification:', err)
    showCustomToast({
      message: 'Erreur lors de la modification du paiement',
      type: 'error',
    })
  }
})

// Récupération des données au montage du composant
onMounted(async () => {
  await fetchData()
  await fetchPaymentMethods()
  await fetchPaymentMotifs()
  await fetchSchoolFees()
  await fetchFeeTypes()
})

// Fonction pour ouvrir le dialog de modification
const openEditDialog = (payment: PaymentData) => {
  editingItem.value = payment
  setValues({
    school_id: payment.school_id || '',
    user_id: payment.user_id,

    fee_id: payment.fee_id,
    payment_method_id: payment.payment_method_id,
    payment_motif_id: payment.payment_motif_id,
    currency_id: payment.currency_id,
    exchange_rate_id: payment.exchange_rate_id,
    account_type_id: payment.account_type_id,
    amount: payment.amount,
    amount_foreign: payment.amount_foreign,
    remaining_amount: payment.remaining_amount,
    details: payment.details || '',
    paid_at: payment.paid_at || '',
    status: payment.status,
  })
  isEditDialogOpen.value = true
}

// Fonction pour fermer le dialog de création
const closeCreateDialog = () => {
  isCreateDialogOpen.value = false
  resetForm()
}

// Fonction pour fermer le dialog d'édition
const closeEditDialog = () => {
  isEditDialogOpen.value = false
  editingItem.value = null
  resetForm()
}

// Fonction pour supprimer un paiement
const handleDelete = async (paymentId: number) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?')) {
    return
  }

  try {
    await deleteItem(API_ROUTES.DELETE_PAYMENT(paymentId))

    showCustomToast({
      message: 'Paiement supprimé avec succès',
      type: 'success',
    })

    await fetchData()
    eventBus.emit('paymentUpdated')
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la suppression du paiement',
      type: 'error',
    })
  }
}

// Fonction pour gérer la sélection multiple
const toggleSelection = (paymentId: number) => {
  if (selectedPayments.value.includes(paymentId)) {
    selectedPayments.value = selectedPayments.value.filter((id) => id !== paymentId)
  } else {
    selectedPayments.value.push(paymentId)
  }
}

// Fonction pour sélectionner/désélectionner tout
const toggleSelectAll = () => {
  if (
    selectedPayments.value.length === filteredPayments.value.length &&
    filteredPayments.value.length > 0
  ) {
    selectedPayments.value = []
  } else {
    selectedPayments.value = filteredPayments.value.map((payment) => payment.id)
  }
}

// Fonction pour supprimer les paiements sélectionnés
const deleteSelectedPayments = async () => {
  if (selectedPayments.value.length === 0) {
    showCustomToast({
      message: 'Aucun paiement sélectionné',
      type: 'warning',
    })
    return
  }

  if (
    !confirm(`Êtes-vous sûr de vouloir supprimer ${selectedPayments.value.length} paiement(s) ?`)
  ) {
    return
  }

  try {
    for (const paymentId of selectedPayments.value) {
      await deleteItem(API_ROUTES.DELETE_PAYMENT(paymentId))
    }

    showCustomToast({
      message: `${selectedPayments.value.length} paiement(s) supprimé(s) avec succès`,
      type: 'success',
    })

    selectedPayments.value = []
    await fetchData()
    eventBus.emit('paymentUpdated')
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la suppression des paiements',
      type: 'error',
    })
  }
}

const formatPaymentMethod = (method?: string | any) => {
  if (!method) return '-'

  // Si c'est un objet, extraire le nom
  if (typeof method === 'object' && method !== null) {
    return method.name || method.label || method.code || '-'
  }

  // Si c'est une chaîne, la retourner directement
  return method
}

const formatCurrency = (currency?: string | any) => {
  if (!currency) return '-'

  // Si c'est un objet, extraire le code ou le nom
  if (typeof currency === 'object' && currency !== null) {
    // Priorité: symbol + code, puis code seul, puis name
    if (currency.symbol && currency.code) {
      return `${currency.symbol} ${currency.code}`
    }
    return currency.code || currency.name || currency.symbol || '-'
  }

  // Si c'est une chaîne, la retourner directement
  return currency
}

const formatStatus = (status: string) => {
  return getStatusLabel(status)
}

// Variables d'état pour les composables
const createLoading = computed(() => creating.value)
const updateLoading = computed(() => updating.value)
const deleteLoading = computed(() => deleting.value)
const paymentsLoading = computed(() => loading.value)
const paymentsError = computed(() => error.value)

// Charger les données au montage
onMounted(async () => {
  await Promise.all([
    fetchData(),
    fetchPaymentMethods(),
    fetchPaymentMotifs(),
    fetchSchoolFees(),
    fetchFeeTypes(),
    fetchUsers(),
    fetchExchangeRates(),
    fetchAccountTypes(),
  ])
})

// Écoute des événements
eventBus.on('paymentUpdated', async () => {
  await fetchData()
})

// Écouter l'événement de création de paiement depuis NouveauPaiement.vue
eventBus.on('paiementCreated', async () => {
  console.log('🔄 Événement paiementCreated reçu, rafraîchissement des données...')
  await fetchData()
})
</script>

<template>
  <ComptaLayout activeBread="Paiements" active-tag-name="paiements" group="frais">
    <BoxPanelWrapper>
      <!-- Barre d'actions -->
      <div class="flex items-center gap-3 justify-between mb-4">
        <div class="relative flex-1 max-w-md">
          <Input
            type="text"
            v-model="searchQuery"
            placeholder="Rechercher par montant, devise ou date de paiement..."
            class="pl-10"
          />
          <span
            class="iconify hugeicons--search-01 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
          ></span>
        </div>

        <div class="flex items-center gap-2">
          <!-- Actions groupées -->
          <Button
            v-if="selectedPayments.length > 0"
            variant="destructive"
            size="sm"
            @click="deleteSelectedPayments"
            :disabled="deleteLoading"
          >
            <span class="iconify hugeicons--delete-02 mr-2"></span>
            Supprimer ({{ selectedPayments.length }})
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

          <!-- Bouton pour ouvrir le dialog -->
          <RouterLink to="/comptabilite/frais/paiements/nouveau">
            <Button size="md" class="bg-primary text-primary-foreground">
              <span class="iconify hugeicons--add-01 mr-2"></span>
              Nouveau paiement
            </Button>
          </RouterLink>
        </div>
      </div>

      <!-- Tableau des paiements -->
      <div class="border rounded-lg overflow-hidden flex flex-1 bg-white">
        <Table>
          <TableHeader>
            <TableRow class="bg-white">
              <TableHead class="w-[50px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="
                    selectedPayments.length === filteredPayments.length &&
                    filteredPayments.length > 0
                  "
                  @click="toggleSelectAll"
                />
              </TableHead>
              <TableHead class="w-[140px]">Montant</TableHead>
              <TableHead class="w-[140px]">Montant restant</TableHead>
              <TableHead class="w-[100px]">Devise</TableHead>
              <TableHead class="w-[150px]">Méthode</TableHead>
              <TableHead class="w-[120px]">Statut</TableHead>
              <TableHead class="w-[150px]">Date de paiement</TableHead>
              <TableHead class="w-[120px]">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <!-- État de chargement -->
            <TableRow v-if="paymentsLoading">
              <TableCell :colspan="7" class="text-center py-8">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--loading-03 animate-spin"></span>
                  Chargement des paiements...
                </div>
              </TableCell>
            </TableRow>

            <!-- État d'erreur -->
            <TableRow v-else-if="paymentsError">
              <TableCell :colspan="7" class="text-center py-8 text-red-500">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--alert-circle"></span>
                  Erreur lors du chargement : {{ paymentsError }}
                </div>
              </TableCell>
            </TableRow>

            <!-- Liste vide -->
            <TableRow v-else-if="filteredPayments.length === 0">
              <TableCell :colspan="7" class="text-center py-8 text-gray-500">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--invoice-02"></span>
                  {{
                    searchQuery
                      ? 'Aucun paiement trouvé pour cette recherche'
                      : 'Aucun paiement enregistré'
                  }}
                </div>
              </TableCell>
            </TableRow>

            <!-- Données -->
            <TableRow v-else v-for="payment in filteredPayments" :key="payment.id">
              <TableCell class="w-[40px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="selectedPayments.includes(payment.id)"
                  @click="toggleSelection(payment.id)"
                />
              </TableCell>
              <TableCell class="font-medium">{{ formatAmount(payment.amount) }}</TableCell>
              <TableCell>{{ formatAmount(payment.remaining_amount) }}</TableCell>
              <TableCell>{{ formatCurrency(payment.currency) }}</TableCell>
              <TableCell>{{ formatPaymentMethod(payment.payment_method) }}</TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(payment.status)">
                  {{ formatStatus(payment.status) }}
                </Badge>
              </TableCell>
              <TableCell>{{ formatDate(payment.paid_at || undefined) }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <Button
                    variant="outline"
                    size="icon"
                    class="size-8"
                    @click="openEditDialog(payment)"
                    title="Modifier le paiement"
                  >
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>
                  <Button
                    variant="destructive"
                    size="icon"
                    class="size-8"
                    @click="handleDelete(payment.id)"
                    title="Supprimer le paiement"
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

    <!-- Dialog de modification - CORRIGÉ -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent
        class="sm:max-w-[800px] max-h-[90vh] overflow-y-auto"
        @pointer-down-outside="closeEditDialog"
        @escape-key-down="closeEditDialog"
      >
        <DialogHeader>
          <DialogTitle>Modifier le paiement</DialogTitle>
          <DialogDescription> Modifiez les informations du paiement. </DialogDescription>
        </DialogHeader>
        <form @submit="onSubmitEdit" class="space-y-4">
          <!-- Informations de base -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Field name="school_id" v-slot="{ field, errorMessage }">
                <ListSchool v-bind="field" />
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="edit_user_id">Utilisateur *</Label>
              <Field name="user_id" v-slot="{ field, errorMessage }">
                <Select v-bind="field">
                  <SelectTrigger :class="{ 'border-red-500': errorMessage }">
                    <SelectValue placeholder="Sélectionner un utilisateur" />
                  </SelectTrigger>
                  <SelectContent>
                    <template v-if="loadingUsers">
                      <SelectItem value="loading" disabled>
                        Chargement des utilisateurs...
                      </SelectItem>
                    </template>
                    <template v-else-if="!users || users.length === 0">
                      <SelectItem value="empty" disabled> Aucune donnée disponible </SelectItem>
                    </template>
                    <template v-else>
                      <SelectItem
                        v-for="user in users"
                        :key="user.id || user.name"
                        :value="user.id ? user.id.toString() : 'null'"
                      >
                        {{ user.name || user.firstname || `Utilisateur #${user.id}` }}
                      </SelectItem>
                    </template>
                  </SelectContent>
                </Select>
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
          </div>

          <!-- Frais et méthode -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_fee_id">Frais *</Label>
              <Field name="fee_id" v-slot="{ field, errorMessage }">
                <Select v-bind="field">
                  <SelectTrigger :class="{ 'border-red-500': errorMessage }">
                    <SelectValue placeholder="Sélectionner un frais" />
                  </SelectTrigger>
                  <SelectContent>
                    <template v-if="loadingFees">
                      <SelectItem value="loading" disabled> Chargement des frais... </SelectItem>
                    </template>
                    <template v-else-if="!schoolFees || schoolFees.length === 0">
                      <SelectItem value="empty" disabled> Aucune donnée disponible </SelectItem>
                    </template>
                    <template v-else>
                      <SelectItem
                        v-for="fee in schoolFees"
                        :key="fee.id || fee.name"
                        :value="fee.id ? fee.id.toString() : 'null'"
                      >
                        {{ fee.name || fee.label || `Frais #${fee.id}` }}
                      </SelectItem>
                    </template>
                  </SelectContent>
                </Select>
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="edit_payment_method_id">Méthode de paiement *</Label>
              <Field name="payment_method_id" v-slot="{ field, errorMessage }">
                <Select v-bind="field">
                  <SelectTrigger :class="{ 'border-red-500': errorMessage }">
                    <SelectValue placeholder="Sélectionner une méthode" />
                  </SelectTrigger>
                  <SelectContent>
                    <template v-if="loadingMethods">
                      <SelectItem value="loading" disabled> Chargement des méthodes... </SelectItem>
                    </template>
                    <template v-else-if="!paymentMethods || paymentMethods.length === 0">
                      <SelectItem value="empty" disabled> Aucune donnée disponible </SelectItem>
                    </template>
                    <template v-else>
                      <SelectItem
                        v-for="method in paymentMethods"
                        :key="method.id || method.name"
                        :value="method.id ? method.id.toString() : 'null'"
                      >
                        {{ method.name || `Méthode #${method.id}` }}
                      </SelectItem>
                    </template>
                  </SelectContent>
                </Select>
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
          </div>

          <!-- Motif et devise -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_payment_motif_id">Motif de paiement *</Label>
              <Field name="payment_motif_id" v-slot="{ field, errorMessage }">
                <Select v-bind="field">
                  <SelectTrigger :class="{ 'border-red-500': errorMessage }">
                    <SelectValue placeholder="Sélectionner un motif" />
                  </SelectTrigger>
                  <SelectContent>
                    <template v-if="loadingMotifs">
                      <SelectItem value="loading" disabled> Chargement des motifs... </SelectItem>
                    </template>
                    <template v-else-if="!paymentMotifs || paymentMotifs.length === 0">
                      <SelectItem value="empty" disabled> Aucune donnée disponible </SelectItem>
                    </template>
                    <template v-else>
                      <SelectItem
                        v-for="motif in paymentMotifs"
                        :key="motif.id || motif.name"
                        :value="motif.id ? motif.id.toString() : 'null'"
                      >
                        {{ motif.name || motif.label || `Motif #${motif.id}` }}
                      </SelectItem>
                    </template>
                  </SelectContent>
                </Select>
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="edit_currency_id">Devise *</Label>
              <Field name="currency_id" v-slot="{ field, errorMessage }">
                <ListCurrency v-bind="field" />
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
          </div>

          <!-- Taux de change et type de compte -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_exchange_rate_id">Taux de change *</Label>
              <Field name="exchange_rate_id" v-slot="{ field, errorMessage }">
                <Select v-bind="field">
                  <SelectTrigger :class="{ 'border-red-500': errorMessage }">
                    <SelectValue placeholder="Sélectionner un taux" />
                  </SelectTrigger>
                  <SelectContent>
                    <template v-if="loadingRates">
                      <SelectItem value="loading" disabled> Chargement des taux... </SelectItem>
                    </template>
                    <template v-else-if="!exchangeRates || exchangeRates.length === 0">
                      <SelectItem value="empty" disabled> Aucune donnée disponible </SelectItem>
                    </template>
                    <template v-else>
                      <SelectItem
                        v-for="rate in exchangeRates"
                        :key="rate.id || rate.name"
                        :value="rate.id ? rate.id.toString() : 'null'"
                      >
                        {{ rate.name || rate.label || `Taux #${rate.id}` }}
                      </SelectItem>
                    </template>
                  </SelectContent>
                </Select>
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="edit_account_type_id">Type de compte *</Label>
              <Field name="account_type_id" v-slot="{ field, errorMessage }">
                <Select v-bind="field">
                  <SelectTrigger :class="{ 'border-red-500': errorMessage }">
                    <SelectValue placeholder="Sélectionner un type" />
                  </SelectTrigger>
                  <SelectContent>
                    <template v-if="loadingAccountTypes">
                      <SelectItem value="loading" disabled> Chargement des types... </SelectItem>
                    </template>
                    <template v-else-if="!accountTypes || accountTypes.length === 0">
                      <SelectItem value="empty" disabled> Aucune donnée disponible </SelectItem>
                    </template>
                    <template v-else>
                      <SelectItem
                        v-for="type in accountTypes"
                        :key="type.id || type.name"
                        :value="type.id ? type.id.toString() : 'null'"
                      >
                        {{ type.name || type.label || `Type #${type.id}` }}
                      </SelectItem>
                    </template>
                  </SelectContent>
                </Select>
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
          </div>

          <!-- Montants -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_amount">Montant *</Label>
              <Field name="amount" v-slot="{ field, errorMessage }">
                <Input
                  id="edit_amount"
                  v-bind="field"
                  type="number"
                  step="0.01"
                  placeholder="1000.00"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="edit_remaining_amount">Montant restant</Label>
              <Field name="remaining_amount" v-slot="{ field, errorMessage }">
                <Input
                  id="edit_remaining_amount"
                  v-bind="field"
                  type="number"
                  step="0.01"
                  placeholder="0.00"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
          </div>

          <!-- Dates -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_paid_at">Date de paiement</Label>
              <Field name="paid_at" v-slot="{ field, errorMessage }">
                <Input
                  id="edit_paid_at"
                  v-bind="field"
                  type="datetime-local"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
            <div class="space-y-2">
              <Label for="edit_status">Statut</Label>
              <Field name="status" v-slot="{ field, errorMessage }">
                <Select v-bind="field">
                  <SelectTrigger :class="{ 'border-red-500': errorMessage }">
                    <SelectValue placeholder="Sélectionner un statut" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="pending">En attente</SelectItem>
                    <SelectItem value="confirmed">Confirmé</SelectItem>
                    <SelectItem value="refunded">Remboursé</SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
          </div>

          <!-- Détails -->
          <div class="space-y-2">
            <Label for="edit_details">Détails</Label>
            <Field name="details" v-slot="{ field, errorMessage }">
              <Textarea
                id="edit_details"
                v-bind="field"
                placeholder="Détails du paiement..."
                rows="3"
                :class="{ 'border-red-500': errorMessage }"
              />
              <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
            </Field>
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
