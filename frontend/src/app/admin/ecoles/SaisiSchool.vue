<script setup lang="ts">
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
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
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
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
  name: string
  province_id?: number
  country_id?: string
  type_id?: string
  city?: string
  address?: string
  latitude?: string | number
  longitude?: string | number
  phone_number?: string
  email?: string
  logo?: string
}

interface Province {
  id: number
  name: string
  country_id: string
}

interface Type {
  id: number
  title: string
}

const router = useRouter()

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Administration', href: '/admin' },
    { label: 'Écoles', isActive: true },
  ],
}

const adminEcoleTags = [{ name: 'ecoles', text: 'Écoles', href: '/admin/ecoles' }]

const activeTagName = computed(() => 'ecoles')

const query = ref('')

const { data, loading, error, fetchData } = useGetApi<Item[]>(API_ROUTES.GET_SCHOOLS)
const { data: provinces, fetchData: fetchProvinces } = useGetApi<Province[]>(
  API_ROUTES.GET_PROVINCES,
)
const { data: types, fetchData: fetchTypes } = useGetApi<Type[]>(API_ROUTES.GET_TYPES)
const { deleteItem, deleting, errorDelete: delError } = useDeleteApi()

function goToEditSchool(id: number) {
  router.push(`/admin/ecoles/edit/${id}`)
}

const deletingId = ref<number | null>(null)

async function onDeleteSchool(id: number, name: string) {
  deletingId.value = id
  const res = await deleteItem(`/schools/${id}`)
  if (res) {
    showCustomToast({ message: `École "${name}" supprimée avec succès`, type: 'success' })
    eventBus.emit('schoolUpdated')
  } else {
    showCustomToast({ message: delError.value || 'Suppression impossible', type: 'error' })
  }
  deletingId.value = null
}

function goToNewSchool() {
  router.push('/admin/ecoles/nouveau')
}

const filtered = computed(() => {
  const list = data.value || []
  const q = query.value.trim().toLowerCase()
  if (!q) return list
  return list.filter((it) => it.name?.toLowerCase().includes(q))
})

onMounted(() => {
  fetchData()
  fetchProvinces()
  fetchTypes()
  eventBus.on('schoolUpdated', onRefresh)
})

onBeforeUnmount(() => {
  eventBus.off('schoolUpdated', onRefresh)
})

function onRefresh() {
  fetchData()
}
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/admin/ecoles" module-name="admin">
    <div class="pb-6 mx-auto w-full max-w-7xl">
      <DashPageHeader
        title="Administration"
        :tags="adminEcoleTags"
        :active-tag-name="activeTagName"
      />
      <BoxPanelWrapper>
        <div class="flex items-center gap-3 justify-between">
          <div class="relative flex-1">
            <Input
              v-model="query"
              placeholder="Rechercher une école..."
              class="w-full max-w-sm ps-10 h-10"
            />
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-foreground-muted/70">
              <span class="iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button @click="goToNewSchool">
              <span class="iconify hugeicons--add-01"></span>
              <span>Nouvelle école</span>
            </Button>
          </div>
        </div>

        <!-- List -->
        <div class="mt-4 flex-1 min-h-0 overflow-auto bg-white rounded-md border border-gray-100">
          <div v-if="loading" class="p-4 text-sm text-gray-600">Chargement...</div>
          <div v-else-if="error" class="p-4 text-sm text-red-600">{{ error }}</div>
          <template v-else>
            <div v-if="(filtered || []).length === 0" class="p-4 text-sm text-gray-600">
              Aucune école trouvée.
            </div>
            <ul v-else class="divide-y">
              <li
                v-for="c in filtered"
                :key="c.id"
                class="px-4 py-3 flex items-center justify-between"
              >
                <div class="flex items-center gap-3">
                  <span class="iconify hugeicons--school text-primary"></span>
                  <span class="font-medium">{{ c.name }}</span>
                </div>
                <TableRowActions>
                  <template #actions>
                    <Button size="sm" variant="outline" class="h-8" @click="goToEditSchool(c.id)">
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
                          <AlertDialogTitle>Supprimer cette école ?</AlertDialogTitle>
                          <AlertDialogDescription>
                            Cette action est irréversible. Si des données dépendent de cette école,
                            la suppression sera refusée.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Annuler</AlertDialogCancel>
                          <Button
                            variant="destructive"
                            :disabled="deleting && deletingId === c.id"
                            @click="onDeleteSchool(c.id, c.name)"
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
            </ul>
          </template>
        </div>
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>

<style scoped></style>
