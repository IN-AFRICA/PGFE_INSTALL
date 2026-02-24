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
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'
import { Textarea } from '@/components/ui/textarea'

// Using specific infrastructure inventory API
const { data: rawInventories, loading, fetchData } = useGetApi(API_ROUTES.GET_INFRA_INFRASTRUCTURE_INVENTAIRES)
const { data: rawInfraList, fetchData: fetchInfrastructures } = useGetApi(API_ROUTES.GET_INFRA_INFRASTRUCTURES)
const { data: rawStates, fetchData: fetchStates } = useGetApi(API_ROUTES.GET_INFRA_STATES)
const { postData, loading: creating, success: createSuccess } = usePostApi()
const { putData, loading: updating, success: updateSuccess } = usePutApi()
const { deleteItem, deleting, success: deleteSuccess } = useDeleteApi()

const infrastructures = computed(() => {
  if (!rawInfraList.value) return []
  if (Array.isArray(rawInfraList.value)) return rawInfraList.value
  return (rawInfraList.value as any).data || []
})

const states = computed(() => {
  if (!rawStates.value) return []
  if (Array.isArray(rawStates.value)) return rawStates.value
  return (rawStates.value as any).data || []
})

const getInfraName = (id: number) => {
  const infra = infrastructures.value.find((i: any) => i.id === id)
  return infra ? infra.name : `ID: ${id}`
}

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

const query = ref('')
const filteredInventories = computed(() => {
  if (!query.value) return inventories.value
  const lowerQuery = query.value.toLowerCase()
  return inventories.value.filter(
    (i: any) =>
      (i.title && i.title.toLowerCase().includes(lowerQuery)) ||
      (i.inventory_date && i.inventory_date.includes(lowerQuery))
  )
})

const isDialogOpen = ref(false)
const formData = ref({
  date: new Date().toISOString().split('T')[0],
  title: '',
  status: 'bon',
  infra_infrastructure_id: '',
})

onMounted(() => {
  fetchData()
  fetchInfrastructures()
  fetchStates()
})

const openAddModal = () => {
  formData.value = {
    date: new Date().toISOString().split('T')[0],
    title: '',
    status: 'bon',
    infra_infrastructure_id: '',
  }
  isDialogOpen.value = true
}

const handleCreateInventory = async () => {
  const payload = {
    inventory_date: formData.value.date,
    title: formData.value.title,
    status: formData.value.status,
    infra_infrastructure_id: parseInt(formData.value.infra_infrastructure_id),
  }

  await postData(API_ROUTES.CREATE_INFRA_INFRASTRUCTURE_INVENTAIRE, payload)
  if (createSuccess.value) {
    showCustomToast({ message: 'Inventaire d\'infrastructure créé avec succès', type: 'success' })
    fetchData()
    isDialogOpen.value = false
  }
}

const handleDelete = async (id: number) => {
  await deleteItem(API_ROUTES.DELETE_INFRA_INFRASTRUCTURE_INVENTAIRE(id))
  if (deleteSuccess.value) {
    showCustomToast({ message: 'Inventaire supprimé avec succès', type: 'success' })
    fetchData()
  }
}

const viewDetails = (id: number) => {
  router.push(`/infra/prealables/inventories/${id}`)
}

// Add Item to Inventory Modal
const showAddItemDialog = ref(false)
const addItemFormData = ref({
  inventoryId: null as number | null,
  type: 'zone',
  name: '',
  description: '',
  state_id: '',
})

const openAddItemModal = (inventory: any) => {
  addItemFormData.value = {
    inventoryId: inventory.id,
    type: 'zone',
    name: '',
    description: '',
    state_id: '',
  }
  showAddItemDialog.value = true
}

const handleAddItem = async () => {
  if (!addItemFormData.value.inventoryId) return

  // Validate required fields
  if (!addItemFormData.value.state_id) {
    showCustomToast({ message: 'Veuillez sélectionner un état', type: 'error' })
    return
  }

  if (!addItemFormData.value.name) {
    showCustomToast({ message: 'Veuillez saisir un label', type: 'error' })
    return
  }

  // Build item according to API spec for infrastructure
  const item: any = {
    type: addItemFormData.value.type,
    label: addItemFormData.value.name,
    etat: states.value.find((s: any) => s.id === parseInt(addItemFormData.value.state_id))?.name || 'bon',
  }

  if (addItemFormData.value.description) {
    item.description = addItemFormData.value.description
  }

  // Wrap in items array as per API spec
  const payload = {
    items: [item]
  }

  await postData(
    API_ROUTES.CREATE_INFRA_INFRASTRUCTURE_INVENTAIRE_ITEM(addItemFormData.value.inventoryId),
    payload
  )

  if (createSuccess.value) {
    showCustomToast({ message: 'Élément ajouté avec succès', type: 'success' })
    showAddItemDialog.value = false
    fetchData()
  }
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
        description="Gestion des inventaires d'infrastructures"
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
                <TableHead>Infrastructure</TableHead>
                <TableHead>Emplacement</TableHead>
                <TableHead>Date Inventaire</TableHead>
                <TableHead>Statut</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in filteredInventories" :key="item.id">
                <TableCell><Checkbox /></TableCell>
                <TableCell>{{ item.id }}</TableCell>
                <TableCell class="font-medium">{{ item.title }}</TableCell>
                <TableCell>
                  <div class="flex flex-col">
                    <span class="font-medium text-sm">{{ item.infrastructure?.name || 'N/A' }}</span>
                    <span class="text-xs text-gray-500">Constr: {{ item.infrastructure?.date_construction || 'N/A' }}</span>
                  </div>
                </TableCell>
                <TableCell>{{ item.infrastructure?.emplacement || 'N/A' }}</TableCell>
                <TableCell>{{ item.inventory_date }}</TableCell>
                <TableCell>
                  <span 
                    class="px-2 py-1 rounded-full text-xs font-medium uppercase"
                    :class="{
                      'bg-green-100 text-green-700': item.status === 'excellent' || item.status === 'bon',
                      'bg-yellow-100 text-yellow-700': item.status === 'moyen',
                      'bg-red-100 text-red-700': item.status === 'mauvais' || item.status === 'critique',
                    }"
                  >
                    {{ item.status }}
                  </span>
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
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
                              Confirmez-vous la suppression de l'inventaire du {{ item.inventory_date }} ?
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
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Créer un nouvel inventaire (Infra)</DialogTitle>
          <DialogDescription>Initialisez une nouvelle session d'inventaire Infrastructure.</DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="title" class="text-right">Titre</Label>
            <Input id="title" v-model="formData.title" class="col-span-3" placeholder="Ex: Inventaire Bloc A" />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="infra_id" class="text-right">Infrastructure</Label>
            <div class="col-span-3">
              <Select v-model="formData.infra_infrastructure_id">
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner l'infrastructure" />
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
            <Input id="date" type="date" v-model="formData.date" class="col-span-3" />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="status" class="text-right">Statut</Label>
            <Select v-model="formData.status" class="col-span-3">
              <SelectTrigger class="col-span-3">
                <SelectValue placeholder="Choisir un statut" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="excellent">Excellent</SelectItem>
                <SelectItem value="bon">Bon</SelectItem>
                <SelectItem value="moyen">Moyen</SelectItem>
                <SelectItem value="mauvais">Mauvais</SelectItem>
                <SelectItem value="critique">Critique</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="isDialogOpen = false">Annuler</Button>
          <Button type="submit" @click="handleCreateInventory" :disabled="creating">
            <IconifySpinner v-if="creating" class="mr-2" />
            <span v-if="creating">Création...</span>
            <span v-else>Créer</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Add Item to Inventory Modal -->
    <Dialog :open="showAddItemDialog" @update:open="showAddItemDialog = $event">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Ajouter un élément à l'inventaire</DialogTitle>
          <DialogDescription>
            Ajoutez une zone, une salle ou un élément structurel à inventorier
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="type" class="text-right">Type *</Label>
            <Select v-model="addItemFormData.type" class="col-span-3">
              <SelectTrigger class="col-span-3">
                <SelectValue placeholder="Type d'élément" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="zone">Zone / Toiture</SelectItem>
                <SelectItem value="room">Salle / Local</SelectItem>
                <SelectItem value="element">Élément Structurel</SelectItem>
                <SelectItem value="other">Autre</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="name" class="text-right">Label *</Label>
            <Input id="name" v-model="addItemFormData.name" class="col-span-3" placeholder="Ex: Toiture, Salle A1, Fenêtres, Murs..." />
          </div>

          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="state" class="text-right">État *</Label>
            <Select v-model="addItemFormData.state_id" class="col-span-3">
              <SelectTrigger class="col-span-3">
                <SelectValue placeholder="Sélectionner l'état" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="state in states" :key="state.id" :value="state.id.toString()">
                  {{ state.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="grid grid-cols-4 items-start gap-4">
            <Label for="description" class="text-right pt-2">Description</Label>
            <Textarea
              id="description"
              v-model="addItemFormData.description"
              class="col-span-3"
              placeholder="Détails supplémentaires (ex: Quelques tuiles abîmées)..."
              rows="3"
            />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showAddItemDialog = false">Annuler</Button>
          <Button type="submit" @click="handleAddItem" :disabled="creating">
            <IconifySpinner v-if="creating" class="mr-2" />
            <span v-if="creating">Ajout...</span>
            <span v-else>Ajouter</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </DashLayout>
</template>
