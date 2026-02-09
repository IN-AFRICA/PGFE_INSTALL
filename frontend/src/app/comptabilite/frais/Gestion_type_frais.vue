<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useForm, Field } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'

// Imports des composants UI
import ComptaLayout from '@/components/templates/compta/ComptaLayout.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
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
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

// Imports des composables API
import { useGetApi } from '@/composables/useGetApi.ts'
import { usePostApi } from '@/composables/usePostApi.ts'
import { usePutApi } from '@/composables/usePutApi.ts'
import { useDeleteApi } from '@/composables/useDeleteApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { showCustomToast } from '@/utils/widgets/custom_toast.ts'

// Interface pour les types de frais
interface FeeType {
  id: number
  name: string
  code: string
  description?: string
  created_at?: string
  updated_at?: string
}

// Interface pour la réponse API
interface FeeTypesApiResponse {
  success: boolean
  message: string
  fee_types: FeeType[]
}

// Schéma de validation Zod
const feeTypeSchema = z.object({
  name: z
    .string()
    .min(1, 'Le nom est requis')
    .max(100, 'Le nom ne peut pas dépasser 100 caractères'),
  code: z
    .string()
    .min(1, 'Le code est requis')
    .max(20, 'Le code ne peut pas dépasser 20 caractères'),
  description: z.string().optional(),
})

type FeeTypeFormData = z.infer<typeof feeTypeSchema>

// Variables réactives
const searchQuery = ref('')
const selectedFeeTypes = ref<number[]>([])
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingItem = ref<FeeType | null>(null)

// APIs
const {
  data: feeTypesData,
  loading,
  error,
  fetchData,
} = useGetApi<FeeTypesApiResponse>(API_ROUTES.GET_FEE_TYPES)
const { postData: createFeeType, loading: createLoading } = usePostApi()
const { putData: updateFeeType, loading: updateLoading } = usePutApi()
const { deleteItem: deleteFeeType, deleting: deleteLoading, errorDelete } = useDeleteApi()

// Formulaire pour création/modification
const { handleSubmit, resetForm, setValues } = useForm({
  validationSchema: toTypedSchema(feeTypeSchema),
})

// Computed pour filtrer les types de frais
const filteredFeeTypes = computed(() => {
  if (!feeTypesData.value?.fee_types) return []

  if (!searchQuery.value) {
    return feeTypesData.value.fee_types
  }

  const query = searchQuery.value.toLowerCase()
  return feeTypesData.value.fee_types.filter(
    (feeType) =>
      feeType.name.toLowerCase().includes(query) ||
      (feeType.description && feeType.description.toLowerCase().includes(query)),
  )
})

// Fonction pour formater la date
const formatDate = (dateString?: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR')
}

// Soumission du formulaire de création
const onSubmitCreate = handleSubmit(async (values) => {
  try {
    await createFeeType(API_ROUTES.CREATE_FEE_TYPE, values)

    showCustomToast({
      message: 'Type de frais créé avec succès',
      type: 'success',
    })

    resetForm()
    isCreateDialogOpen.value = false
    await fetchData()
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la création du type de frais',
      type: 'error',
    })
  }
})

// Soumission du formulaire de modification
const onSubmitEdit = handleSubmit(async (values) => {
  if (!editingItem.value) return

  try {
    await updateFeeType(API_ROUTES.UPDATE_FEE_TYPE(editingItem.value.id), values)

    showCustomToast({
      message: 'Type de frais modifié avec succès',
      type: 'success',
    })

    resetForm()
    isEditDialogOpen.value = false
    editingItem.value = null
    await fetchData()
  } catch (err) {
    showCustomToast({
      message: 'Erreur lors de la modification du type de frais',
      type: 'error',
    })
  }
})

// Fonction pour ouvrir le dialog de modification
const openEditDialog = (feeType: FeeType) => {
  editingItem.value = feeType
  setValues({
    name: feeType.name,
    code: feeType.code,
    description: feeType.description || '',
  })
  isEditDialogOpen.value = true
}

// Fonction pour supprimer un type de frais
const handleDelete = async (feeTypeId: number) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce type de frais ?')) {
    return
  }

  try {
    console.log('Suppression du type de frais ID:', feeTypeId)
    console.log('URL de suppression:', API_ROUTES.DELETE_FEE_TYPE(feeTypeId))

    const result = await deleteFeeType(API_ROUTES.DELETE_FEE_TYPE(feeTypeId))

    console.log('Résultat de suppression:', result)
    console.log('Erreur de suppression:', errorDelete.value)

    if (errorDelete.value) {
      showCustomToast({
        message: errorDelete.value,
        type: 'error',
      })
      return
    }

    showCustomToast({
      message: 'Type de frais supprimé avec succès',
      type: 'success',
    })

    await fetchData()
  } catch (err: any) {
    console.error('Erreur lors de la suppression:', err)
    showCustomToast({
      message: err?.message || 'Erreur lors de la suppression du type de frais',
      type: 'error',
    })
  }
}

// Fonction pour gérer la sélection multiple
const toggleSelection = (feeTypeId: number) => {
  if (selectedFeeTypes.value.includes(feeTypeId)) {
    selectedFeeTypes.value = selectedFeeTypes.value.filter((id) => id !== feeTypeId)
  } else {
    selectedFeeTypes.value.push(feeTypeId)
  }
}

// Fonction pour sélectionner/désélectionner tout
const toggleSelectAll = () => {
  if (
    selectedFeeTypes.value.length === filteredFeeTypes.value.length &&
    filteredFeeTypes.value.length > 0
  ) {
    selectedFeeTypes.value = []
  } else {
    selectedFeeTypes.value = filteredFeeTypes.value.map((feeType) => feeType.id)
  }
}

// Fonction pour supprimer les types de frais sélectionnés
const deleteSelectedFeeTypes = async () => {
  if (selectedFeeTypes.value.length === 0) {
    showCustomToast({
      message: 'Aucun type de frais sélectionné',
      type: 'warning',
    })
    return
  }

  if (
    !confirm(
      `Êtes-vous sûr de vouloir supprimer ${selectedFeeTypes.value.length} type(s) de frais ?`,
    )
  ) {
    return
  }

  console.log('Suppression multiple de types de frais:', selectedFeeTypes.value)

  let successCount = 0
  let errorCount = 0
  const errors: string[] = []

  try {
    for (const feeTypeId of selectedFeeTypes.value) {
      console.log('Suppression du type de frais ID:', feeTypeId)
      console.log('URL:', API_ROUTES.DELETE_FEE_TYPE(feeTypeId))

      const result = await deleteFeeType(API_ROUTES.DELETE_FEE_TYPE(feeTypeId))

      console.log('Résultat:', result)
      console.log('Erreur:', errorDelete.value)

      if (errorDelete.value) {
        errorCount++
        errors.push(`Type #${feeTypeId}: ${errorDelete.value}`)
      } else {
        successCount++
      }
    }

    if (errorCount > 0) {
      console.error('Erreurs de suppression:', errors)
      showCustomToast({
        message: `${successCount} supprimé(s), ${errorCount} échec(s). Voir la console pour les détails.`,
        type: 'warning',
      })
    } else {
      showCustomToast({
        message: `${successCount} type(s) de frais supprimé(s) avec succès`,
        type: 'success',
      })
    }

    selectedFeeTypes.value = []
    await fetchData()
  } catch (err: any) {
    console.error('Erreur lors de la suppression des types de frais:', err)
    showCustomToast({
      message: err?.message || 'Erreur lors de la suppression des types de frais',
      type: 'error',
    })
  }
}

// Charger les données au montage
onMounted(() => {
  fetchData()
})
</script>

<template>
  <ComptaLayout activeBread="Types de Frais" active-tag-name="types-frais" group="frais">
    <BoxPanelWrapper>
      <!-- Barre d'actions -->
      <div class="flex items-center gap-3 justify-between mb-4">
        <div class="relative flex-1 max-w-md">
          <Input
            type="text"
            id="search"
            name="search"
            placeholder="Rechercher par libellé ou description..."
            v-model="searchQuery"
            class="pl-10"
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70">
            <span class="flex iconify hugeicons--search-01 text-sm"></span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Actions groupées -->
          <Button
            v-if="selectedFeeTypes.length > 0"
            variant="destructive"
            size="sm"
            @click="deleteSelectedFeeTypes"
            :disabled="deleteLoading"
          >
            <span class="iconify hugeicons--delete-02 mr-2"></span>
            Supprimer ({{ selectedFeeTypes.length }})
          </Button>

          <!-- Bouton d'export -->
          <!-- <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" size="sm">
                  <span class="iconify hugeicons--download-01 mr-2"></span>
                  Exporter
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent>
                <DropdownMenuItem>
                  <span class="iconify hugeicons--file-02 mr-2"></span>
                  Excel
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <span class="iconify hugeicons--file-02 mr-2"></span>
                  PDF
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu> -->

          <!-- Dialog de création -->
          <Dialog v-model:open="isCreateDialogOpen">
            <DialogTrigger as-child>
              <Button size="md" class="bg-primary text-primary-foreground">
                <span class="iconify hugeicons--add-01 mr-2"></span>
                <span class="hidden sm:flex">Nouveau Type</span>
              </Button>
            </DialogTrigger>
            <DialogContent class="sm:max-w-[500px]">
              <DialogHeader>
                <DialogTitle>Créer un nouveau type de frais</DialogTitle>
                <DialogDescription>
                  Remplissez les informations du nouveau type de frais.
                </DialogDescription>
              </DialogHeader>
              <form @submit="onSubmitCreate" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label for="name">Nom *</Label>
                    <Field name="name" v-slot="{ field, errorMessage }">
                      <Input
                        id="name"
                        v-bind="field"
                        placeholder="Frais de scolarité"
                        :class="{ 'border-red-500': errorMessage }"
                      />
                      <p v-if="errorMessage" class="text-sm text-red-500 mt-1">
                        {{ errorMessage }}
                      </p>
                    </Field>
                  </div>

                  <div class="space-y-2">
                    <Label for="code">Code *</Label>
                    <Field name="code" v-slot="{ field, errorMessage }">
                      <Input
                        id="code"
                        v-bind="field"
                        placeholder="FS001"
                        class="font-mono"
                        :class="{ 'border-red-500': errorMessage }"
                      />
                      <p v-if="errorMessage" class="text-sm text-red-500 mt-1">
                        {{ errorMessage }}
                      </p>
                    </Field>
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="description">Description</Label>
                  <Field name="description" v-slot="{ field, errorMessage }">
                    <Textarea
                      id="description"
                      v-bind="field"
                      placeholder="Description du type de frais (optionnel)"
                      rows="3"
                      :class="{ 'border-red-500': errorMessage }"
                    />
                    <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
                  </Field>
                </div>

                <DialogFooter>
                  <Button type="button" variant="outline" @click="isCreateDialogOpen = false">
                    Annuler
                  </Button>
                  <Button type="submit" :disabled="createLoading">
                    <span
                      v-if="createLoading"
                      class="iconify hugeicons--loading-03 animate-spin mr-2"
                    ></span>
                    Créer
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>
      </div>

      <!-- Tableau des types de frais -->
      <div class="border rounded-lg overflow-hidden flex flex-1 bg-white">
        <Table>
          <TableHeader>
            <TableRow class="bg-white">
              <TableHead class="w-[40px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="
                    selectedFeeTypes.length === filteredFeeTypes.length &&
                    filteredFeeTypes.length > 0
                  "
                  @click="toggleSelectAll"
                />
              </TableHead>
              <TableHead>Nom</TableHead>
              <TableHead>Code</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>Date de création</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <!-- État de chargement -->
            <TableRow v-if="loading">
              <TableCell :colspan="6" class="text-center py-8">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--loading-03 animate-spin"></span>
                  Chargement des types de frais...
                </div>
              </TableCell>
            </TableRow>

            <!-- État d'erreur -->
            <TableRow v-else-if="error">
              <TableCell :colspan="6" class="text-center py-8 text-red-500">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--alert-circle"></span>
                  Erreur lors du chargement : {{ error }}
                </div>
              </TableCell>
            </TableRow>

            <!-- Liste vide -->
            <TableRow v-else-if="filteredFeeTypes.length === 0">
              <TableCell :colspan="6" class="text-center py-8 text-gray-500">
                <div class="flex items-center justify-center gap-2">
                  <span class="iconify hugeicons--tag-01"></span>
                  {{
                    searchQuery
                      ? 'Aucun type de frais trouvé pour cette recherche'
                      : 'Aucun type de frais configuré'
                  }}
                </div>
              </TableCell>
            </TableRow>

            <!-- Données -->
            <TableRow v-else v-for="feeType in filteredFeeTypes" :key="feeType.id">
              <TableCell class="w-[40px]">
                <Checkbox
                  class="bg-white scale-70"
                  :checked="selectedFeeTypes.includes(feeType.id)"
                  @click="toggleSelection(feeType.id)"
                />
              </TableCell>
              <TableCell class="font-medium">{{ feeType.name }}</TableCell>
              <TableCell>
                <span class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{
                  feeType.code
                }}</span>
              </TableCell>
              <TableCell>
                <span class="text-sm text-gray-600">{{ feeType.description || '-' }}</span>
              </TableCell>
              <TableCell>{{ formatDate(feeType.created_at) }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 w-max">
                  <Button
                    variant="outline"
                    size="icon"
                    class="size-8"
                    @click="openEditDialog(feeType)"
                    title="Modifier le type de frais"
                  >
                    <span class="iconify hugeicons--edit-02"></span>
                  </Button>
                  <Button
                    variant="destructive"
                    size="icon"
                    class="size-8"
                    @click="handleDelete(feeType.id)"
                    title="Supprimer le type de frais"
                    :disabled="deleteLoading"
                  >
                    <span class="iconify hugeicons--delete-02"></span>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </BoxPanelWrapper>

    <!-- Dialog de modification -->
    <Dialog v-model:open="isEditDialogOpen">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Modifier le type de frais</DialogTitle>
          <DialogDescription> Modifiez les informations du type de frais. </DialogDescription>
        </DialogHeader>
        <form @submit="onSubmitEdit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit_name">Nom *</Label>
              <Field name="name" v-slot="{ field, errorMessage }">
                <Input
                  id="edit_name"
                  v-bind="field"
                  placeholder="Frais de scolarité"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>

            <div class="space-y-2">
              <Label for="edit_code">Code *</Label>
              <Field name="code" v-slot="{ field, errorMessage }">
                <Input
                  id="edit_code"
                  v-bind="field"
                  placeholder="FS001"
                  class="font-mono"
                  :class="{ 'border-red-500': errorMessage }"
                />
                <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
              </Field>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="edit_description">Description</Label>
            <Field name="description" v-slot="{ field, errorMessage }">
              <Textarea
                id="edit_description"
                v-bind="field"
                placeholder="Description du type de frais (optionnel)"
                rows="3"
                :class="{ 'border-red-500': errorMessage }"
              />
              <p v-if="errorMessage" class="text-sm text-red-500 mt-1">{{ errorMessage }}</p>
            </Field>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="isEditDialogOpen = false">
              Annuler
            </Button>
            <Button type="submit" :disabled="updateLoading">
              <span
                v-if="updateLoading"
                class="iconify hugeicons--loading-03 animate-spin mr-2"
              ></span>
              Modifier
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </ComptaLayout>
</template>
