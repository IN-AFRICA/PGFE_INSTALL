<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import { tagInfraNavOperations } from '@/components/templates/infra/tags-links'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import ExportDropdown from '@/components/ExportDropdown.vue'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useGetApi } from '@/composables/useGetApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useAuthStore } from '@/stores/auth'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogClose,
} from '@/components/ui/dialog'

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Infrastructure', href: '/infra' },
    { label: 'Opérations', href: '/infra/operations' },
    { label: 'Infrastructures', isActive: true },
  ],
}

const activeTagName = 'registre infrastructures'
const router = useRouter()
const authStore = useAuthStore()

// API Logic
const { data: rawData, loading, fetchData } = useGetApi(API_ROUTES.GET_INFRA_INFRASTRUCTURES)
const { deleteItem, deleting, success: deleteSuccess } = useDeleteApi()

// Load reference data for mapping
const categories = ref<Array<{ id: number; name: string }>>([])
const bailleurs = ref<Array<{ id: number; name: string }>>([])

const loadReferenceData = async () => {
  try {
    const [catResponse, bailResponse] = await Promise.all([
      fetch('https://pgfe-back.inafrica.tech/api/v1/infrastructures/categories', {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
          Accept: 'application/json',
        },
      }),
      fetch('https://pgfe-back.inafrica.tech/api/v1/infrastructures/bailleurs', {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
          Accept: 'application/json',
        },
      }),
    ])

    if (catResponse.ok) {
      const result = await catResponse.json()
      categories.value = result.data || []
    }

    if (bailResponse.ok) {
      const result = await bailResponse.json()
      bailleurs.value = result.data || []
    }
  } catch (error) {
    console.error('Erreur chargement références:', error)
  }
}

const getCategoryName = (id: string | number) => {
  const cat = categories.value.find(c => c.id === Number(id))
  return cat?.name || '-'
}

const getBailleurName = (id: string | number) => {
  const bail = bailleurs.value.find(b => b.id === Number(id))
  return bail?.name || '-'
}

const data = computed(() => {
  if (!rawData.value) return []
  if (Array.isArray(rawData.value)) return rawData.value
  return (rawData.value as any).data || []
})

const query = ref('')
const filteredData = computed(() => {
  if (!query.value) return data.value
  const lowerQuery = query.value.toLowerCase()
  return data.value.filter((i: any) =>
    i.name?.toLowerCase().includes(lowerQuery) ||
    i.emplacement?.toLowerCase().includes(lowerQuery) ||
    i.categorie?.name?.toLowerCase().includes(lowerQuery)
  )
})

const navigateToAdd = () => {
  router.push('/infra/operations/infrastructures/nouveau')
}

const navigateToEdit = (id: number) => {
  router.push(`/infra/operations/infrastructures/${id}/modifier`)
}

const handleDelete = async (id: number) => {
  await deleteItem(API_ROUTES.DELETE_INFRA_INFRASTRUCTURE(id))
  if (deleteSuccess.value) {
    showCustomToast({ message: 'Infrastructure supprimée', type: 'success' })
    fetchData()
  }
}

onMounted(async () => {
  await loadReferenceData()
  fetchData()
})

const handleExport = (format: string) => {
  showCustomToast({
    message: `Export ${format.toUpperCase()} lancé...`,
    type: 'success',
  })
}
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/infra/operations" module-name="infra">
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Registre Infrastructures"
        description="Gestion du registre des bâtiments et locaux"
        :tags="tagInfraNavOperations"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper class="flex-1 flex flex-col min-h-0">
        <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between mb-4">
          <div class="flex flex-1 items-center gap-2">
            <div class="relative w-full max-w-xs">
              <Input
                type="text"
                v-model="query"
                placeholder="Recherche..."
                class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div
                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
              >
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>

            <Popover>
              <PopoverTrigger as-child>
                <Button variant="ghost" size="sm" class="h-10 rounded-md border bg-white">
                  <span class="hidden sm:flex"> Filtre</span>
                  <span class="iconify hugeicons--filter"></span>
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-80">
                <div class="grid gap-4">
                  <div class="space-y-2">
                    <h4 class="font-medium leading-none">Filtrage</h4>
                  </div>
                  <div class="flex flex-col gap-3.5">
                    <div class="text-sm text-gray-500">Filtres (Bientôt disponible)</div>
                  </div>
                </div>
              </PopoverContent>
            </Popover>
          </div>

          <div class="flex flex-wrap items-center sm:justify-end gap-2.5 flex-1">
            <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" @click="navigateToAdd">
                <span class="flex iconify hugeicons--plus-sign"></span>
                <span class="hidden sm:flex ml-1">Ajouter</span>
            </Button>
            <ExportDropdown :loading="false" @export="handleExport" />
          </div>
        </div>

        <div
          v-if="loading"
          class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500"
        >
          <IconifySpinner class="text-2xl" />
          <span>Chargement...</span>
        </div>

        <div
          v-else-if="filteredData && filteredData.length"
          class="mt-4 rounded-md overflow-hidden flex flex-1 bg-white border"
        >
           <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[50px]"><Checkbox /></TableHead>
                <TableHead>Nom</TableHead>
                <TableHead>Catégorie</TableHead>
                <TableHead>Bailleur</TableHead>
                <TableHead>Date Construction</TableHead>
                <TableHead>Montant</TableHead>
                <TableHead>Emplacement</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="item in filteredData" :key="item.id">
                  <TableCell><Checkbox /></TableCell>
                  <TableCell class="font-medium">{{ item.name }}</TableCell>
                  <TableCell>{{ getCategoryName(item.infra_categorie_id) }}</TableCell>
                  <TableCell>{{ getBailleurName(item.infra_bailleur_id) }}</TableCell>
                  <TableCell>{{ item.date_construction ? new Date(item.date_construction).toLocaleDateString('fr-FR') : '-' }}</TableCell>
                  <TableCell>{{ item.montant_construction ? item.montant_construction + ' $' : '-' }}</TableCell>
                  <TableCell>{{ item.emplacement || '-' }}</TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                        <Button
                          size="icon"
                          variant="ghost"
                          class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                          @click="navigateToEdit(item.id)"
                        >
                          <span class="iconify hugeicons--edit-01"></span>
                        </Button>
                         <Dialog>
                          <DialogTrigger as-child>
                             <Button
                              size="icon"
                              variant="ghost"
                              class="size-8 text-gray-500 hover:text-red-600 hover:bg-red-50"
                            >
                              <span class="iconify hugeicons--delete-02"></span>
                            </Button>
                          </DialogTrigger>
                          <DialogContent>
                            <DialogHeader>
                              <DialogTitle>Supprimer l'infrastructure ?</DialogTitle>
                              <DialogDescription>
                                Cette action est irréversible.
                              </DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                               <DialogClose as-child><Button variant="outline">Annuler</Button></DialogClose>
                               <Button variant="destructive" @click="handleDelete(item.id)" :disabled="deleting">
                                  <IconifySpinner v-if="deleting" class="mr-2" />
                                  <span>Supprimer</span>
                               </Button>
                            </DialogFooter>
                          </DialogContent>
                        </Dialog>
                    </div>
                  </TableCell>
                </TableRow>
            </TableBody>
           </Table>
        </div>

        <div
          v-else
          class="flex flex-col items-center justify-center h-full py-10 bg-white rounded-md text-gray-500 border mt-4"
        >
          <span class="iconify hugeicons--package-open text-4xl mb-2"></span>
          <span>Aucune infrastructure trouvée.</span>
        </div>

      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>
