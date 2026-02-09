<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashLayout from '@/components/templates/DashLayout.vue'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { tagInfraNavPrealables } from '@/components/templates/infra/tags-links'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const { data: rawInventories, loading, fetchData } = useGetApi(API_ROUTES.GET_INFRA_INFRASTRUCTURE_INVENTARIES)
// Tentative d'utiliser les classes comme infrastructures (à confirmer si c'est le bon endpoint)
const { data: rawInfrastructures, fetchData: fetchInfrastructures } = useGetApi(API_ROUTES.GET_CLASSROOMS)

const { postData, loading: creating, success: createSuccess, error: createError } = usePostApi()
const { deleteItem, deleting, success: deleteSuccess } = useDeleteApi()

const router = useRouter()

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Infrastructure', href: '/infra' },
    { label: 'Préalables', href: '/infra/prealables' },
    { label: 'Inv. Infrastructures', isActive: true },
  ],
}

const activeTagName = 'inventaire-infrastructures'

const inventories = computed(() => {
  if (!rawInventories.value) return []
  if (Array.isArray(rawInventories.value)) return rawInventories.value
  return (rawInventories.value as any).data || []
})

const infrastructures = computed(() => {
  if (!rawInfrastructures.value) return []
  if (Array.isArray(rawInfrastructures.value)) return rawInfrastructures.value
  // Gestion de la pagination standard laravel si présent
  return (rawInfrastructures.value as any).data || []
})

const query = ref('')
const filteredInventories = computed(() => {
  if (!query.value) return inventories.value
  const lowerQuery = query.value.toLowerCase()
  return inventories.value.filter(
    (i: any) =>
      (i.title?.toLowerCase().includes(lowerQuery)) ||
      (i.observations?.join(' ')?.toLowerCase().includes(lowerQuery)) ||
      i.inventory_date?.includes(lowerQuery)
  )
})

const isDialogOpen = ref(false)
const formData = ref({
  infra_infrastructure_id: '',
  title: '',
  inventory_date: new Date().toISOString().split('T')[0],
  status: '',
  observations: '',
})

const statusOptions = [
  { value: 'excellent', label: 'Excellent' },
  { value: 'bon', label: 'Bon' },
  { value: 'moyen', label: 'Moyen' },
  { value: 'mauvais', label: 'Mauvais' },
  { value: 'critique', label: 'Critique' },
]

onMounted(() => {
  fetchData()
  fetchInfrastructures()
})

const openAddModal = () => {
  formData.value = {
    infra_infrastructure_id: '',
    title: '',
    inventory_date: new Date().toISOString().split('T')[0],
    status: '',
    observations: '',
  }
  isDialogOpen.value = true
}

const handleCreateInventory = async () => {
  const payload = {
    infra_infrastructure_id: parseInt(formData.value.infra_infrastructure_id),
    title: formData.value.title,
    inventory_date: formData.value.inventory_date,
    status: formData.value.status,
    observations: formData.value.observations ? [formData.value.observations] : [], // API expects array of strings
  }

  await postData(API_ROUTES.CREATE_INFRA_INFRASTRUCTURE_INVENTARY, payload)
  if (createSuccess.value) {
    showCustomToast({ message: 'Inventaire créé avec succès', type: 'success' })
    fetchData()
    isDialogOpen.value = false
  } else {
     showCustomToast({ 
      message: createError.value || "Erreur lors de la création de l'inventaire", 
      type: 'error' 
    })
  }
}

const handleDelete = async (id: number) => {
  await deleteItem(API_ROUTES.DELETE_INFRA_INFRASTRUCTURE_INVENTARY(id))
  if (deleteSuccess.value) {
    showCustomToast({ message: 'Inventaire supprimé avec succès', type: 'success' })
    fetchData()
  }
}

const viewDetails = (id: number) => {
  // TODO: Create a details page for specific infrastructure inventory if needed
  // router.push(`/infra/prealables/inventories/${id}`)
  showCustomToast({ message: 'Détails bientôt disponibles', type: 'success' })
}

const getInfrastructureName = (id: number) => {
  const infra = infrastructures.value.find((i: any) => i.id === id)
  return infra ? infra.name : `ID: ${id}`
}
</script>

<template>
  <DashLayout
    :breadcrumb="breadcrumbItems"
    active-route="/infra/prealables"
    module-name="infra"
  >
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Inventaires Infrastructures"
        description="Suivi de l'état des bâtiments et salles"
        :tags="tagInfraNavPrealables"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper class="flex-1 flex flex-col min-h-0">
        <!-- Toolbar -->
        <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between mb-4">
          <div class="relative w-full max-w-xs">
            <Input
              v-model="query"
              placeholder="Rechercher..."
              class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
            />
            <div
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
            >
              <span class="flex iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>

          <Button size="md" class="rounded-md max-sm:flex-1 sm:w-max" @click="openAddModal">
            <span class="flex iconify hugeicons--plus-sign"></span>
            <span class="hidden sm:flex">Nouvel Inventaire</span>
          </Button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
          <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
          <span>Chargement...</span>
        </div>

        <!-- Table -->
        <div v-else class="rounded-md border bg-white flex-1 overflow-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[50px]"><Checkbox /></TableHead>
                <TableHead class="w-[80px]">ID</TableHead>
                <TableHead>Titre</TableHead>
                <TableHead>Date</TableHead>
                <TableHead>Infrastructure</TableHead>
                <TableHead>Statut</TableHead>
                <TableHead>Observations</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in filteredInventories" :key="item.id">
                <TableCell><Checkbox /></TableCell>
                <TableCell>{{ item.id }}</TableCell>
                <TableCell class="font-medium">{{ item.title }}</TableCell>
                <TableCell>{{ item.inventory_date }}</TableCell>
                <TableCell>{{ getInfrastructureName(item.infra_infrastructure_id) }}</TableCell>
                <TableCell>
                  <span class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                    :class="{
                      'bg-green-100 text-green-800': item.status === 'excellent',
                      'bg-lime-100 text-lime-800': item.status === 'bon',
                      'bg-yellow-100 text-yellow-800': item.status === 'moyen',
                      'bg-orange-100 text-orange-800': item.status === 'mauvais',
                      'bg-red-100 text-red-800': item.status === 'critique'
                    }"
                  >
                    {{ item.status }}
                  </span>
                </TableCell>
                <TableCell class="max-w-[200px] truncate">
                   {{ item.observations && item.observations.length ? item.observations.join(', ') : '-' }}
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                       <Button
                        size="icon"
                        variant="ghost"
                        class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                        title="Voir détails"
                        @click="viewDetails(item.id)"
                      >
                        <span class="iconify hugeicons--view"></span>
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
                            <DialogTitle>Supprimer l'inventaire ?</DialogTitle>
                            <DialogDescription>
                              Confirmez-vous la suppression de l'inventaire "{{ item.title }}" ?
                            </DialogDescription>
                          </DialogHeader>
                          <DialogFooter>
                             <DialogClose as-child>
                                <Button variant="outline">Annuler</Button>
                             </DialogClose>
                             <Button variant="destructive" @click="handleDelete(item.id)" :disabled="deleting">
                                <IconifySpinner v-if="deleting" class="mr-2" />
                                <span v-if="deleting">Suppression...</span>
                                <span v-else>Supprimer</span>
                             </Button>
                          </DialogFooter>
                        </DialogContent>
                      </Dialog>
                    </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="filteredInventories.length === 0">
                 <TableCell colspan="8" class="h-24 text-center">Aucun résultat trouvé.</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </BoxPanelWrapper>
    </div>

    <!-- Create Inventory Modal -->
    <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
      <DialogContent class="sm:max-w-[550px]">
        <DialogHeader>
          <DialogTitle>Nouvel Inventaire Infrastructure</DialogTitle>
          <DialogDescription>Enregistrez l'état d'un bâtiment ou d'une salle.</DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="title" class="text-right">Titre</Label>
            <Input id="title" v-model="formData.title" class="col-span-3" placeholder="Ex: État Salle A1" />
          </div>

          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="infra_id" class="text-right">Infrastructure</Label>
            <div class="col-span-3">
               <Select v-model="formData.infra_infrastructure_id">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Sélectionner une infrastructure (Salle/Bât.)" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="infra in infrastructures" :key="infra.id" :value="infra.id.toString()">
                    {{ infra.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="date" class="text-right">Date</Label>
            <Input id="date" type="date" v-model="formData.inventory_date" class="col-span-3" />
          </div>

          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="status" class="text-right">Statut</Label>
             <div class="col-span-3">
               <Select v-model="formData.status">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="État général" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="grid grid-cols-4 items-start gap-4">
            <Label for="observations" class="text-right pt-2">Observations</Label>
            <Textarea
              id="observations"
              v-model="formData.observations"
              class="col-span-3"
              placeholder="Détails sur l'état, problèmes constatés..."
            />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="isDialogOpen = false">Annuler</Button>
          <Button type="submit" @click="handleCreateInventory" :disabled="creating">
            <IconifySpinner v-if="creating" class="mr-2" />
            <span v-if="creating">Enregistrement...</span>
            <span v-else>Créer</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </DashLayout>
</template>
