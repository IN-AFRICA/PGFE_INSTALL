<script setup lang="ts">
import { ref } from 'vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import { insertionNavPrealables } from '@/components/templates/insertion/insertions_tags_links'
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
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { usePostApi } from '@/composables/usePostApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Insertion', href: '/insertion' },
    { label: 'Candidats', isActive: true },
  ],
}

const activeTagName = 'candidats'
const query = ref('')

const dialogOpen = ref(false)
const editingItem = ref<any>(null)
const form = ref({ name: '', email: '', phone: '' })

// Local store
const candidats = ref<any[]>([])

const { postData, loading, error, success } = usePostApi()

const filteredCandidats = () => {
  if (!query.value) return candidats.value
  const s = query.value.toLowerCase()
  return candidats.value.filter(
    (c: any) => c.name.toLowerCase().includes(s) || c.email.toLowerCase().includes(s)
  )
}

const resetForm = () => {
  form.value = { name: '', email: '', phone: '' }
  editingItem.value = null
}

const openAddDialog = () => {
  resetForm()
  dialogOpen.value = true
}

const openEditDialog = (item: any) => {
  editingItem.value = item
  form.value = { name: item.name, email: item.email, phone: item.phone || '' }
  dialogOpen.value = true
}

const handleSubmit = async () => {
  if (!form.value.name || !form.value.email) {
    showCustomToast({ message: 'Le nom et l\'email sont obligatoires.', type: 'error' })
    return
  }

  if (editingItem.value) {
    const idx = candidats.value.findIndex((c) => c.id === editingItem.value.id)
    if (idx !== -1) {
      candidats.value[idx] = { ...candidats.value[idx], ...form.value }
    }
    showCustomToast({ message: 'Candidat modifié localement.', type: 'success' })
    dialogOpen.value = false
    resetForm()
    return
  }

  const payload: any = { name: form.value.name, email: form.value.email }
  if (form.value.phone) payload.phone = form.value.phone

  await postData(API_ROUTES.REGISTER_CANDIDATE, payload)

  if (success.value) {
    const newItem = {
      id: Date.now(),
      name: form.value.name,
      email: form.value.email,
      phone: form.value.phone || '—',
    }
    candidats.value.push(newItem)
    showCustomToast({ message: 'Candidat inscrit avec succès.', type: 'success' })
    dialogOpen.value = false
    resetForm()
  } else if (error.value) {
    showCustomToast({ message: error.value, type: 'error' })
  }
}

const handleDelete = (id: number) => {
  candidats.value = candidats.value.filter((c) => c.id !== id)
  showCustomToast({ message: 'Candidat supprimé.', type: 'success' })
}
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/insertion/prealables" module-name="insertion">
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Registre des Candidats"
        :tags="insertionNavPrealables"
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
                placeholder="Recherche par nom ou email..."
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
                <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" @click="openAddDialog">
                  <span class="flex iconify hugeicons--plus-sign"></span>
                  <span class="hidden sm:flex">Inscrire Candidat</span>
                </Button>
              </DialogTrigger>
              <DialogContent class="sm:max-w-[460px]">
                <DialogHeader>
                  <DialogTitle>{{ editingItem ? 'Modifier le candidat' : 'Nouveau Candidat' }}</DialogTitle>
                  <DialogDescription>
                    {{ editingItem ? 'Modifiez les informations du candidat.' : 'Inscrivez un nouveau candidat à l\'insertion professionnelle.' }}
                  </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                  <div class="grid gap-2">
                    <Label for="cand-name">Nom complet <span class="text-red-500">*</span></Label>
                    <Input id="cand-name" v-model="form.name" placeholder="Ex: Jean Musumba" />
                  </div>
                  <div class="grid gap-2">
                    <Label for="cand-email">Email <span class="text-red-500">*</span></Label>
                    <Input id="cand-email" v-model="form.email" type="email" placeholder="Ex: jean@example.com" />
                  </div>
                  <div class="grid gap-2">
                    <Label for="cand-phone">Téléphone <span class="text-gray-400 text-xs">(optionnel)</span></Label>
                    <Input id="cand-phone" v-model="form.phone" placeholder="Ex: +243 82 000 0000" />
                  </div>
                </div>
                <DialogFooter>
                  <DialogClose as-child>
                    <Button variant="outline" @click="resetForm">Annuler</Button>
                  </DialogClose>
                  <Button :disabled="loading" @click="handleSubmit">
                    <IconifySpinner v-if="loading" />
                    <span v-else>{{ editingItem ? 'Enregistrer' : 'Inscrire' }}</span>
                  </Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>
          </div>
        </div>

        <!-- Table -->
        <div v-if="filteredCandidats().length" class="mt-2 rounded-md overflow-hidden flex flex-1 bg-white">
          <Table class="rounded-md bg-white">
            <TableHeader>
              <TableRow>
                <TableHead>N°</TableHead>
                <TableHead>Nom complet</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Téléphone</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in filteredCandidats()" :key="item.id">
                <TableCell>{{ index + 1 }}</TableCell>
                <TableCell class="font-medium">{{ item.name }}</TableCell>
                <TableCell>{{ item.email }}</TableCell>
                <TableCell>{{ item.phone || '—' }}</TableCell>
                <TableCell>
                  <div class="flex items-center justify-end gap-2">
                    <Button size="icon" variant="ghost" class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50" @click="openEditDialog(item)">
                      <span class="iconify hugeicons--edit-01"></span>
                    </Button>
                    <Dialog>
                      <DialogTrigger as-child>
                        <Button size="icon" variant="ghost" class="size-8 text-gray-500 hover:text-red-600 hover:bg-red-50">
                          <span class="iconify hugeicons--delete-02"></span>
                        </Button>
                      </DialogTrigger>
                      <DialogContent>
                        <DialogHeader>
                          <DialogTitle>Supprimer ce candidat ?</DialogTitle>
                          <DialogDescription>Action irréversible. Voulez-vous vraiment supprimer "{{ item.name }}" ?</DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                          <DialogClose as-child><Button variant="outline">Annuler</Button></DialogClose>
                          <Button variant="destructive" @click="handleDelete(item.id)">Confirmer</Button>
                        </DialogFooter>
                      </DialogContent>
                    </Dialog>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center h-full py-16 bg-white rounded-md text-gray-500">
          <span class="iconify hugeicons--user-group text-5xl mb-3 text-gray-300"></span>
          <p class="font-medium">Aucun candidat inscrit</p>
          <p class="text-sm text-gray-400 mt-1">Inscrivez votre premier candidat à l'insertion professionnelle.</p>
        </div>
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>
