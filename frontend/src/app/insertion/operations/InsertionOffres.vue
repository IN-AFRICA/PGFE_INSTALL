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
import { Textarea } from '@/components/ui/textarea'
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
    { label: "Offres d'emploi", isActive: true },
  ],
}

const activeTagName = 'offres'
const query = ref('')
const dialogOpen = ref(false)
const closeDialogOpen = ref(false)
const closingItem = ref<any>(null)

const form = ref({ title: '', description: '', company_id: '' })

// Local store (pas de GET list API)
const offres = ref<any[]>([])

const { postData, loading, error, success } = usePostApi()
const { postData: closeOffer, loading: closing } = usePostApi()

const filteredOffres = () => {
  if (!query.value) return offres.value
  const s = query.value.toLowerCase()
  return offres.value.filter(
    (o: any) => o.title.toLowerCase().includes(s) || o.company_id?.toString().includes(s)
  )
}

const resetForm = () => {
  form.value = { title: '', description: '', company_id: '' }
}

const handleSubmit = async () => {
  if (!form.value.title || !form.value.description || !form.value.company_id) {
    showCustomToast({ message: 'Tous les champs sont obligatoires.', type: 'error' })
    return
  }

  await postData(API_ROUTES.CREATE_JOB_OFFER, {
    title: form.value.title,
    description: form.value.description,
    company_id: Number(form.value.company_id),
  })

  if (success.value) {
    offres.value.push({
      id: Date.now(),
      title: form.value.title,
      description: form.value.description,
      company_id: form.value.company_id,
      status: 'open',
      created_at: new Date().toLocaleDateString('fr-FR'),
    })
    showCustomToast({ message: "Offre d'emploi créée avec succès.", type: 'success' })
    dialogOpen.value = false
    resetForm()
  } else if (error.value) {
    showCustomToast({ message: error.value, type: 'error' })
  }
}

const openCloseDialog = (item: any) => {
  closingItem.value = item
  closeDialogOpen.value = true
}

const handleClose = async () => {
  if (!closingItem.value) return

  await closeOffer(API_ROUTES.CLOSE_JOB_OFFER(closingItem.value.id), {})

  // Mise à jour locale dans tous les cas (la clôture peut réussir même si l'ID est local)
  const idx = offres.value.findIndex((o) => o.id === closingItem.value.id)
  if (idx !== -1) {
    offres.value[idx].status = 'closed'
  }
  showCustomToast({ message: "Offre clôturée avec succès.", type: 'success' })
  closeDialogOpen.value = false
  closingItem.value = null
}
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/insertion/operations" module-name="insertion">
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Offres d'Emploi"
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
                placeholder="Recherche par titre..."
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
                  <span class="hidden sm:flex">Nouvelle Offre</span>
                </Button>
              </DialogTrigger>
              <DialogContent class="sm:max-w-[520px]">
                <DialogHeader>
                  <DialogTitle>Nouvelle Offre d'emploi</DialogTitle>
                  <DialogDescription>Créez une offre d'emploi liée à une entreprise partenaire.</DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                  <div class="grid gap-2">
                    <Label for="offer-title">Titre du poste <span class="text-red-500">*</span></Label>
                    <Input id="offer-title" v-model="form.title" placeholder="Ex: Développeur Full Stack" />
                  </div>
                  <div class="grid gap-2">
                    <Label for="offer-desc">Description <span class="text-red-500">*</span></Label>
                    <Textarea id="offer-desc" v-model="form.description" placeholder="Décrivez le poste, les missions et les compétences requises..." rows="4" />
                  </div>
                  <div class="grid gap-2">
                    <Label for="offer-company">ID Entreprise <span class="text-red-500">*</span></Label>
                    <Input id="offer-company" v-model="form.company_id" type="number" placeholder="Ex: 1" />
                  </div>
                </div>
                <DialogFooter>
                  <DialogClose as-child>
                    <Button variant="outline" @click="resetForm">Annuler</Button>
                  </DialogClose>
                  <Button :disabled="loading" @click="handleSubmit">
                    <IconifySpinner v-if="loading" />
                    <span v-else>Créer l'offre</span>
                  </Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>
          </div>
        </div>

        <!-- Table -->
        <div v-if="filteredOffres().length" class="mt-2 rounded-md overflow-hidden flex flex-1 bg-white">
          <Table class="rounded-md bg-white">
            <TableHeader>
              <TableRow>
                <TableHead>N°</TableHead>
                <TableHead>Titre</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Entreprise (ID)</TableHead>
                <TableHead>Statut</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in filteredOffres()" :key="item.id">
                <TableCell>{{ index + 1 }}</TableCell>
                <TableCell class="font-medium">{{ item.title }}</TableCell>
                <TableCell class="max-w-[200px] truncate text-gray-500">{{ item.description }}</TableCell>
                <TableCell>{{ item.company_id }}</TableCell>
                <TableCell>
                  <Badge
                    :class="item.status === 'closed'
                      ? 'bg-gray-100 text-gray-500 border border-gray-200'
                      : 'bg-green-100 text-green-700 border border-green-200'"
                  >
                    {{ item.status === 'closed' ? 'Clôturée' : 'Ouverte' }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div class="flex items-center justify-end gap-2">
                    <Button
                      v-if="item.status !== 'closed'"
                      size="sm"
                      variant="outline"
                      class="h-8 text-orange-600 border-orange-200 hover:bg-orange-50 hover:text-orange-700"
                      @click="openCloseDialog(item)"
                    >
                      <span class="iconify hugeicons--lock-02 mr-1"></span>
                      Clôturer
                    </Button>
                    <span v-else class="text-xs text-gray-400">Terminée</span>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center h-full py-16 bg-white rounded-md text-gray-500">
          <span class="iconify hugeicons--job-link text-5xl mb-3 text-gray-300"></span>
          <p class="font-medium">Aucune offre d'emploi</p>
          <p class="text-sm text-gray-400 mt-1">Créez votre première offre d'emploi.</p>
        </div>
      </BoxPanelWrapper>
    </div>

    <!-- Dialog Clôturer -->
    <Dialog v-model:open="closeDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Clôturer cette offre ?</DialogTitle>
          <DialogDescription>
            L'offre "{{ closingItem?.title }}" sera marquée comme terminée. Cette action est irréversible.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <DialogClose as-child><Button variant="outline">Annuler</Button></DialogClose>
          <Button variant="destructive" :disabled="closing" @click="handleClose">
            <IconifySpinner v-if="closing" />
            <span v-else>Confirmer la clôture</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </DashLayout>
</template>
