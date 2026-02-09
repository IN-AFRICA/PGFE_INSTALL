<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
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
import { showCustomToast } from '@/utils/widgets/custom_toast'
import { useGetApi } from '@/composables/useGetApi'
import { usePostApi } from '@/composables/usePostApi'
import { usePutApi } from '@/composables/usePutApi'
import { useDeleteApi } from '@/composables/useDeleteApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import IconifySpinner from '@/components/ui/spinner/IconifySpinner.vue'

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Infrastructure', href: '/infra' },
    { label: 'Préalables', href: '/infra/prealables' },
    { label: 'Types & États', isActive: true },
  ],
}

const activeTagName = 'types-etats'

// ==========================================
// TYPES LOGIC
// ==========================================
const { data: rawTypes, loading: loadingTypes, fetchData: fetchTypes } = useGetApi(API_ROUTES.GET_INFRA_TYPES)
const { postData: postType, loading: creatingType, success: createTypeSuccess } = usePostApi()
const { putData: putType, loading: updatingType, success: updateTypeSuccess } = usePutApi()
const { deleteItem: deleteType, deleting: deletingType, success: deleteTypeSuccess } = useDeleteApi()

const types = computed(() => {
  if (!rawTypes.value) return []
  if (Array.isArray(rawTypes.value)) return rawTypes.value
  return (rawTypes.value as any).data || []
})

const queryType = ref('')
const filteredTypes = computed(() => {
  if (!queryType.value) return types.value
  const lowerQuery = queryType.value.toLowerCase()
  return types.value.filter((t: any) => t.name.toLowerCase().includes(lowerQuery))
})

// Type Modal State
const isTypeDialogOpen = ref(false)
const isTypeEditing = ref(false)
const currentTypeId = ref<number | null>(null)
const formTypeData = ref({ name: '' })

const openAddTypeModal = () => {
  isTypeEditing.value = false
  currentTypeId.value = null
  formTypeData.value = { name: '' }
  isTypeDialogOpen.value = true
}

const openEditTypeModal = (item: any) => {
  isTypeEditing.value = true
  currentTypeId.value = item.id
  formTypeData.value = { name: item.name }
  isTypeDialogOpen.value = true
}

const handleSaveType = async () => {
  if (!formTypeData.value.name) {
    showCustomToast({ message: 'Le nom est requis', type: 'error' })
    return
  }
  const payload = { name: formTypeData.value.name }

  if (isTypeEditing.value && currentTypeId.value !== null) {
    await putType(API_ROUTES.UPDATE_INFRA_TYPE(currentTypeId.value), payload)
    if (updateTypeSuccess.value) {
      showCustomToast({ message: 'Type modifié avec succès', type: 'success' })
      fetchTypes()
      isTypeDialogOpen.value = false
    }
  } else {
    await postType(API_ROUTES.CREATE_INFRA_TYPE, payload)
    if (createTypeSuccess.value) {
      showCustomToast({ message: 'Type ajouté avec succès', type: 'success' })
      fetchTypes()
      isTypeDialogOpen.value = false
    }
  }
}

const handleDeleteType = async (id: number) => {
  await deleteType(API_ROUTES.DELETE_INFRA_TYPE(id))
  if (deleteTypeSuccess.value) {
    showCustomToast({ message: 'Type supprimé avec succès', type: 'success' })
    fetchTypes()
  }
}


// ==========================================
// STATES LOGIC
// ==========================================
const { data: rawStates, loading: loadingStates, fetchData: fetchStates } = useGetApi(API_ROUTES.GET_INFRA_STATES)
const { postData: postState, loading: creatingState, success: createStateSuccess } = usePostApi()
const { putData: putState, loading: updatingState, success: updateStateSuccess } = usePutApi()
const { deleteItem: deleteState, deleting: deletingState, success: deleteStateSuccess } = useDeleteApi()

const states = computed(() => {
  if (!rawStates.value) return []
  if (Array.isArray(rawStates.value)) return rawStates.value
  return (rawStates.value as any).data || []
})

const queryState = ref('')
const filteredStates = computed(() => {
  if (!queryState.value) return states.value
  const lowerQuery = queryState.value.toLowerCase()
  return states.value.filter((s: any) => s.name.toLowerCase().includes(lowerQuery))
})

// State Modal State
const isStateDialogOpen = ref(false)
const isStateEditing = ref(false)
const currentStateId = ref<number | null>(null)
const formStateData = ref({ name: '' })

const openAddStateModal = () => {
  isStateEditing.value = false
  currentStateId.value = null
  formStateData.value = { name: '' }
  isStateDialogOpen.value = true
}

const openEditStateModal = (item: any) => {
  isStateEditing.value = true
  currentStateId.value = item.id
  formStateData.value = { name: item.name }
  isStateDialogOpen.value = true
}

const handleSaveState = async () => {
  if (!formStateData.value.name) {
    showCustomToast({ message: 'Le nom est requis', type: 'error' })
    return
  }
  const payload = { name: formStateData.value.name }

  if (isStateEditing.value && currentStateId.value !== null) {
    await putState(API_ROUTES.UPDATE_INFRA_STATE(currentStateId.value), payload)
    if (updateStateSuccess.value) {
      showCustomToast({ message: 'État modifié avec succès', type: 'success' })
      fetchStates()
      isStateDialogOpen.value = false
    }
  } else {
    await postState(API_ROUTES.CREATE_INFRA_STATE, payload)
    if (createStateSuccess.value) {
      showCustomToast({ message: 'État ajouté avec succès', type: 'success' })
      fetchStates()
      isStateDialogOpen.value = false
    }
  }
}

const handleDeleteState = async (id: number) => {
  await deleteState(API_ROUTES.DELETE_INFRA_STATE(id))
  if (deleteStateSuccess.value) {
    showCustomToast({ message: 'État supprimé avec succès', type: 'success' })
    fetchStates()
  }
}

// Global Mount
onMounted(() => {
  fetchTypes()
  fetchStates()
})
</script>

<template>
  <DashLayout
    :breadcrumb="breadcrumbItems"
    active-route="/infra/prealables"
    module-name="infra"
  >
    <div class="pb-6 mx-auto w-full max-w-7xl h-full flex flex-col">
      <DashPageHeader
        title="Types & États"
        description="Gérez les types et les états des équipements"
        :tags="tagInfraNavPrealables"
        :active-tag-name="activeTagName"
      />

      <!-- Content Area with Grid -->
      <div class="grid lg:grid-cols-2 gap-7 flex-1">
        
        <!-- =======================
             LEFT COLUMN: TYPES
             ======================= -->
        <BoxPanelWrapper>
          <span class="text-gray-500 my-1.5 text-xl font-medium">Types d'Équipements</span>
          
          <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between mb-4 gap-3">
             <div class="relative w-full max-w-xs">
              <Input
                v-model="queryType"
                placeholder="Rechercher un type..."
                class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>
            <Button size="md" class="rounded-md" @click="openAddTypeModal">
              <span class="flex iconify hugeicons--plus-sign mr-1"></span>
              Nouveau
            </Button>
          </div>

          <!-- Loading Type -->
          <div v-if="loadingTypes" class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
            <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
            <span>Chargement...</span>
          </div>

          <!-- Table Types -->
          <div v-else class="rounded-md border bg-white flex-1 overflow-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[50px]"><Checkbox /></TableHead>
                  <TableHead>Nom</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="type in filteredTypes" :key="type.id">
                  <TableCell><Checkbox /></TableCell>
                  <TableCell class="font-medium">{{ type.name }}</TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                      <Button
                        size="icon"
                        variant="ghost"
                        class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                        @click="openEditTypeModal(type)"
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
                            <DialogTitle>Supprimer le type ?</DialogTitle>
                            <DialogDescription>
                              Supprimer "{{ type.name }}" est irréversible.
                            </DialogDescription>
                          </DialogHeader>
                          <DialogFooter>
                            <DialogClose as-child><Button variant="outline">Annuler</Button></DialogClose>
                            <Button variant="destructive" @click="handleDeleteType(type.id)" :disabled="deletingType">
                                <IconifySpinner v-if="deletingType" class="mr-2" />
                                <span v-if="deletingType">Suppression...</span>
                                <span v-else>Supprimer</span>
                            </Button>
                          </DialogFooter>
                        </DialogContent>
                      </Dialog>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="filteredTypes.length === 0">
                   <TableCell colspan="3" class="h-24 text-center text-gray-500">Aucun type trouvé.</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </BoxPanelWrapper>


        <!-- =======================
             RIGHT COLUMN: STATES
             ======================= -->
        <BoxPanelWrapper>
          <span class="text-gray-500 my-1.5 text-xl font-medium">États d'Équipements</span>
          
           <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between mb-4 gap-3">
             <div class="relative w-full max-w-xs">
              <Input
                v-model="queryState"
                placeholder="Rechercher un état..."
                class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>
            <Button size="md" class="rounded-md" @click="openAddStateModal">
              <span class="flex iconify hugeicons--plus-sign mr-1"></span>
              Nouveau
            </Button>
          </div>

          <!-- Loading State -->
          <div v-if="loadingStates" class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
            <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
            <span>Chargement...</span>
          </div>

          <!-- Table States -->
          <div v-else class="rounded-md border bg-white flex-1 overflow-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[50px]"><Checkbox /></TableHead>
                  <TableHead>Nom</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="state in filteredStates" :key="state.id">
                  <TableCell><Checkbox /></TableCell>
                  <TableCell class="font-medium">{{ state.name }}</TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                       <Button
                        size="icon"
                        variant="ghost"
                        class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                        @click="openEditStateModal(state)"
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
                            <DialogTitle>Supprimer l'état ?</DialogTitle>
                            <DialogDescription>
                              Supprimer "{{ state.name }}" est irréversible.
                            </DialogDescription>
                          </DialogHeader>
                          <DialogFooter>
                             <DialogClose as-child><Button variant="outline">Annuler</Button></DialogClose>
                             <Button variant="destructive" @click="handleDeleteState(state.id)" :disabled="deletingState">
                                <IconifySpinner v-if="deletingState" class="mr-2" />
                                <span v-if="deletingState">Suppression...</span>
                                <span v-else>Supprimer</span>
                             </Button>
                          </DialogFooter>
                        </DialogContent>
                      </Dialog>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="filteredStates.length === 0">
                   <TableCell colspan="3" class="h-24 text-center text-gray-500">Aucun état trouvé.</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </BoxPanelWrapper>

      </div>
    </div>

    <!-- =======================
         MODALS
         ======================= -->

    <!-- Modal Form for Types -->
    <Dialog :open="isTypeDialogOpen" @update:open="isTypeDialogOpen = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{{ isTypeEditing ? 'Modifier Type' : 'Ajouter Type' }}</DialogTitle>
          <DialogDescription>
            Remplissez les informations pour le type d'équipement.
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="typeName" class="text-right">Nom</Label>
            <Input id="typeName" v-model="formTypeData.name" class="col-span-3" placeholder="Ex: Mobilier" />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="isTypeDialogOpen = false">Annuler</Button>
          <Button type="submit" @click="handleSaveType" :disabled="creatingType || updatingType">
            <IconifySpinner v-if="creatingType || updatingType" class="mr-2" />
            <span v-if="creatingType || updatingType">Enregistrement...</span>
            <span v-else>Enregistrer</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Modal Form for States -->
    <Dialog :open="isStateDialogOpen" @update:open="isStateDialogOpen = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{{ isStateEditing ? 'Modifier État' : 'Ajouter État' }}</DialogTitle>
          <DialogDescription>
            Remplissez les informations pour l'état d'équipement.
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="stateName" class="text-right">Nom</Label>
            <Input id="stateName" v-model="formStateData.name" class="col-span-3" placeholder="Ex: Bon état" />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="isStateDialogOpen = false">Annuler</Button>
          <Button type="submit" @click="handleSaveState" :disabled="creatingState || updatingState">
            <IconifySpinner v-if="creatingState || updatingState" class="mr-2" />
            <span v-if="creatingState || updatingState">Enregistrement...</span>
            <span v-else>Enregistrer</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </DashLayout>
</template>
