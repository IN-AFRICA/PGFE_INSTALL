<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
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
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import AlertMessage from '@/components/modals/AlertMessage.vue'
import ExportDropdown from '@/components/ExportDropdown.vue'
import TabPagination from '@/components/blocks/TabPagination.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import { tagInfraNavPrealables } from '@/components/templates/infra/tags-links'
import SpanRequired from '@/components/atoms/SpanRequired.vue'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import type { BreadcrumbProps } from '@/types'

type InfraCategory = {
  id: number
  name: string
  description: string | null
  school_id: number
  author_id: string
  created_at: string
  updated_at: string
}

const query = ref('')
const exportLoading = ref(false)
const page = ref(1)
const perPageCount = ref(10)
const isModalOpen = ref(false)

// Form data
const formData = ref({
  name: '',
  description: '',
})

// API calls
const { data: apiData, loading, fetchData } = useGetApi<InfraCategory[]>(
  API_ROUTES.GET_INFRA_CATEGORIES
)
const { postData, loading: posting, success: postSuccess } = usePostApi()
const { deleteItem, deleting, success: deleteSuccess } = useDeleteApi()

onMounted(async () => {
  await fetchData()
})

const allItems = computed(() => apiData.value || [])

const filteredItems = computed(() => {
  const q = query.value.trim().toLowerCase()
  return allItems.value.filter((item) => {
    return (
      q.length === 0 ||
      item.name.toLowerCase().includes(q) ||
      (item.description && item.description.toLowerCase().includes(q))
    )
  })
})

const total = computed(() => filteredItems.value.length)
const data = computed(() => {
  const start = (page.value - 1) * perPageCount.value
  return filteredItems.value.slice(start, start + perPageCount.value)
})

const onPerPageUpdate = (value: number) => {
  perPageCount.value = value
  page.value = 1
}

const handleDelete = async (id: number) => {
  await deleteItem(API_ROUTES.DELETE_INFRA_CATEGORY(id))
  if (deleteSuccess.value) {
    showCustomToast({ message: 'Catégorie supprimée avec succès', type: 'success' })
    await fetchData()
  }
}

const handleExport = (format: string) => {
  exportLoading.value = true
  window.setTimeout(() => {
    exportLoading.value = false
    console.info(`Mock export: ${format}`)
  }, 600)
}

const handleSubmit = async () => {
  if (!formData.value.name) {
    showCustomToast({
      message: 'Le nom de la catégorie est requis',
      type: 'error',
    })
    return
  }

  const payload = {
    name: formData.value.name,
    description: formData.value.description || null,
  }

  await postData(API_ROUTES.CREATE_INFRA_CATEGORY, payload)

  if (postSuccess.value) {
    showCustomToast({
      message: 'Catégorie enregistrée avec succès',
      type: 'success',
    })
    isModalOpen.value = false
    formData.value = { name: '', description: '' }
    await fetchData()
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR')
}

const breadcrumb: BreadcrumbProps = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Infrastructure', href: '/infra' },
    { label: 'Catégories', isActive: true },
  ],
}
</script>

<template>
  <DashLayout module-name="infra" active-route="/infra/prealables" :breadcrumb="breadcrumb">
    <div class="pb-6 mx-auto w-full max-w-7xl">
      <DashPageHeader
        title="Infrastructure"
        :tags="tagInfraNavPrealables"
        active-tag-name="categories"
      />
      <BoxPanelWrapper>
        <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between">
          <div class="flex flex-1 items-center gap-2">
            <div class="relative w-full max-w-xs">
              <Input
                type="text"
                v-model="query"
                id="search"
                name="search"
                placeholder="Rechercher..."
                class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div
                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
              >
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>
          </div>
          <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
            <Dialog v-model:open="isModalOpen">
              <DialogTrigger as-child>
                <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max">
                  <span class="flex iconify hugeicons--plus-sign"></span>
                  <span class="hidden sm:flex">Nouvelle catégorie</span>
                </Button>
              </DialogTrigger>
              <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                  <DialogTitle>Ajouter une catégorie</DialogTitle>
                  <DialogDescription>
                    Créer une nouvelle catégorie d'infrastructure
                  </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleSubmit" class="space-y-4">
                  <div class="space-y-2">
                    <Label for="name">Nom de la catégorie<SpanRequired /></Label>
                    <Input
                      id="name"
                      v-model="formData.name"
                      type="text"
                      placeholder="Ex: Bâtiments"
                      required
                      class="border-gray-200"
                    />
                  </div>
                  <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                      id="description"
                      v-model="formData.description"
                      placeholder="Description de la catégorie..."
                      class="border-gray-200"
                      rows="3"
                    />
                  </div>
                  <DialogFooter>
                    <Button
                      type="button"
                      variant="outline"
                      @click="isModalOpen = false"
                      :disabled="posting"
                    >
                      Annuler
                    </Button>
                    <Button type="submit" :disabled="posting">
                      <IconifySpinner v-if="posting" class="mr-2" />
                      <span v-if="posting">Enregistrement...</span>
                      <span v-else>Enregistrer</span>
                    </Button>
                  </DialogFooter>
                </form>
              </DialogContent>
            </Dialog>
            <ExportDropdown :loading="exportLoading" @export="handleExport" />
          </div>
        </div>
        <div
          v-if="loading"
          class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500"
        >
          <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
          <span>Chargement des catégories...</span>
        </div>
        <div
          v-else-if="data && data.length"
          class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white"
        >
          <Table class="rounded-md bg-white">
            <TableHeader>
              <TableRow>
                <TableHead class="w-[20px]">
                  <Checkbox class="bg-white scale-70" />
                </TableHead>
                <TableHead class="w-[60px]">N°</TableHead>
                <TableHead>Nom</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Date création</TableHead>
                <TableHead>Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in data" :key="item.id" class="group">
                <TableCell class="w-[40px]">
                  <Checkbox class="bg-white scale-70" />
                </TableCell>
                <TableCell>{{ (page - 1) * perPageCount + (data.indexOf(item) + 1) }}</TableCell>
                <TableCell>{{ item.name }}</TableCell>
                <TableCell>{{ item.description || '-' }}</TableCell>
                <TableCell>{{ formatDate(item.created_at) }}</TableCell>
                <TableCell>
                  <div class="flex items-center gap-2">
                    <button
                      class="p-1.5 hover:bg-gray-100 rounded-md transition-colors opacity-0 group-hover:opacity-100"
                      title="Modifier"
                    >
                      <span class="iconify hugeicons--edit-02 text-base text-blue-600"></span>
                    </button>
                    <AlertMessage
                      :item-id="item.id"
                      :on-confirm="handleDelete"
                      :is-deleting="deleting"
                      title="Supprimer cette catégorie ?"
                      message="Cette action est irréversible. La catégorie sera définitivement supprimée."
                    >
                      <template #trigger>
                        <button
                          class="p-1.5 hover:bg-gray-100 rounded-md transition-colors opacity-0 group-hover:opacity-100"
                          title="Supprimer"
                        >
                          <span class="iconify hugeicons--delete-02 text-base text-red-600"></span>
                        </button>
                      </template>
                    </AlertMessage>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        <div
          v-else
          class="flex items-center justify-center py-10 bg-white rounded-md text-gray-500"
        >
          Aucune catégorie trouvée
        </div>
        <TabPagination
          v-if="data && data.length"
          v-model="page"
          :perPage="perPageCount"
          :totalItems="total"
          @update:perPage="onPerPageUpdate"
        />
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>
