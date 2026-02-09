<script setup lang="ts">
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { onMounted, ref, computed, onBeforeUnmount } from 'vue'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { usePutApi } from '@/composables/usePutApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { eventBus } from '@/utils/eventBus'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import TableRowActions from '@/components/molecules/TableRowActions.vue'
import NewType from '@/components/modals/admin/type/NewType.vue'

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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

interface Item {
  id: number
  title: string
}

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Administration', href: '/admin' },
    { label: 'Types', isActive: true },
  ],
}

const adminGeoTags = [{ name: 'type', text: 'Type', href: '/admin/type' }]
const activeTagName = computed(() => 'type')

const query = ref('')
const { data, loading, error, fetchData } = useGetApi<Item[]>(API_ROUTES.GET_TYPES)
const { deleteItem, deleting, errorDelete: delError } = useDeleteApi()
const { putData, loading: updating, error: updError } = usePutApi()

const editOpen = ref(false)
const editId = ref<number | null>(null)
const editTitle = ref('')

function openEditModal(c: Item) {
  editId.value = c.id
  editTitle.value = c.title
  editOpen.value = true
}

const deletingId = ref<number | null>(null)

async function onDeleteType(id: number, title: string) {
  deletingId.value = id
  const res = await deleteItem(`${API_ROUTES.GET_TYPES}/${id}`)
  if (res) {
    showCustomToast({ message: `Type "${title}" supprimé avec succès`, type: 'success' })
    eventBus.emit('typeUpdated')
  } else {
    showCustomToast({ message: delError.value || 'Suppression impossible', type: 'error' })
  }
  deletingId.value = null
}

async function onSubmitEdit() {
  if (!editId.value) return
  const payload = { title: editTitle.value.trim() }
  await putData(`${API_ROUTES.GET_TYPES}/${editId.value}`, payload)
  if (updError.value) {
    showCustomToast({ message: updError.value, type: 'error' })
    return
  }
  if (updResponse.value) {
    showCustomToast({ message: 'Type mis à jour avec succès', type: 'success' })
    editOpen.value = false
    eventBus.emit('typeUpdated')
  }
}

function onRefresh() {
  fetchData()
}

onMounted(() => {
  fetchData()
  eventBus.on('typeUpdated', onRefresh)
})

onBeforeUnmount(() => {
  eventBus.off('typeUpdated', onRefresh)
})

const filtered = computed(() => {
  const list = data.value || []
  const q = query.value.trim().toLowerCase()
  if (!q) return list
  return list.filter((it) => it.title?.toLowerCase().includes(q))
})
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/admin/type" module-name="admin">
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
              placeholder="Rechercher un type..."
              class="w-full max-w-sm ps-10 h-10"
            />
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-foreground-muted/70">
              <span class="iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <NewType />
          </div>
        </div>

        <div class="mt-4 flex-1 min-h-0 overflow-auto bg-white rounded-md border border-gray-100">
          <div v-if="loading" class="p-4 text-sm text-gray-600">Chargement...</div>
          <div v-else-if="error" class="p-4 text-sm text-red-600">{{ error }}</div>
          <template v-else>
            <div v-if="(filtered || []).length === 0" class="p-4 text-sm text-gray-600">
              Aucun type trouvé.
            </div>
            <ul v-else class="divide-y">
              <li
                v-for="c in filtered"
                :key="c.id"
                class="px-4 py-3 flex items-center justify-between"
              >
                <div class="flex items-center gap-3">
                  <span class="font-medium">{{ c.title }}</span>
                </div>
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
                          <AlertDialogTitle>Supprimer ce type ?</AlertDialogTitle>
                          <AlertDialogDescription>
                            Cette action est irréversible. Si des données dépendent de ce type, la
                            suppression sera refusée.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Annuler</AlertDialogCancel>
                          <Button
                            variant="destructive"
                            :disabled="deleting && deletingId === c.id"
                            @click="onDeleteType(c.id, c.title)"
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
              </li>

              <Dialog v-model:open="editOpen">
                <DialogContent class="sm:max-w-[420px]">
                  <DialogHeader>
                    <DialogTitle>Modifier un type</DialogTitle>
                    <DialogDescription>Mettre à jour le titre du type</DialogDescription>
                  </DialogHeader>
                  <form @submit.prevent="onSubmitEdit">
                    <div class="grid gap-4 py-4">
                      <div class="flex flex-col space-y-1.5">
                        <Label for="title" class="text-sm font-medium">Titre</Label>
                        <Input
                          id="title"
                          v-model="editTitle"
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
                      >
                        Annuler
                      </Button>
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
          </template>
        </div>
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>

<style scoped></style>
