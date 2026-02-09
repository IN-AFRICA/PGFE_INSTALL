<script setup lang="ts">
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import NewCountry from '@/components/modals/admin/location/NewCountry.vue'
import { onMounted, ref, computed, onBeforeUnmount } from 'vue'
import { useGetApi } from '@/composables/useGetApi.ts'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { eventBus } from '@/utils/eventBus.ts'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import TableRowActions from '@/components/molecules/TableRowActions.vue'

import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog'

interface Item {
  id: number
  name: string
}

import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Administration', href: '/admin' },
    { label: 'Pays', isActive: true },
  ],
}

const adminGeoTags = [
  { name: 'pays', text: 'Pays', href: '/admin/countries' },
  { name: 'provinces', text: 'Provinces', href: '/admin/provinces' },
  { name: 'territoires', text: 'Territoires', href: '/admin/territoires' },
  { name: 'communes', text: 'Communes', href: '/admin/communes' },
]

const activeTagName = computed(() => 'pays')

const query = ref('')
const { data, loading, error, fetchData } = useGetApi<Item[]>(API_ROUTES.GET_COUNTRIES)
const { deleteItem, deleting, errorDelete: delError } = useDeleteApi()
const { putData, loading: updating, error: updError, response: updResponse } = usePutApi()

const editOpen = ref(false)
const editId = ref<number | null>(null)
const editName = ref('')

function openEditModal(c: Item) {
  editId.value = c.id
  editName.value = c.name
  editOpen.value = true
}

const deletingId = ref<number | null>(null)

async function onDeleteCountry(id: number, name: string) {
  deletingId.value = id
  const res = await deleteItem(`${API_ROUTES.GET_COUNTRIES}/${id}`)
  if (res) {
    showCustomToast({
      message: `Pays "${name} supprimé avec succès"`,
      type: 'success',
    })
    eventBus.emit('countryUpdated')
  } else {
    showCustomToast({
      message: delError.value || 'Suppression impossible',
      type: 'error',
    })
  }
  deletingId.value = null
}

async function onSubmitEdit() {
  if (!editId.value) return
  const payload = { name: editName.value.trim() }
  await putData(`${API_ROUTES.GET_COUNTRIES}/${editId.value}`, payload)
  if (updError.value) {
    showCustomToast({ message: updError.value, type: 'error' })
    return
  }
  if (updResponse.value) {
    showCustomToast({ message: 'Pays mis à jour avec succès', type: 'success' })
    editOpen.value = false
    eventBus.emit('countryUpdated')
  }
}

onMounted(() => {
  fetchData()
  eventBus.on('countryUpdated', onRefresh)
})

onBeforeUnmount(() => {
  eventBus.off('countryUpdated', onRefresh)
})

function onRefresh() {
  fetchData()
}

const filtered = computed(() => {
  const list = data.value || []
  const q = query.value.trim().toLowerCase()
  if (!q) return list
  return list.filter((it: Item) => it.name.toLowerCase().includes(q))
})
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/admin" module-name="admin">
    <div class="pb-6 mx-auto w-full max-w-7xl">
      <DashPageHeader
        title="Administration"
        :tags="adminGeoTags"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper>
        <div class="flex items-center gap-3 justify-between">
          <div class="relative flex-1">
            <Input
              v-model="query"
              placeholder="Rechercher un pays..."
              class="w-full max-w-sm ps-10 h-10"
            />
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-foreground-muted/70">
              <span class="iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <NewCountry />
          </div>
        </div>

        <div class="mt-4 flex-1 min-h-0 overflow-auto bg-white rounded-md border border-gray-100">
          <div v-if="loading" class="p-4 text-sm text-gray-600">Chargement...</div>
          <div v-else-if="error" class="p-4 text-sm text-red-600">{{ error }}</div>
          <template v-else>
            <div v-if="(filtered || []).length === 0" class="p-4 text-sm text-gray-600">
              Aucun pays trouvé.
            </div>
            <div v-else class="w-full">
              <div
                class="px-4 py-3 bg-gray-200 border-b border-gray-100 flex items-center text-sm font-semibold text-foreground-title"
              >
                <div class="w-12">N°</div>
                <div class="flex-1">Nom du Pays</div>
                <div class="w-24 text-right">Actions</div>
              </div>
              <ul class="divide-y">
                <li
                  v-for="(c, index) in filtered"
                  :key="c.id"
                  class="px-4 py-3 flex items-center text-sm"
                >
                  <div class="w-12 text-gray-500">{{ index + 1 }}</div>
                  <div class="flex-1 flex items-center gap-3">
                    <span class="iconify hugeicons--flag-02 text-primary"></span>
                    <span class="font-medium">{{ c.name }}</span>
                  </div>
                  <div class="w-24 flex justify-end">
                    <TableRowActions>
                      <template #actions>
                        <Button size="sm" variant="outline" class="h-8" @click="openEditModal(c)">
                          <span class="iconify hugeicons--edit-01"></span>
                          <span class="sr-only">Modifier</span>
                        </Button>
                        <AlertDialog>
                          <AlertDialogTrigger as-child>
                            <Button size="sm" variant="destructive" class="h-8">
                              <span class="iconify hugeicons--delete-02"></span>
                              <span class="sr-only">Supprimer</span>
                            </Button>
                          </AlertDialogTrigger>
                          <AlertDialogContent>
                            <AlertDialogHeader>
                              <AlertDialogTitle>Supprimer ce pays ?</AlertDialogTitle>
                              <AlertDialogDescription>
                                Cette action est irréversible. Si des provinces dépendent de ce
                                pays, la suppression sera refusée.
                              </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                              <AlertDialogCancel>Annuler</AlertDialogCancel>
                              <Button
                                variant="destructive"
                                :disabled="deleting && deletingId === c.id"
                                @click="onDeleteCountry(c.id, c.name)"
                              >
                                <span
                                  v-if="deleting && deletingId === c.id"
                                  class="flex items-center gap-2"
                                >
                                  <IconifySpinner size="sm" />
                                  <span>Suppression...</span>
                                </span>
                                <span v-else>Confirmer</span>
                              </Button>
                            </AlertDialogFooter>
                          </AlertDialogContent>
                        </AlertDialog>
                      </template>
                    </TableRowActions>
                  </div>
                </li>

                <Dialog v-model:open="editOpen">
                  <DialogContent class="sm:max-w-[420px]">
                    <DialogHeader>
                      <DialogTitle>Modifier un Pays</DialogTitle>
                      <DialogDescription>Mettre à jour le nom du pays</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="onSubmitEdit">
                      <div class="grid gap-4 py-4">
                        <div class="flex flex-col space-y-1.5">
                          <Label for="name" class="text-sm font-medium">Nom</Label>
                          <Input
                            id="name"
                            v-model="editName"
                            class="h-10"
                            :disabled="updating"
                            required
                          />
                        </div>
                      </div>
                      <DialogFooter class="flex justify-end gap-2 items-center">
                        <Button
                          size="sm"
                          class="h-9"
                          variant="outline"
                          type="button"
                          :disabled="updating"
                          @click="editOpen = false"
                          >Annuler</Button
                        >
                        <Button size="sm" class="h-9" type="submit" :disabled="updating">
                          <span v-if="!updating" class="flex items-center gap-2">
                            <span class="iconify hugeicons--floppy-disk"></span>
                            <span>Enregistrer</span>
                          </span>
                          <span v-else class="flex items-center gap-2">
                            <IconifySpinner size="lg" />
                            <span>Mise à jour...</span>
                          </span>
                        </Button>
                      </DialogFooter>
                    </form>
                  </DialogContent>
                </Dialog>
              </ul>
            </div>
          </template>
        </div>
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>

<style scoped></style>
