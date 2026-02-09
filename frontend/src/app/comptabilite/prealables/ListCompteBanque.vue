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
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { ref, reactive, watch } from 'vue'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import NewCompteBanque from '@/components/modals/compta/NewCompteBanque.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
import { useSearch } from '@/composables/useSearch'
import { usePagination } from '@/composables/usePagination'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'

// Interface pour les comptes bancaires
interface BankAccount {
  id: number
  name: string
  code: string
  number: string
  created_at?: string
}

// API pour récupérer les comptes bancaires
const {
  data: accountsData,
  error: accountsError,
  loading: accountsLoading,
  fetchData: fetchAccounts,
  meta,
} = useGetApi<BankAccount[]>(API_ROUTES.GET_BANK_ACCOUNTS)

// API pour supprimer un compte
const { deleteItem, errorDelete, deleting } = useDeleteApi()

// Variables pour la sélection
const selectedAccounts = ref<number[]>([])

// Paramètres de filtrage
const filterParams = reactive({
  code: undefined as string | undefined,
  name: undefined as string | undefined,
})

// Custom labels pour les filtres
const customLabels = {
  code: (value: string) => value || '—',
  name: (value: string) => value || '—',
}

// Fonction pour retirer un filtre
const removeFilter = (key: string) => {
  if (key === 'code') filterParams.code = undefined
  if (key === 'name') filterParams.name = undefined
}

// Search avec debounce
const { query } = useSearch((params: { search: string }) => {
  fetchAccounts({ page: page.value, per_page: perPageCount.value, ...filterParams, ...params })
}, 500)

// Pagination
const { page, perPageCount, total } = usePagination(fetchAccounts, 1, 15, {
  pageParam: 'page',
  perPageParam: 'per_page',
})

// Fetch initial
fetchAccounts({ page: page.value, per_page: perPageCount.value })

// Watch pour les filtres
watch(
  [() => filterParams.code, () => filterParams.name],
  () => {
    const params: Record<string, string | number | undefined> = {
      page: page.value,
      per_page: perPageCount.value,
      search: query.value,
    }
    if (filterParams.code) params.code = filterParams.code
    if (filterParams.name) params.name = filterParams.name

    fetchAccounts(params)
  },
  { deep: true },
)

// Watch meta pour synchroniser la pagination
if (meta) {
  watch(
    () => meta.value,
    (m) => {
      if (!m) return
      if (typeof m.total === 'number') total.value = m.total
      if (typeof m.per_page === 'number' && perPageCount.value !== m.per_page) {
        perPageCount.value = m.per_page
      }
      if (typeof m.current_page === 'number' && page.value !== m.current_page) {
        page.value = m.current_page
      }
    },
  )
}

// Écouter les événements de mise à jour
// eslint-disable-next-line @typescript-eslint/no-explicit-any
eventBus.on('accountUpdated' as any, () => {
  fetchAccounts({
    page: page.value,
    per_page: perPageCount.value,
    search: query.value,
    ...filterParams,
  })
})

// Fonction pour supprimer un compte
const deleteAccount = async (id: number) => {
  try {
    const endpoint = API_ROUTES.DELETE_BANK_ACCOUNT(id)
    const result = await deleteItem(endpoint)
    if (result === null) throw new Error('Suppression échouée')
    showCustomToast({
      message: 'Compte bancaire supprimé avec succès',
      type: 'success',
    })

    // Rafraîchir la liste
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    eventBus.emit('accountUpdated' as any)
  } catch {
    const errorMessage = errorDelete.value || 'Erreur lors de la suppression du compte'
    showCustomToast({
      message: errorMessage,
      type: 'error',
    })
  }
}

// Fonction pour supprimer les comptes sélectionnés
const deleteSelectedAccounts = async () => {
  if (selectedAccounts.value.length === 0) {
    showCustomToast({
      message: 'Aucun compte sélectionné',
      type: 'error',
    })
    return
  }

  const accountNames = selectedAccounts.value
    .map((id) => accountsData.value?.find((a) => a.id === id)?.name)
    .filter(Boolean)
    .join(', ')

  if (
    !confirm(
      `Êtes-vous sûr de vouloir supprimer ${selectedAccounts.value.length} compte(s) ?\n\n${accountNames}`,
    )
  ) {
    return
  }

  try {
    for (const id of selectedAccounts.value) {
      const endpoint = API_ROUTES.DELETE_BANK_ACCOUNT(id)
      await deleteItem(endpoint)
    }

    showCustomToast({
      message: `${selectedAccounts.value.length} compte(s) supprimé(s) avec succès`,
      type: 'success',
    })

    selectedAccounts.value = []
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    eventBus.emit('accountUpdated' as any)
  } catch {
    showCustomToast({
      message: 'Erreur lors de la suppression des comptes',
      type: 'error',
    })
  }
}

// Fonction pour sélectionner/désélectionner tous les comptes
const toggleSelectAll = () => {
  if (selectedAccounts.value.length === accountsData.value?.length) {
    selectedAccounts.value = []
  } else {
    selectedAccounts.value = accountsData.value?.map((a) => a.id) || []
  }
}

// Formater la date
const formatDate = (dateString?: string) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

// Fonction pour mettre à jour perPage
function onPerPageUpdate(val: number) {
  page.value = 1
  perPageCount.value = val
}
</script>

<template>
  <ComptaLayout activeBread="Comptes banque" active-tag-name="comptes-banque" group="saisie">
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative w-full max-w-xs">
            <Input
              v-model="query"
              type="text"
              id="search"
              name="search"
              placeholder="Recherche..."
              class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
            />
            <div
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
            >
              <span class="flex iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <!-- <Popover>
            <PopoverTrigger as-child>
              <Button variant="ghost" size="sm" class="h-10 rounded-md border bg-white">
                <span class="hidden sm:flex"> Filtre</span>
                <span class="iconify hugeicons--filter">Filtre</span>
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-80">
              <div class="grid gap-4">
                <div class="space-y-2">
                  <h4 class="font-medium leading-none">Filtrage</h4>
                </div>
                <div class="flex flex-col gap-3.5">
                  <Input
                    v-model="filterParams.code"
                    placeholder="Filtrer par code"
                    class="w-full border border-gray-200/40 bg-white h-10 rounded-md"
                  />
                  <Input
                    v-model="filterParams.name"
                    placeholder="Filtrer par nom"
                    class="w-full border border-gray-200/40 bg-white h-10 rounded-md"
                  />
                </div>
              </div>
            </PopoverContent>
          </Popover> -->
          <FilterBadges
            :filters="filterParams"
            :custom-labels="customLabels"
            @remove-filter="removeFilter"
          />
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <Button
            v-if="selectedAccounts.length > 0"
            variant="destructive"
            size="md"
            @click="deleteSelectedAccounts"
            :disabled="deleting"
          >
            <span class="iconify hugeicons--delete-02 mr-1"></span>
            Supprimer ({{ selectedAccounts.length }})
          </Button>
          <NewCompteBanque />
        </div>
      </div>

      <!-- État de chargement -->
      <div v-if="accountsLoading" class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8">
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--loading-03 text-4xl animate-spin text-primary"></span>
          <p class="text-sm text-foreground-muted">Chargement des comptes bancaires...</p>
        </div>
      </div>

      <!-- État d'erreur -->
      <div
        v-else-if="accountsError"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
      >
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--alert-circle text-4xl text-red-500"></span>
          <p class="text-sm text-foreground-muted">{{ accountsError }}</p>
          <Button @click="fetchAccounts" variant="outline" size="sm">
            <span class="iconify hugeicons--refresh mr-1"></span>
            Réessayer
          </Button>
        </div>
      </div>

      <!-- Liste vide -->
      <div
        v-else-if="!accountsData || accountsData.length === 0"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
      >
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--bank text-4xl text-foreground-muted/50"></span>
          <p class="text-sm text-foreground-muted">
            {{
              query
                ? 'Aucun compte bancaire trouvé pour cette recherche'
                : 'Aucun compte bancaire enregistré'
            }}
          </p>
        </div>
      </div>

      <!-- Tableau des données -->
      <div v-else class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white">
        <Table class="rounded-md bg-white">
          <TableHeader>
            <TableRow>
              <TableHead class="w-[20px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="
                    selectedAccounts.length === accountsData.length && accountsData.length > 0
                  "
                  @update:checked="toggleSelectAll"
                />
              </TableHead>
              <TableHead class="w-[60px]">N°</TableHead>
              <TableHead>Code</TableHead>
              <TableHead>Numéro de compte</TableHead>
              <TableHead>Nom du compte</TableHead>
              <TableHead>Date de création</TableHead>
              <TableHead> Opérations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(account, index) in accountsData" :key="account.id">
              <TableCell class="w-[40px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="selectedAccounts.includes(account.id)"
                  @update:checked="
                    (checked: boolean) => {
                      if (checked) {
                        selectedAccounts.push(account.id)
                      } else {
                        selectedAccounts = selectedAccounts.filter((id) => id !== account.id)
                      }
                    }
                  "
                />
              </TableCell>
              <TableCell>{{ (page - 1) * perPageCount + (index + 1) }}</TableCell>
              <TableCell>{{ account.code || '-' }}</TableCell>
              <TableCell>{{ account.number || '-' }}</TableCell>
              <TableCell>{{ account.name || '-' }}</TableCell>
              <TableCell>{{ formatDate(account.created_at) }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <NewCompteBanque :account-id="account.id">
                    <template #trigger>
                      <Button variant="outline" size="icon" class="size-8">
                        <span class="iconify hugeicons--edit-02"></span>
                      </Button>
                    </template>
                  </NewCompteBanque>
                  <AlertMessage
                    action="danger"
                    title="Supprimer un compte bancaire"
                    :message="`Vous êtes sur le point de supprimer le compte '${account.name}'. Êtes-vous sûr de continuer?`"
                  >
                    <template #trigger>
                      <Button
                        variant="ghost"
                        size="icon"
                        class="size-8 justify-center text-gray-500 hover:text-red-600 hover:bg-red-50"
                      >
                        <span class="iconify hugeicons--delete-02"></span>
                      </Button>
                    </template>
                    <template #confirm-action-button>
                      <Button
                        variant="destructive"
                        size="sm"
                        class="h-10 px-4"
                        @click.stop="deleteAccount(account.id)"
                        :disabled="deleting"
                      >
                        Oui, Supprimer
                      </Button>
                    </template>
                  </AlertMessage>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <TabPagination
        v-model="page"
        :perPage="perPageCount"
        :totalItems="total"
        @update:perPage="onPerPageUpdate"
      />
    </BoxPanelWrapper>
  </ComptaLayout>
</template>
