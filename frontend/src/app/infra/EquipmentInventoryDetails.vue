<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'

const route = useRoute()
const router = useRouter()
const inventoryId = route.params.id

// Fetch Equipment Inventory Details
// TEMPORAIRE: Commenté à cause de l'erreur backend "categorie" relation
// const { data: inventory, loading, fetchData } = useGetApi(
//   API_ROUTES.GET_INFRA_INVENTORY(inventoryId as string)
// )

// Fetch items for this inventory
const { data: rawItems, loading: loadingItems, fetchData: fetchItems } = useGetApi(
  API_ROUTES.GET_INVENTORY_ITEMS(inventoryId as string)
)

// Fetch equipments for name mapping
const { data: rawEquipments, fetchData: fetchEquipments } = useGetApi(API_ROUTES.GET_INFRA_EQUIPMENTS)

onMounted(async () => {
  await Promise.all([
    // fetchData(), // Commenté temporairement
    fetchItems(),
    fetchEquipments()
  ])
})

// Temporaire: Utiliser les données des items au lieu de l'inventory principal
const inventoryData = computed(() => {
  // Si on a des items, on peut extraire les infos de base depuis le premier item
  if (items.value.length > 0) {
    const firstItem = items.value[0]
    return {
      id: inventoryId,
      inventory_date: firstItem.created_at,
      note: 'Inventaire équipements',
      school: firstItem.school,
      user: firstItem.user
    }
  }
  return null
})

const items = computed(() => {
  if (!rawItems.value) return []
  if (Array.isArray(rawItems.value)) return rawItems.value
  return (rawItems.value as any).data || []
})

const equipments = computed(() => {
  if (!rawEquipments.value) return []
  if (Array.isArray(rawEquipments.value)) return rawEquipments.value
  return (rawEquipments.value as any).data || []
})

const getEquipmentName = (equipmentId: number) => {
  const eq = equipments.value.find((e: any) => e.id === equipmentId)
  return eq ? eq.name : `Équipement #${equipmentId}`
}

const formattedDate = computed(() => {
  const date = inventoryData.value?.inventory_date
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
})

const goBack = () => {
  router.push('/infra/prealables/inventaires')
}
</script>

<template>
  <DashFormLayout
    :link-back="'/infra/prealables/inventaires'"
    title="Détails de l'Inventaire Équipements"
    group-route="/infra/prealables"
    module="infra"
    :breadcrumb="[
      { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
      { label: 'Infrastructure', href: '/infra' },
      { label: 'Préalables', href: '/infra/prealables' },
      { label: 'Inventaires Équipements', href: '/infra/prealables/inventaires' },
      { label: 'Détails', isActive: true },
    ]"
  >
    <div v-if="loadingItems" class="flex gap-2 items-center justify-center py-20">
      <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
      <span>Chargement...</span>
    </div>

    <div v-else-if="inventoryData" class="w-full flex flex-col space-y-8">
      
      <!-- General Info Section -->
      <FormSection
        title="Informations Générales"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label>ID Inventaire</Label>
          <Input
            :model-value="inventoryData.id?.toString() || '-'"
            readonly
            class="bg-gray-50 text-gray-700"
          />
        </InputWrapper>

        <InputWrapper>
          <Label>Date de l'inventaire</Label>
          <Input
            :model-value="formattedDate"
            readonly
            class="bg-gray-50 text-gray-700"
          />
        </InputWrapper>

        <InputWrapper class="sm:col-span-2">
          <Label>Note / Description</Label>
          <Input
            :model-value="inventoryData.note || 'Aucune note'"
            readonly
            class="bg-gray-50 text-gray-700"
          />
        </InputWrapper>
      </FormSection>

      <!-- Equipments Table -->
      <FormSection title="Équipements Inventoriés">
        <div v-if="loadingItems" class="flex gap-2 items-center justify-center py-10 text-gray-500">
          <span class="iconify animate-spin hugeicons--loading-03 text-xl"></span>
          <span>Chargement des éléments...</span>
        </div>
        <div v-else class="rounded-md border bg-white">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[60px]">N°</TableHead>
                <TableHead>Équipement</TableHead>
                <TableHead class="text-right">Quantité</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in items" :key="item.id || index">
                <TableCell>{{ Number(index) + 1 }}</TableCell>
                <TableCell class="font-medium">{{ getEquipmentName(item.equipment_id) }}</TableCell>
                <TableCell class="text-right">{{ item.quantity }}</TableCell>
              </TableRow>
              <TableRow v-if="items.length === 0">
                <TableCell colspan="3" class="text-center h-24 text-gray-500">
                  Aucun équipement ajouté à cet inventaire
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </FormSection>

      <!-- Actions -->
      <div class="flex justify-end gap-3 pt-4">
        <Button variant="outline" @click="goBack">
          <span class="iconify hugeicons--arrow-left mr-2"></span>
          Retour à la liste
        </Button>
      </div>
    </div>

    <div v-else class="flex flex-col items-center justify-center py-20 text-gray-500">
      <span class="iconify hugeicons--alert-circle text-4xl mb-4"></span>
      <p>Inventaire non trouvé</p>
      <Button variant="outline" class="mt-4" @click="goBack">
        Retour à la liste
      </Button>
    </div>
  </DashFormLayout>
</template>
