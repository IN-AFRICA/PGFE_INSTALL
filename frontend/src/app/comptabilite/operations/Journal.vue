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
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import { ref, onMounted, computed } from 'vue'
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import api from '@/services/api'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
import { useFileExport } from '@/composables/useFileExport.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import ExportDropdown from '@/components/ExportDropdown.vue'

const {
  data: journalData,
  loading: journalLoading,
  error: journalError,
  fetchData: fetchJournalEntries,
} = useGetApi<JournalEntry[]>(API_ROUTES.GET_JOURNAL)

// Interface pour les écritures de journal
interface JournalEntry {
  id: number
  date: string
  abandoned: boolean
  account_id: string
  account_plan_id: string
  description: string
  input_account_id: string
  linked_journal?: string
  linked_journal_id?: string
  montant: string
  output_account_id: string
  sub_account_plan_id: string
  updated_at: string
  label: string
  debit: number
  credit: number
  created_at?: string
  account: {
    id: number
    name: string
    number: string
    code: string
    solde: string
    school_id: string
    user_id: string
    created_at: string
    updated_at: string
  }
  account_plan: {
    id: number
    name: string
    code: string
    category_compatibility_id: string
    class_compatibility_id: string
    school_id: string
    user_id: string
    created_at: string
    updated_at: string
  }
  input_account: {
    id: number
    name: string
    amount: string
    justification: string
    account_plan_id: string
    sub_account_plan_id: string
    school_id: string
    user_id: string
    created_at: string
    updated_at: string
  }
  output_account: {
    id: number
    name: string
    amount: string
    justification: string
    account_plan_id: string
    sub_account_plan_id: string
    school_id: string
    user_id: string
    created_at: string
    updated_at: string
  }
  sub_account_plan: {
    id: number
    name: string
    code: string
    account_plan_id: string
    created_at: string
    updated_at: string
  }
  piece_number?: string
}

// Variables pour la recherche et la pagination
const searchQuery = ref('')
const page = ref(1)
const perPage = ref(15)
const selectedEntries = ref<number[]>([])

// API pour supprimer une écriture
const { deleteItem, deleting } = useDeleteApi()

// Filtrer les écritures selon la recherche
const filteredEntries = computed(() => {
  if (!journalData.value) return []

  if (!searchQuery.value) {
    return journalData.value
  }

  const query = searchQuery.value.toLowerCase()
  return journalData.value.filter(
    (entry) =>
      entry.piece_number?.toLowerCase().includes(query) ||
      entry.account.code?.toLowerCase().includes(query) ||
      entry.account.name?.toLowerCase().includes(query) ||
      entry.label?.toLowerCase().includes(query),
  )
})

// Pagination des écritures
const paginatedEntries = computed(() => {
  const start = (page.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredEntries.value.slice(start, end)
})

const total = computed(() => filteredEntries.value.length)

// NOTE: deletion handled via `deactivateEntry` (abandons). Removed direct delete function.

// Fonction pour supprimer les écritures sélectionnées
const deactivateSelectedEntries = async () => {
  if (selectedEntries.value.length === 0) {
    showCustomToast({
      message: 'Aucune écriture sélectionnée',
      type: 'error',
    })
    return
  }

  if (
    !confirm(`Êtes-vous sûr de vouloir supprimer ${selectedEntries.value.length} écriture(s) ?`)
  ) {
    return
  }

  try {
    let successCount = 0

    for (const id of selectedEntries.value) {
      const endpoint = API_ROUTES.ABANDONED_JOURNAL_ENTRY(id)
      const response = await deleteItem(endpoint)

      // Vérifier si la réponse indique un succès
      if (response && response.success) {
        successCount++
      }
    }

    showCustomToast({
      message: `${successCount} ${successCount > 1 ? 'journaux' : 'journal'} abandonné${
        successCount > 1 ? 's' : ''
      } avec succès`,
      type: 'success',
    })

    selectedEntries.value = []
    eventBus.emit('journalUpdated')
  } catch (err) {
    console.log('erreur suppression journal : ', err)
    showCustomToast({
      message: 'Erreur lors de la suppression des journaux',
      type: 'error',
    })
  }
}

// Désactiver (abandonner) une écriture (single)
const deactivateEntry = async (id: number, pieceNumber?: string) => {
  if (!confirm(`Êtes-vous sûr de vouloir abandonner l'écriture "${pieceNumber || id}" ?`)) return

  try {
    const response = await deleteItem(API_ROUTES.ABANDONED_JOURNAL_ENTRY(id))

    // Vérifier si la réponse indique un succès
    if (response && response.success) {
      showCustomToast({ message: 'Journal abandonné avec succès', type: 'success' })
      eventBus.emit('journalUpdated')
    } else {
      showCustomToast({ message: response.message, type: 'error' })
    }
  } catch (err) {
    console.error('Erreur désactivation journal : ', err)
    showCustomToast({ message: "Erreur lors de l'abandon du journal", type: 'error' })
  }
}

// Activer une écriture (si elle est abandonnée)
const activateEntry = async (id: number) => {
  if (!confirm('Êtes-vous sûr de vouloir réactiver ce journal ?')) return

  try {
    await api.put(API_ROUTES.ABANDONED_JOURNAL_ENTRY(id))

    showCustomToast({ message: 'journal réactivé avec succès', type: 'success' })
    eventBus.emit('journalUpdated')
  } catch (err) {
    console.error('Erreur activation journal : ', err)
    showCustomToast({ message: 'Erreur lors de la réactivation', type: 'error' })
  }
}

// Fonction pour sélectionner/désélectionner toutes les écritures
const handleSelectAllChange = (checked: boolean) => {
  if (checked) {
    selectedEntries.value = paginatedEntries.value.map((e) => e.id)
  } else {
    selectedEntries.value = []
  }
}

// Formater la date
const formatDate = (dateString?: string) => {
  if (!dateString) return '-'

  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

// Formater les montants
const formatAmount = (amount: number) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

// Exporter les journaux (PDF / Excel)
const { loading: exportLoading, exportMultiFormat } = useFileExport()

const exportJournals = async (format: 'pdf' | 'excel' = 'excel') => {
  await exportMultiFormat(
    API_ROUTES.EXPORT_JOURNALS_PDF,
    API_ROUTES.EXPORT_JOURNALS,
    format,
    'journals',
  )
}

// Charger les données au montage
onMounted(async () => {
  await fetchJournalEntries()
})

// Écouter les événements de mise à jour
eventBus.on('journalUpdated', () => {
  fetchJournalEntries()
})
</script>

<template>
  <ComptaLayout activeBread="journal" active-tag-name="journal" group="operations">
    <!-- Boutons d'accès rapide -->
    <BoxPanelWrapper>
      <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
        <div class="relative flex-1">
          <Input
            v-model="searchQuery"
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher un compte bancaire..."
            class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>
        <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
          <Button
            v-if="selectedEntries.length > 0"
            variant="destructive"
            size="md"
            @click="deactivateSelectedEntries"
            :disabled="deleting"
          >
            <span class="iconify hugeicons--delete-02 mr-1"></span>
            Supprimer ({{ selectedEntries.length }})
          </Button>
          <RouterLink to="/comptabilite/saisie-operations/nouveau-journal">
            <Button size="md">
              <span class="iconify hugeicons--add-01 mr-2"></span>
              Nouveau journal
            </Button>
          </RouterLink>
          <ExportDropdown :loading="exportLoading" :onExport="exportJournals" />
        </div>
      </div>

      <!-- État de chargement -->
      <div v-if="journalLoading" class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8">
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--loading-03 animate-spin text-4xl text-blue-500"></span>
          <p class="text-sm text-foreground-muted">Chargement des écritures de journal...</p>
        </div>
      </div>

      <!-- État d'erreur -->
      <div
        v-else-if="journalError"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
      >
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--alert-circle text-4xl text-red-500"></span>
          <p class="text-sm text-foreground-muted">{{ journalError }}</p>
          <Button @click="() => fetchJournalEntries()" size="sm" variant="outline">
            <span class="iconify hugeicons--refresh mr-1"></span>
            Réessayer
          </Button>
        </div>
      </div>

      <!-- Liste vide -->
      <div
        v-else-if="!paginatedEntries || paginatedEntries.length === 0"
        class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white p-8"
      >
        <div class="flex flex-col items-center justify-center w-full gap-3">
          <span class="iconify hugeicons--notebook text-4xl text-foreground-muted/50"></span>
          <p class="text-sm text-foreground-muted">
            {{
              searchQuery
                ? 'Aucune écriture trouvée pour cette recherche'
                : 'Aucune écriture de journal enregistrée'
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
                    selectedEntries.length === paginatedEntries.length &&
                    paginatedEntries.length > 0
                  "
                  @update:checked="handleSelectAllChange"
                />
              </TableHead>
              <TableHead>Date</TableHead>
              <TableHead>N° Pièce</TableHead>
              <TableHead>Code Compte</TableHead>
              <TableHead>Nom du Compte</TableHead>
              <TableHead>Libellé</TableHead>
              <TableHead class="text-right">Débit</TableHead>
              <TableHead class="text-right">Crédit</TableHead>
              <TableHead> Opérations </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="entry in paginatedEntries"
              :key="entry.id"
              :class="{ 'opacity-60 text-foreground-muted/70': entry.abandoned }"
            >
              <TableCell class="w-[40px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="selectedEntries.includes(entry.id)"
                  @update:checked="
                    (checked: boolean) => {
                      if (checked) {
                        selectedEntries.push(entry.id)
                      } else {
                        selectedEntries = selectedEntries.filter((id) => id !== entry.id)
                      }
                    }
                  "
                />
              </TableCell>
              <TableCell>{{ formatDate(entry.date) }}</TableCell>
              <TableCell>{{ entry.piece_number || '-' }}</TableCell>
              <TableCell>{{ entry.account.code || '-' }}</TableCell>
              <TableCell>{{ entry.account.name || '-' }}</TableCell>
              <TableCell>{{ entry.label || '-' }}</TableCell>
              <TableCell class="text-right font-medium">
                {{
                  parseInt(entry.output_account.amount) > 0
                    ? formatAmount(parseInt(entry.output_account.amount))
                    : '-'
                }}
              </TableCell>
              <TableCell class="text-right font-medium">
                {{
                  parseInt(entry.input_account.amount) > 0
                    ? formatAmount(parseInt(entry.input_account.amount))
                    : '-'
                }}
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <router-link :to="`/comptabilite/journal/${entry.id}`">
                    <Button variant="outline" size="icon" class="size-8">
                      <span class="iconify hugeicons--view"></span>
                    </Button>
                  </router-link>
                  <router-link
                    :to="{ path: `/comptabilite/journal/${entry.id}`, query: { edit: 'true' } }"
                  >
                    <Button variant="outline" size="icon" class="size-8">
                      <span class="iconify hugeicons--edit-02"></span>
                    </Button>
                  </router-link>
                  <Button
                    v-if="entry.abandoned"
                    variant="ghost"
                    size="icon"
                    class="size-8"
                    @click="activateEntry(entry.id)"
                    :disabled="deleting"
                    title="Réactiver"
                  >
                    <span class="iconify hugeicons--restore-bin"></span>
                  </Button>
                  <Button
                    v-else
                    variant="destructive"
                    size="icon"
                    class="size-8"
                    @click="deactivateEntry(entry.id, entry.label)"
                    :disabled="deleting"
                    title="Abandonner"
                  >
                    <span class="iconify hugeicons--delete-02"></span>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <TabPagination
        v-if="!journalLoading && !journalError && paginatedEntries.length > 0"
        v-model="page"
        :perPage="perPage"
        :totalItems="total"
        @update:perPage="(val) => (perPage = val)"
      />
    </BoxPanelWrapper>
  </ComptaLayout>
</template>
