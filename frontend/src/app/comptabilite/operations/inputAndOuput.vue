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
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectItem,
  SelectGroup,
  SelectContent,
} from '@/components/ui/select'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { usePostApi } from '@/composables/usePostApi.ts'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { Field, useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import * as z from 'zod'
import { Link } from 'lucide-vue-next'

// Détecter la route actuelle
const route = useRoute()

interface InputAccount {
  id: number
  name: string
  account_plan_id: number
  sub_account_plan_id: number
  amount: number
  justification: string
  created_at?: string
}

interface OutputAccount {
  id: number
  name: string
  account_plan_id: number
  sub_account_plan_id: number
  amount: number
  justification: string
  created_at?: string
}

interface ApiResponse {
  success: boolean
  message: string
}

const inputSchema = z.object({
  name: z.string().min(1, 'Le libellé est requis').max(255, '255 caractères maximum'),
  account_plan_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'Le compte principal est requis')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'Le compte principal est requis'),
  sub_account_plan_id: z
    .union([
      z
        .string()
        .refine((v) => v !== '', 'Le sous-compte est requis')
        .transform((v) => Number(v)),
      z.number(),
    ])
    .refine((v) => Number(v) > 0, 'Le sous-compte est requis'),
  amount: z.coerce
    .number({ invalid_type_error: 'Le montant doit être un nombre' })
    .min(0, 'Le montant doit être positif'),
  justification: z
    .string()
    .min(1, 'La justification est requise')
    .max(255, '255 caractères maximum'),
})

const outputSchema = inputSchema

const {
  data: inputsData,
  error: inputsError,
  loading: inputsLoading,
  fetchData: fetchInputs,
} = useGetApi<InputAccount[]>(API_ROUTES.GET_INPUTACCOUNT)
const {
  putData: updateInput,
  error: updateInputError,
  loading: updatingInput,
} = usePutApi<ApiResponse>()
const { deleteItem: deleteInput, errorDelete: deleteInputError } = useDeleteApi<ApiResponse>()

const {
  data: outputsData,
  error: outputsError,
  loading: outputsLoading,
  fetchData: fetchOutputs,
} = useGetApi<OutputAccount[]>(API_ROUTES.GET_OUTPUTACCOUNT)
const {
  putData: updateOutput,
  error: updateOutputError,
  loading: updatingOutput,
} = usePutApi<ApiResponse>()
const { deleteItem: deleteOutput, errorDelete: deleteOutputError } = useDeleteApi<ApiResponse>()

const {
  data: plansData,
  loading: loadingPlans,
  fetchData: fetchPlans,
} = useGetApi<any[]>(API_ROUTES.GET_COMPTE)
const {
  data: subsData,
  loading: loadingSubs,
  fetchData: fetchSubs,
} = useGetApi<any[]>(API_ROUTES.GET_SUB_COMPTE)

function mapList(v: any): any[] {
  let list: any[] = []
  if (Array.isArray(v)) list = v
  else if (v?.data && Array.isArray(v.data)) list = v.data
  else if (v?.items && Array.isArray(v.items)) list = v.items
  else if (v?.accounts && Array.isArray(v.accounts)) list = v.accounts
  else if (v && typeof v === 'object')
    list = Object.values(v).filter(
      (item: any) => item && typeof item === 'object' && (item.id || item.value),
    )
  return list
}

const accountPlans = computed(() =>
  mapList(plansData.value).map((it: any) => ({
    id: String(it.id ?? it.value ?? ''),
    name: it.name ?? it.label ?? it.libelle ?? it.title ?? `Compte #${it.id ?? it.value}`,
    code: it.code ?? '',
  })),
)

const subAccountPlans = computed(() =>
  mapList(subsData.value).map((it: any) => ({
    id: String(it.id ?? it.value ?? ''),
    name: it.name ?? it.label ?? it.libelle ?? it.title ?? `Sous-compte #${it.id ?? it.value}`,
    code: it.code ?? '',
    account_plan_id: it.account_plan_id ?? it.accountPlanId ?? null,
  })),
)

const searchQueryInput = ref('')
const searchQueryOutput = ref('')
const isEditInputDialogOpen = ref(false)
const isEditOutputDialogOpen = ref(false)
const editingInput = ref<InputAccount | null>(null)
const editingOutput = ref<OutputAccount | null>(null)

const {
  handleSubmit: handleSubmitEditInput,
  resetForm: resetEditInputForm,
  setValues: setEditInputValues,
  values: editInputValues,
} = useForm({
  validationSchema: toTypedSchema(inputSchema),
})

const {
  handleSubmit: handleSubmitEditOutput,
  resetForm: resetEditOutputForm,
  setValues: setEditOutputValues,
  values: editOutputValues,
} = useForm({
  validationSchema: toTypedSchema(outputSchema),
})

const filteredSubAccountsForEditInput = computed(() => {
  const planId = editInputValues.account_plan_id
  if (!planId) return []
  return subAccountPlans.value.filter((sc) => String(sc.account_plan_id) === String(planId))
})

const filteredSubAccountsForEditOutput = computed(() => {
  const planId = editOutputValues.account_plan_id
  if (!planId) return []
  return subAccountPlans.value.filter((sc) => String(sc.account_plan_id) === String(planId))
})

watch(
  () => editInputValues.account_plan_id,
  () => setEditInputValues({ ...editInputValues, sub_account_plan_id: '' }),
)
watch(
  () => editOutputValues.account_plan_id,
  () => setEditOutputValues({ ...editOutputValues, sub_account_plan_id: '' }),
)

const inputs = computed(() => {
  const v: any = inputsData.value
  let list: any[] = []
  if (Array.isArray(v)) list = v
  else if (v?.data && Array.isArray(v.data)) list = v.data
  else if (v && typeof v === 'object' && !Array.isArray(v))
    list = Object.values(v).filter((item: any) => item && typeof item === 'object' && item.id)
  return list as InputAccount[]
})

const outputs = computed(() => {
  const v: any = outputsData.value
  let list: any[] = []
  if (Array.isArray(v)) list = v
  else if (v?.data && Array.isArray(v.data)) list = v.data
  else if (v && typeof v === 'object' && !Array.isArray(v))
    list = Object.values(v).filter((item: any) => item && typeof item === 'object' && item.id)
  return list as OutputAccount[]
})

const filteredInputs = computed(() => {
  const query = searchQueryInput.value.toLowerCase()
  return inputs.value.filter(
    (item) =>
      item.name.toLowerCase().includes(query) || item.justification?.toLowerCase().includes(query),
  )
})

const filteredOutputs = computed(() => {
  const query = searchQueryOutput.value.toLowerCase()
  return outputs.value.filter(
    (item) =>
      item.name.toLowerCase().includes(query) || item.justification?.toLowerCase().includes(query),
  )
})

const onSubmitEditInput = handleSubmitEditInput(async (values) => {
  if (!editingInput.value) return
  try {
    await updateInput(API_ROUTES.UPDATE_INPUTACCOUNT(editingInput.value.id), values)
    if (updateInputError.value) {
      showCustomToast({ message: 'Erreur lors de la modification', type: 'error' })
      return
    }
    showCustomToast({ message: 'Entrée modifiée avec succès', type: 'success' })
    isEditInputDialogOpen.value = false
    resetEditInputForm()
    editingInput.value = null
    await fetchInputs()
  } catch (err) {
    showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
  }
})

const openEditInputDialog = (item: InputAccount) => {
  editingInput.value = item
  setEditInputValues({
    name: item.name,
    account_plan_id: item.account_plan_id?.toString() || '',
    sub_account_plan_id: item.sub_account_plan_id?.toString() || '',
    amount: item.amount,
    justification: item.justification,
  })
  isEditInputDialogOpen.value = true
}

const handleDeleteInput = async (id: number) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette entrée ?')) return
  try {
    await deleteInput(API_ROUTES.DELETE_INPUTACCOUNT(id))
    if (deleteInputError.value) {
      showCustomToast({ message: 'Erreur lors de la suppression', type: 'error' })
      return
    }
    showCustomToast({ message: 'Entrée supprimée avec succès', type: 'success' })
    await fetchInputs()
  } catch (err) {
    showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
  }
}

const onSubmitEditOutput = handleSubmitEditOutput(async (values) => {
  if (!editingOutput.value) return
  try {
    await updateOutput(API_ROUTES.UPDATE_OUTPUTACCOUNT(editingOutput.value.id), values)
    if (updateOutputError.value) {
      showCustomToast({ message: 'Erreur lors de la modification', type: 'error' })
      return
    }
    showCustomToast({ message: 'Sortie modifiée avec succès', type: 'success' })
    isEditOutputDialogOpen.value = false
    resetEditOutputForm()
    editingOutput.value = null
    await fetchOutputs()
  } catch (err) {
    showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
  }
})

const openEditOutputDialog = (item: OutputAccount) => {
  editingOutput.value = item
  setEditOutputValues({
    name: item.name,
    account_plan_id: item.account_plan_id?.toString() || '',
    sub_account_plan_id: item.sub_account_plan_id?.toString() || '',
    amount: item.amount,
    justification: item.justification,
  })
  isEditOutputDialogOpen.value = true
}

const handleDeleteOutput = async (id: number) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette sortie ?')) return
  try {
    await deleteOutput(API_ROUTES.DELETE_OUTPUTACCOUNT(id))
    if (deleteOutputError.value) {
      showCustomToast({ message: 'Erreur lors de la suppression', type: 'error' })
      return
    }
    showCustomToast({ message: 'Sortie supprimée avec succès', type: 'success' })
    await fetchOutputs()
  } catch (err) {
    showCustomToast({ message: 'Une erreur est survenue', type: 'error' })
  }
}

const formatDate = (dateString?: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR')
}

const formatAmount = (amount: number) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'CDF' }).format(amount)
}

const getAccountName = (accountId: number) => {
  const account = accountPlans.value.find((a) => String(a.id) === String(accountId))
  return account ? `${account.code ? account.code + ' - ' : ''}${account.name}` : '-'
}

const getSubAccountName = (subAccountId: number) => {
  const subAccount = subAccountPlans.value.find((sa) => String(sa.id) === String(subAccountId))
  return subAccount ? `${subAccount.code ? subAccount.code + ' - ' : ''}${subAccount.name}` : '-'
}

// Vérifier si on est sur la page des recettes (Encaissement & Décaissement)
const isOnRecettesPage = computed(() => {
  return (
    route.path === '/comptabilite/recettes' ||
    route.path.includes('/comptabilite/nouvelle-entree') ||
    route.path.includes('/comptabilite/nouvelle-sortie')
  )
})

onMounted(async () => {
  await Promise.all([fetchInputs(), fetchOutputs(), fetchPlans(), fetchSubs()])
})
</script>

<template>
  <ComptaLayout activeBread="Entrées et Sorties" active-tag-name="recettes" group="operations">
    <div class="grid lg:grid-cols-2 gap-7">
      <!-- Section Entrées/Recettes -->
      <BoxPanelWrapper>
        <span class="text-gray-500 my-1.5 text-xl">Recettes</span>
        <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between mb-4">
          <div class="flex gap-20">
            <Input v-model="searchQueryInput" placeholder="Rechercher..." class="w-64" />
            <RouterLink to="/comptabilite/saisie-operations/nouvelle-entree">
              <Button size="md">
                <span class="iconify hugeicons--add-01 mr-2"></span>
                Nouvelle Entrée
              </Button>
            </RouterLink>
          </div>
        </div>

        <!-- Tableau des entrées -->
        <div class="overflow-x-auto">
          <Table v-if="!inputsLoading && !inputsError">
            <TableHeader>
              <TableRow>
                <TableHead>Libellé</TableHead>
                <TableHead>Compte</TableHead>
                <TableHead>Montant</TableHead>
                <TableHead>Date</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="filteredInputs.length === 0">
                <TableCell colspan="5" class="text-center text-gray-500">
                  {{ searchQueryInput ? 'Aucune entrée trouvée' : 'Aucune entrée enregistrée' }}
                </TableCell>
              </TableRow>
              <TableRow v-for="item in filteredInputs" :key="item.id">
                <TableCell>{{ item.name }}</TableCell>
                <TableCell>{{ getAccountName(item.account_plan_id) }}</TableCell>
                <TableCell>{{ formatAmount(item.amount) }}</TableCell>
                <TableCell>{{ formatDate(item.created_at) }}</TableCell>
                <TableCell class="text-right">
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="sm">
                        <span class="iconify hugeicons--more-vertical text-lg"></span>
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem @click="openEditInputDialog(item)">
                        <span class="iconify hugeicons--pencil-edit-01 mr-2"></span>
                        Modifier
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="handleDeleteInput(item.id)" class="text-red-600">
                        <span class="iconify hugeicons--delete-02 mr-2"></span>
                        Supprimer
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
          <div v-if="inputsLoading" class="text-center py-8">
            <span class="iconify hugeicons--loading-03 animate-spin text-2xl"></span>
            <p class="text-gray-500 mt-2">Chargement des entrées...</p>
          </div>
          <div v-if="inputsError" class="text-center py-8 text-red-500">
            <span class="iconify hugeicons--alert-circle text-2xl"></span>
            <p class="mt-2">Erreur lors du chargement des entrées</p>
          </div>
        </div>
      </BoxPanelWrapper>

      <!-- Section Sorties/Dépenses -->
      <BoxPanelWrapper>
        <span class="text-gray-500 my-1.5 text-xl">Dépenses</span>
        <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between mb-4">
          <div class="flex gap-20">
            <Input v-model="searchQueryOutput" placeholder="Rechercher..." class="w-64" />
            <RouterLink to="/comptabilite/saisie-operations/nouvelle-sortie">
              <Button size="md">
                <span class="iconify hugeicons--add-01 mr-2"></span>
                Nouvelle Sortie
              </Button>
            </RouterLink>
          </div>
        </div>

        <!-- Tableau des sorties -->
        <div class="overflow-x-auto">
          <Table v-if="!outputsLoading && !outputsError">
            <TableHeader>
              <TableRow>
                <TableHead>Libellé</TableHead>
                <TableHead>Compte</TableHead>
                <TableHead>Montant</TableHead>
                <TableHead>Date</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="filteredOutputs.length === 0">
                <TableCell colspan="5" class="text-center text-gray-500">
                  {{ searchQueryOutput ? 'Aucune sortie trouvée' : 'Aucune sortie enregistrée' }}
                </TableCell>
              </TableRow>
              <TableRow v-for="item in filteredOutputs" :key="item.id">
                <TableCell>{{ item.name }}</TableCell>
                <TableCell>{{ getAccountName(item.account_plan_id) }}</TableCell>
                <TableCell>{{ formatAmount(item.amount) }}</TableCell>
                <TableCell>{{ formatDate(item.created_at) }}</TableCell>
                <TableCell class="text-right">
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="sm">
                        <span class="iconify hugeicons--more-vertical text-lg"></span>
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem @click="openEditOutputDialog(item)">
                        <span class="iconify hugeicons--pencil-edit-01 mr-2"></span>
                        Modifier
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="handleDeleteOutput(item.id)" class="text-red-600">
                        <span class="iconify hugeicons--delete-02 mr-2"></span>
                        Supprimer
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
          <div v-if="outputsLoading" class="text-center py-8">
            <span class="iconify hugeicons--loading-03 animate-spin text-2xl"></span>
            <p class="text-gray-500 mt-2">Chargement des sorties...</p>
          </div>
          <div v-if="outputsError" class="text-center py-8 text-red-500">
            <span class="iconify hugeicons--alert-circle text-2xl"></span>
            <p class="mt-2">Erreur lors du chargement des sorties</p>
          </div>
        </div>
      </BoxPanelWrapper>
    </div>
  </ComptaLayout>
</template>
