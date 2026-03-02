<script setup lang="ts">
import { ref } from 'vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import { insertionNavOperations } from '@/components/templates/insertion/insertions_tags_links'
import {
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle,
  DialogDescription, DialogFooter, DialogClose,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Insertion', href: '/insertion' },
    { label: 'Candidatures', isActive: true },
  ],
}

const activeTagName = 'candidatures'
const query = ref('')

const dialogOpen = ref(false)
const actionDialogOpen = ref(false)
const pendingAction = ref<{ type: 'accept' | 'reject'; item: any } | null>(null)

const form = ref({ candidate_id: '', job_offer_id: '' })

// Local store
const candidatures = ref<any[]>([])

const { postData, loading, error, success } = usePostApi()
const { postData: doAction, loading: actionLoading } = usePostApi()

const filteredCandidatures = () => {
  if (!query.value) return candidatures.value
  const s = query.value.toLowerCase()
  return candidatures.value.filter(
    (c: any) =>
      c.candidate_id?.toString().includes(s) ||
      c.job_offer_id?.toString().includes(s)
  )
}

const resetForm = () => {
  form.value = { candidate_id: '', job_offer_id: '' }
}

const statusLabel = (status: string) => {
  if (status === 'accepted') return 'Acceptée'
  if (status === 'rejected') return 'Refusée'
  return 'En attente'
}

const statusClass = (status: string) => {
  if (status === 'accepted') return 'bg-green-100 text-green-700 border border-green-200'
  if (status === 'rejected') return 'bg-red-100 text-red-600 border border-red-200'
  return 'bg-yellow-100 text-yellow-700 border border-yellow-200'
}

const handleSubmit = async () => {
  if (!form.value.candidate_id || !form.value.job_offer_id) {
    showCustomToast({ message: 'Veuillez renseigner le candidat et l\'offre d\'emploi.', type: 'error' })
    return
  }

  await postData(API_ROUTES.POST_INSERTION_APPLICATIONS, {
    candidate_id: Number(form.value.candidate_id),
    job_offer_id: Number(form.value.job_offer_id),
  })

  if (success.value) {
    candidatures.value.push({
      id: Date.now(),
      candidate_id: form.value.candidate_id,
      job_offer_id: form.value.job_offer_id,
      status: 'pending',
      created_at: new Date().toLocaleDateString('fr-FR'),
    })
    showCustomToast({ message: 'Candidature soumise avec succès.', type: 'success' })
    dialogOpen.value = false
    resetForm()
  } else if (error.value) {
    showCustomToast({ message: error.value, type: 'error' })
  }
}

const openActionDialog = (type: 'accept' | 'reject', item: any) => {
  pendingAction.value = { type, item }
  actionDialogOpen.value = true
}

const handleAction = async () => {
  if (!pendingAction.value) return
  const { type, item } = pendingAction.value

  const url = type === 'accept'
    ? API_ROUTES.ACCEPT_INSERTION_APPLICATION(item.id)
    : API_ROUTES.REJECT_INSERTION_APPLICATION(item.id)

  await doAction(url, {})

  // Mise à jour locale
  const idx = candidatures.value.findIndex((c) => c.id === item.id)
  if (idx !== -1) {
    candidatures.value[idx].status = type === 'accept' ? 'accepted' : 'rejected'
  }

  const msg = type === 'accept' ? 'Candidature acceptée.' : 'Candidature refusée.'
  showCustomToast({ message: msg, type: type === 'accept' ? 'success' : 'error' })
  actionDialogOpen.value = false
  pendingAction.value = null
}
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/insertion/operations" module-name="insertion">
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Gestion des Candidatures"
        :tags="insertionNavOperations"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper class="flex-1 flex flex-col min-h-0">
        <!-- Toolbar -->
        <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between mb-4">
          <div class="flex flex-1 items-center gap-2">
            <div class="relative w-full max-w-xs">
              <Input
                v-model="query"
                type="text"
                placeholder="Recherche par candidat ou offre..."
                class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>
          </div>
          <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
            <Dialog v-model:open="dialogOpen">
              <DialogTrigger as-child>
                <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" @click="resetForm">
                  <span class="flex iconify hugeicons--plus-sign"></span>
                  <span class="hidden sm:flex">Nouvelle Candidature</span>
                </Button>
              </DialogTrigger>
              <DialogContent class="sm:max-w-[460px]">
                <DialogHeader>
                  <DialogTitle>Soumettre une Candidature</DialogTitle>
                  <DialogDescription>Associez un candidat à une offre d'emploi.</DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                  <div class="grid gap-2">
                    <Label for="cand-id">ID Candidat <span class="text-red-500">*</span></Label>
                    <Input id="cand-id" v-model="form.candidate_id" type="number" placeholder="Ex: 1" />
                  </div>
                  <div class="grid gap-2">
                    <Label for="offer-id">ID Offre d'emploi <span class="text-red-500">*</span></Label>
                    <Input id="offer-id" v-model="form.job_offer_id" type="number" placeholder="Ex: 3" />
                  </div>
                </div>
                <DialogFooter>
                  <DialogClose as-child>
                    <Button variant="outline" @click="resetForm">Annuler</Button>
                  </DialogClose>
                  <Button :disabled="loading" @click="handleSubmit">
                    <IconifySpinner v-if="loading" />
                    <span v-else>Soumettre</span>
                  </Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>
          </div>
        </div>

        <!-- Table -->
        <div v-if="filteredCandidatures().length" class="mt-2 rounded-md overflow-hidden flex flex-1 bg-white">
          <Table class="rounded-md bg-white">
            <TableHeader>
              <TableRow>
                <TableHead>N°</TableHead>
                <TableHead>Candidat (ID)</TableHead>
                <TableHead>Offre (ID)</TableHead>
                <TableHead>Date</TableHead>
                <TableHead>Statut</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in filteredCandidatures()" :key="item.id">
                <TableCell>{{ index + 1 }}</TableCell>
                <TableCell class="font-medium">{{ item.candidate_id }}</TableCell>
                <TableCell>{{ item.job_offer_id }}</TableCell>
                <TableCell class="text-gray-500">{{ item.created_at }}</TableCell>
                <TableCell>
                  <Badge :class="statusClass(item.status)">
                    {{ statusLabel(item.status) }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div v-if="item.status === 'pending'" class="flex items-center justify-end gap-2">
                    <!-- Accepter -->
                    <Button
                      size="sm"
                      variant="outline"
                      class="h-8 text-green-700 border-green-200 hover:bg-green-50 hover:text-green-800"
                      @click="openActionDialog('accept', item)"
                    >
                      <span class="iconify hugeicons--tick-02 mr-1"></span>
                      Accepter
                    </Button>
                    <!-- Refuser -->
                    <Button
                      size="sm"
                      variant="outline"
                      class="h-8 text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700"
                      @click="openActionDialog('reject', item)"
                    >
                      <span class="iconify hugeicons--cancel-01 mr-1"></span>
                      Refuser
                    </Button>
                  </div>
                  <span v-else class="text-xs text-gray-400 flex justify-end">—</span>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center h-full py-16 bg-white rounded-md text-gray-500">
          <span class="iconify hugeicons--task-01 text-5xl mb-3 text-gray-300"></span>
          <p class="font-medium">Aucune candidature</p>
          <p class="text-sm text-gray-400 mt-1">Soumettez une candidature en associant un candidat à une offre.</p>
        </div>
      </BoxPanelWrapper>
    </div>

    <!-- Dialog de confirmation : Accepter / Refuser -->
    <Dialog v-model:open="actionDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>
            {{ pendingAction?.type === 'accept' ? 'Accepter cette candidature ?' : 'Refuser cette candidature ?' }}
          </DialogTitle>
          <DialogDescription>
            <span v-if="pendingAction?.type === 'accept'">
              Le candidat #{{ pendingAction?.item?.candidate_id }} sera accepté pour l'offre #{{ pendingAction?.item?.job_offer_id }}.
            </span>
            <span v-else>
              Le candidat #{{ pendingAction?.item?.candidate_id }} sera refusé pour l'offre #{{ pendingAction?.item?.job_offer_id }}.
            </span>
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <DialogClose as-child><Button variant="outline">Annuler</Button></DialogClose>
          <Button
            :variant="pendingAction?.type === 'accept' ? 'default' : 'destructive'"
            :disabled="actionLoading"
            @click="handleAction"
          >
            <IconifySpinner v-if="actionLoading" />
            <span v-else>{{ pendingAction?.type === 'accept' ? 'Confirmer l\'acceptation' : 'Confirmer le refus' }}</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </DashLayout>
</template>
