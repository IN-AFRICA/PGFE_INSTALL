<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
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
import { Textarea } from '@/components/ui/textarea'
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
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const route = useRoute()
const router = useRouter()

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Infrastructure', href: '/infra' },
    { label: 'Préalables', isActive: true },
  ],
}

const activeTagName = 'inventaires'

// === API Integration for Inventories ===
const { data: rawInventories, loading, fetchData } = useGetApi(API_ROUTES.GET_INFRA_INVENTORIES)
const { postData, loading: creating, success: createSuccess } = usePostApi()
const { deleteItem, deleting, success: deleteSuccess } = useDeleteApi()

// For adding equipments/states to inventory
const { data: rawEquipments, fetchData: fetchEquipments } = useGetApi(API_ROUTES.GET_INFRA_EQUIPMENTS)
const { data: rawStates, fetchData: fetchStates } = useGetApi(API_ROUTES.GET_INFRA_STATES)

const inventories = computed(() => {
  if (!rawInventories.value) return []
  if (Array.isArray(rawInventories.value)) return rawInventories.value
  return (rawInventories.value as any).data || []
})

const equipments = computed(() => {
  if (!rawEquipments.value) return []
  if (Array.isArray(rawEquipments.value)) return rawEquipments.value
  return (rawEquipments.value as any).data || []
})

const states = computed(() => {
  if (!rawStates.value) return []
  if (Array.isArray(rawStates.value)) return rawStates.value
  return (rawStates.value as any).data || []
})

// === Filtering ===
const query = ref('')
const filteredInventories = computed(() => {
  if (!query.value) return inventories.value
  const lowerQuery = query.value.toLowerCase()
  return inventories.value.filter(
    (i: any) =>
      (i.note && i.note.toLowerCase().includes(lowerQuery)) ||
      i.inventory_date.includes(lowerQuery)
  )
})

// === CRUD Operations ===
const isDialogOpen = ref(false)
const showDetailsDialog = ref(false)
const formData = ref({
  date: new Date().toISOString().split('T')[0],
  note: '',
})

// Details Form Data
const detailsFormData = ref({
  inventoryId: null as number | null,
  equipmentId: '',
  quantity: 1,
  stateId: '',
  detailsNote: '',
})

onMounted(() => {
  fetchData()
})

const openAddModal = () => {
  formData.value = {
    date: new Date().toISOString().split('T')[0],
    note: '',
  }
  isDialogOpen.value = true
}

const handleCreateInventory = async () => {
  const payload = {
    inventory_date: formData.value.date,
    note: formData.value.note || null,
  }

  await postData(API_ROUTES.CREATE_INFRA_INVENTORY, payload)
  if (createSuccess.value) {
    showCustomToast({ message: 'Inventaire créé avec succès', type: 'success' })
    fetchData()
    isDialogOpen.value = false
  }
}

const openAddDetailsModal = (inventory: any) => {
  detailsFormData.value = {
    inventoryId: inventory.id,
    equipmentId: '',
    quantity: 1,
    stateId: '',
    detailsNote: '',
  }
  fetchEquipments()
  fetchStates()
  showDetailsDialog.value = true
}

const handleAddDetails = async () => {
    if (!detailsFormData.value.inventoryId) return
    
    if (!detailsFormData.value.equipmentId) {
        showCustomToast({ message: 'Veuillez sélectionner un équipement', type: 'error' })
        return
    }

    // Build payload according to API spec for equipment inventory
    const payload = {
        items: [
            {
                equipment_id: parseInt(detailsFormData.value.equipmentId),
                quantity: detailsFormData.value.quantity
            }
        ]
    }

    const url = API_ROUTES.CREATE_INVENTORY_ITEM(detailsFormData.value.inventoryId)
    await postData(url, payload)
    
    if (createSuccess.value) {
        showCustomToast({ message: 'Équipement ajouté à l\'inventaire', type: 'success' })
        showDetailsDialog.value = false
        fetchData()
    }
}

const handleDelete = async (id: number) => {
  await deleteItem(API_ROUTES.DELETE_INFRA_INVENTORY(id))
  if (deleteSuccess.value) {
    showCustomToast({ message: 'Inventaire supprimé avec succès', type: 'success' })
    fetchData()
  }
}

const viewDetails = (id: number) => {
  router.push(`/infra/prealables/inventaires/${id}`)
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
        title="Gestion des Inventaires"
        description="Créez et suivez les inventaires d'équipements"
        :tags="tagInfraNavPrealables"
        :active-tag-name="activeTagName"
      />

      <BoxPanelWrapper class="flex-1 flex flex-col min-h-0">
        <!-- Toolbar -->
        <div class="flex sm:items-center gap-3 flex-col sm:flex-row sm:justify-between mb-4">
          <div class="relative w-full max-w-xs">
            <Input
              v-model="query"
              placeholder="Rechercher un inventaire..."
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
                <TableHead>Date</TableHead>
                <TableHead>Note / Description</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in filteredInventories" :key="item.id">
                <TableCell><Checkbox /></TableCell>
                <TableCell>{{ item.id }}</TableCell>
                <TableCell>{{ item.inventory_date }}</TableCell>
                <TableCell>{{ item.note || '-' }}</TableCell>
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
                     <Button
                        size="icon"
                        variant="ghost"
                        class="size-8 text-gray-500 hover:text-green-600 hover:bg-green-50"
                        title="Ajouter Équipement/État"
                        @click="openAddDetailsModal(item)"
                      >
                        <span class="iconify hugeicons--plus-sign-circle"></span>
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
                 <TableCell colspan="5" class="h-24 text-center">Aucun résultat trouvé.</TableCell>
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
          <DialogTitle>Créer un nouvel inventaire</DialogTitle>
          <DialogDescription>Initialisez une nouvelle session d'inventaire.</DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="date" class="text-right">Date</Label>
            <Input id="date" type="date" v-model="formData.date" class="col-span-3" />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="note" class="text-right">Note</Label>
            <Input id="note" v-model="formData.note" class="col-span-3" placeholder="Ex: Inventaire T1" />
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

    <!-- Add Details Modal -->
    <Dialog :open="showDetailsDialog" @update:open="showDetailsDialog = $event">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Ajouter un élément à l'inventaire</DialogTitle>
          <DialogDescription>Enregistrez un équipement ou un état réel.</DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
           <div class="grid gap-2">
             <Label>Équipement</Label>
             <Select v-model="detailsFormData.equipmentId">
               <SelectTrigger><SelectValue placeholder="Choisir équipement" /></SelectTrigger>
               <SelectContent>
                 <SelectItem v-for="eq in equipments" :key="eq.id" :value="eq.id.toString()">
                   {{ eq.name }}
                 </SelectItem>
               </SelectContent>
             </Select>
           </div>
           
           <div class="grid grid-cols-2 gap-4">
              <div class="grid gap-2">
                 <Label>Quantité</Label>
                 <Input type="number" min="1" v-model="detailsFormData.quantity" />
              </div>
              <div class="grid gap-2">
                 <Label>État constaté</Label>
                 <Select v-model="detailsFormData.stateId">
                   <SelectTrigger><SelectValue placeholder="État" /></SelectTrigger>
                   <SelectContent>
                     <SelectItem v-for="st in states" :key="st.id" :value="st.id.toString()">
                       {{ st.name }}
                     </SelectItem>
                   </SelectContent>
                 </Select>
              </div>
           </div>

           <div class="grid gap-2">
             <Label>Note / Observation</Label>
             <Textarea v-model="detailsFormData.detailsNote" placeholder="Détails supplémentaires..." />
           </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showDetailsDialog = false">Fermer</Button>
          <Button type="submit" @click="handleAddDetails" :disabled="creating">
            <IconifySpinner v-if="creating" class="mr-2" />
            <span v-if="creating">Ajout...</span>
            <span v-else>Ajouter</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </DashLayout>
</template>
