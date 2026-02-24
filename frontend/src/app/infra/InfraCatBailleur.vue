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
import { Textarea } from '@/components/ui/textarea'
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
    { label: 'Catégories & Bailleurs', isActive: true },
  ],
}

const activeTagName = 'cat-bailleurs'

// ==========================================
// CATEGORIES LOGIC
// ==========================================
const { data: rawCategories, loading: loadingCategories, fetchData: fetchCategories } = useGetApi(API_ROUTES.GET_INFRA_CATEGORIES)
const { postData: postCategory, loading: creatingCategory, success: createCategorySuccess } = usePostApi()
const { putData: putCategory, loading: updatingCategory, success: updateCategorySuccess } = usePutApi()
const { deleteItem: deleteCategory, deleting: deletingCategory, success: deleteCategorySuccess } = useDeleteApi()

const categories = computed(() => {
  if (!rawCategories.value) return []
  if (Array.isArray(rawCategories.value)) return rawCategories.value
  return (rawCategories.value as any).data || []
})

const queryCategory = ref('')
const filteredCategories = computed(() => {
  if (!queryCategory.value) return categories.value
  const lowerQuery = queryCategory.value.toLowerCase()
  return categories.value.filter((c: any) => 
    c.name.toLowerCase().includes(lowerQuery) ||
    (c.description && c.description.toLowerCase().includes(lowerQuery))
  )
})

// Category Modal State
const isCategoryDialogOpen = ref(false)
const isCategoryEditing = ref(false)
const currentCategoryId = ref<number | null>(null)
const formCategoryData = ref({ name: '', description: '' })

const openAddCategoryModal = () => {
  isCategoryEditing.value = false
  currentCategoryId.value = null
  formCategoryData.value = { name: '', description: '' }
  isCategoryDialogOpen.value = true
}

const openEditCategoryModal = (item: any) => {
  isCategoryEditing.value = true
  currentCategoryId.value = item.id
  formCategoryData.value = { name: item.name, description: item.description || '' }
  isCategoryDialogOpen.value = true
}

const handleSaveCategory = async () => {
  if (!formCategoryData.value.name) {
    showCustomToast({ message: 'Le nom est requis', type: 'error' })
    return
  }
  const payload = { 
    name: formCategoryData.value.name,
    description: formCategoryData.value.description || null
  }

  if (isCategoryEditing.value && currentCategoryId.value !== null) {
    await putCategory(API_ROUTES.UPDATE_INFRA_CATEGORY(currentCategoryId.value), payload)
    if (updateCategorySuccess.value) {
      showCustomToast({ message: 'Catégorie modifiée avec succès', type: 'success' })
      fetchCategories()
      isCategoryDialogOpen.value = false
    }
  } else {
    await postCategory(API_ROUTES.CREATE_INFRA_CATEGORY, payload)
    if (createCategorySuccess.value) {
      showCustomToast({ message: 'Catégorie ajoutée avec succès', type: 'success' })
      fetchCategories()
      isCategoryDialogOpen.value = false
    }
  }
}

const handleDeleteCategory = async (id: number) => {
  await deleteCategory(API_ROUTES.DELETE_INFRA_CATEGORY(id))
  if (deleteCategorySuccess.value) {
    showCustomToast({ message: 'Catégorie supprimée avec succès', type: 'success' })
    fetchCategories()
  }
}


// ==========================================
// BAILLEURS LOGIC
// ==========================================
const { data: rawBailleurs, loading: loadingBailleurs, fetchData: fetchBailleurs } = useGetApi(API_ROUTES.GET_INFRA_BAILLEURS)
const { postData: postBailleur, loading: creatingBailleur, success: createBailleurSuccess } = usePostApi()
const { putData: putBailleur, loading: updatingBailleur, success: updateBailleurSuccess } = usePutApi()
const { deleteItem: deleteBailleur, deleting: deletingBailleur, success: deleteBailleurSuccess } = useDeleteApi()

const bailleurs = computed(() => {
  if (!rawBailleurs.value) return []
  if (Array.isArray(rawBailleurs.value)) return rawBailleurs.value
  return (rawBailleurs.value as any).data || []
})

const queryBailleur = ref('')
const filteredBailleurs = computed(() => {
  if (!queryBailleur.value) return bailleurs.value
  const lowerQuery = queryBailleur.value.toLowerCase()
  return bailleurs.value.filter((b: any) => 
    b.name.toLowerCase().includes(lowerQuery) ||
    (b.description && b.description.toLowerCase().includes(lowerQuery))
  )
})

// Bailleur Modal State
const isBailleurDialogOpen = ref(false)
const isBailleurEditing = ref(false)
const currentBailleurId = ref<number | null>(null)
const formBailleurData = ref({ name: '', description: '' })

const openAddBailleurModal = () => {
  isBailleurEditing.value = false
  currentBailleurId.value = null
  formBailleurData.value = { name: '', description: '' }
  isBailleurDialogOpen.value = true
}

const openEditBailleurModal = (item: any) => {
  isBailleurEditing.value = true
  currentBailleurId.value = item.id
  formBailleurData.value = { name: item.name, description: item.description || '' }
  isBailleurDialogOpen.value = true
}

const handleSaveBailleur = async () => {
  if (!formBailleurData.value.name) {
    showCustomToast({ message: 'Le nom est requis', type: 'error' })
    return
  }
  const payload = { 
    name: formBailleurData.value.name,
    description: formBailleurData.value.description || null
  }

  if (isBailleurEditing.value && currentBailleurId.value !== null) {
    await putBailleur(API_ROUTES.UPDATE_INFRA_BAILLEUR(currentBailleurId.value), payload)
    if (updateBailleurSuccess.value) {
      showCustomToast({ message: 'Bailleur modifié avec succès', type: 'success' })
      fetchBailleurs()
      isBailleurDialogOpen.value = false
    }
  } else {
    await postBailleur(API_ROUTES.CREATE_INFRA_BAILLEUR, payload)
    if (createBailleurSuccess.value) {
      showCustomToast({ message: 'Bailleur ajouté avec succès', type: 'success' })
      fetchBailleurs()
      isBailleurDialogOpen.value = false
    }
  }
}

const handleDeleteBailleur = async (id: number) => {
  await deleteBailleur(API_ROUTES.DELETE_INFRA_BAILLEUR(id))
  if (deleteBailleurSuccess.value) {
    showCustomToast({ message: 'Bailleur supprimé avec succès', type: 'success' })
    fetchBailleurs()
  }
}

// Global Mount
onMounted(() => {
  fetchCategories()
  fetchBailleurs()
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
        title="Catégories & Bailleurs"
        description="Gérez les catégories et les bailleurs d'infrastructure"
        :tags="tagInfraNavPrealables"
        :active-tag-name="activeTagName"
      />

      <!-- Content Area with Grid -->
      <div class="grid lg:grid-cols-2 gap-7 flex-1">
        
        <!-- =======================
             LEFT COLUMN: CATEGORIES
             ======================= -->
        <BoxPanelWrapper>
          <span class="text-gray-500 my-1.5 text-xl font-medium">Catégories</span>
          
          <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between mb-4 gap-3">
             <div class="relative w-full max-w-xs">
              <Input
                v-model="queryCategory"
                placeholder="Rechercher une catégorie..."
                class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>
            <Button size="md" class="rounded-md" @click="openAddCategoryModal">
              <span class="flex iconify hugeicons--plus-sign mr-1"></span>
              Nouveau
            </Button>
          </div>

          <!-- Loading Categories -->
          <div v-if="loadingCategories" class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
            <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
            <span>Chargement...</span>
          </div>

          <!-- Table Categories -->
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
                <TableRow v-for="category in filteredCategories" :key="category.id">
                  <TableCell><Checkbox /></TableCell>
                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-medium">{{ category.name }}</span>
                      <span v-if="category.description" class="text-xs text-gray-500 truncate max-w-[200px]">{{ category.description }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                      <Button
                        size="icon"
                        variant="ghost"
                        class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                        @click="openEditCategoryModal(category)"
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
                            <DialogTitle>Supprimer la catégorie ?</DialogTitle>
                            <DialogDescription>
                              Supprimer "{{ category.name }}" est irréversible.
                            </DialogDescription>
                          </DialogHeader>
                          <DialogFooter>
                            <DialogClose as-child><Button variant="outline">Annuler</Button></DialogClose>
                            <Button variant="destructive" @click="handleDeleteCategory(category.id)" :disabled="deletingCategory">
                                <IconifySpinner v-if="deletingCategory" class="mr-2" />
                                <span v-if="deletingCategory">Suppression...</span>
                                <span v-else>Supprimer</span>
                            </Button>
                          </DialogFooter>
                        </DialogContent>
                      </Dialog>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="filteredCategories.length === 0">
                   <TableCell colspan="3" class="h-24 text-center text-gray-500">Aucune catégorie trouvée.</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </BoxPanelWrapper>


        <!-- =======================
             RIGHT COLUMN: BAILLEURS
             ======================= -->
        <BoxPanelWrapper>
          <span class="text-gray-500 my-1.5 text-xl font-medium">Bailleurs</span>
          
           <div class="flex sm:items-center flex-col sm:flex-row sm:justify-between mb-4 gap-3">
             <div class="relative w-full max-w-xs">
              <Input
                v-model="queryBailleur"
                placeholder="Rechercher un bailleur..."
                class="w-full ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
              />
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
                <span class="flex iconify hugeicons--search-01 text-sm"></span>
              </div>
            </div>
            <Button size="md" class="rounded-md" @click="openAddBailleurModal">
              <span class="flex iconify hugeicons--plus-sign mr-1"></span>
              Nouveau
            </Button>
          </div>

          <!-- Loading Bailleurs -->
          <div v-if="loadingBailleurs" class="flex gap-2 items-center justify-center py-10 bg-white h-full rounded-md text-gray-500">
            <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
            <span>Chargement...</span>
          </div>

          <!-- Table Bailleurs -->
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
                <TableRow v-for="bailleur in filteredBailleurs" :key="bailleur.id">
                  <TableCell><Checkbox /></TableCell>
                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-medium">{{ bailleur.name }}</span>
                      <span v-if="bailleur.description" class="text-xs text-gray-500 truncate max-w-[200px]">{{ bailleur.description }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                       <Button
                        size="icon"
                        variant="ghost"
                        class="size-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                        @click="openEditBailleurModal(bailleur)"
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
                            <DialogTitle>Supprimer le bailleur ?</DialogTitle>
                            <DialogDescription>
                              Supprimer "{{ bailleur.name }}" est irréversible.
                            </DialogDescription>
                          </DialogHeader>
                          <DialogFooter>
                             <DialogClose as-child><Button variant="outline">Annuler</Button></DialogClose>
                             <Button variant="destructive" @click="handleDeleteBailleur(bailleur.id)" :disabled="deletingBailleur">
                                <IconifySpinner v-if="deletingBailleur" class="mr-2" />
                                <span v-if="deletingBailleur">Suppression...</span>
                                <span v-else>Supprimer</span>
                             </Button>
                          </DialogFooter>
                        </DialogContent>
                      </Dialog>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="filteredBailleurs.length === 0">
                   <TableCell colspan="3" class="h-24 text-center text-gray-500">Aucun bailleur trouvé.</TableCell>
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

    <!-- Modal Form for Categories -->
    <Dialog :open="isCategoryDialogOpen" @update:open="isCategoryDialogOpen = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{{ isCategoryEditing ? 'Modifier Catégorie' : 'Ajouter Catégorie' }}</DialogTitle>
          <DialogDescription>
            Remplissez les informations pour la catégorie d'infrastructure.
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="categoryName" class="text-right">Nom</Label>
            <Input id="categoryName" v-model="formCategoryData.name" class="col-span-3" placeholder="Ex: Bâtiments" />
          </div>
          <div class="grid grid-cols-4 items-start gap-4">
            <Label for="categoryDescription" class="text-right pt-2">Description</Label>
            <Textarea id="categoryDescription" v-model="formCategoryData.description" class="col-span-3" placeholder="Description..." rows="3" />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="isCategoryDialogOpen = false">Annuler</Button>
          <Button type="submit" @click="handleSaveCategory" :disabled="creatingCategory || updatingCategory">
            <IconifySpinner v-if="creatingCategory || updatingCategory" class="mr-2" />
            <span v-if="creatingCategory || updatingCategory">Enregistrement...</span>
            <span v-else>Enregistrer</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Modal Form for Bailleurs -->
    <Dialog :open="isBailleurDialogOpen" @update:open="isBailleurDialogOpen = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{{ isBailleurEditing ? 'Modifier Bailleur' : 'Ajouter Bailleur' }}</DialogTitle>
          <DialogDescription>
            Remplissez les informations pour le bailleur d'infrastructure.
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="bailleurName" class="text-right">Nom</Label>
            <Input id="bailleurName" v-model="formBailleurData.name" class="col-span-3" placeholder="Ex: ENABEL" />
          </div>
          <div class="grid grid-cols-4 items-start gap-4">
            <Label for="bailleurDescription" class="text-right pt-2">Description</Label>
            <Textarea id="bailleurDescription" v-model="formBailleurData.description" class="col-span-3" placeholder="Description..." rows="3" />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="isBailleurDialogOpen = false">Annuler</Button>
          <Button type="submit" @click="handleSaveBailleur" :disabled="creatingBailleur || updatingBailleur">
            <IconifySpinner v-if="creatingBailleur || updatingBailleur" class="mr-2" />
            <span v-if="creatingBailleur || updatingBailleur">Enregistrement...</span>
            <span v-else>Enregistrer</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </DashLayout>
</template>
