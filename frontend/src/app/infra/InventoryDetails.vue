<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
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

// Fetch Infrastructure Inventory Details
const { data: inventory, loading, fetchData } = useGetApi(
  API_ROUTES.GET_INFRA_INFRASTRUCTURE_INVENTAIRE(inventoryId as string)
)

// Fetch items for this inventory
const { data: rawItems, loading: loadingItems, fetchData: fetchItems } = useGetApi(
  API_ROUTES.GET_INFRA_INFRASTRUCTURE_INVENTAIRE_ITEMS(inventoryId as string)
)

// Fetch equipments and states for name mapping
const { data: rawEquipments, fetchData: fetchEquipments } = useGetApi(API_ROUTES.GET_INFRA_EQUIPMENTS)
const { data: rawStates, fetchData: fetchStates } = useGetApi(API_ROUTES.GET_INFRA_STATES)

onMounted(() => {
  fetchData({ include: 'infrastructure' })
  fetchItems()
  fetchEquipments()
  fetchStates()
})

const inventoryData = computed(() => {
  if (!inventory.value) return null
  // Handle both {data: ...} wrapper and direct object
  return (inventory.value as any).data || inventory.value
})

const infrastructureName = computed(() => {
  return inventoryData.value?.infrastructure?.name || `ID: ${inventoryData.value?.infra_infrastructure_id}`
})

const formattedDate = computed(() => {
  const date = inventoryData.value?.inventory_date
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
})

const statusClass = computed(() => {
  const status = inventoryData.value?.status
  switch (status) {
    case 'excellent':
    case 'bon':
      return 'bg-green-100 text-green-700'
    case 'moyen':
      return 'bg-yellow-100 text-yellow-700'
    case 'mauvais':
    case 'critique':
      return 'bg-red-100 text-red-700'
    default:
      return 'bg-gray-100 text-gray-700'
  }
})

const goBack = () => {
  router.push('/infra/prealables/infrastructures')
}

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

const states = computed(() => {
  if (!rawStates.value) return []
  if (Array.isArray(rawStates.value)) return rawStates.value
  return (rawStates.value as any).data || []
})

const getEquipmentName = (id: number) => {
  const equip = equipments.value.find((e: any) => e.id === id)
  return equip ? equip.name : `Équipement #${id}`
}

const getStateName = (id: number | string) => {
  if (typeof id === 'string') {
    // If it's already a name/label
    return id
  }
  const state = states.value.find((s: any) => s.id === id)
  return state ? state.name : `État #${id}`
}
</script>

<template>
  <DashFormLayout
    :link-back="'/infra/prealables/infrastructures'"
    title="Détails de l'Inventaire Infrastructure"
    group-route="/infra/prealables"
    module="infra"
    :breadcrumb="[
      { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
      { label: 'Infrastructure', href: '/infra' },
      { label: 'Préalables', href: '/infra/prealables' },
      { label: 'Inventaires Infra', href: '/infra/prealables/infrastructures' },
      { label: 'Détails', isActive: true },
    ]"
  >
    <div v-if="loading" class="flex gap-2 items-center justify-center py-20">
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
          <Label>Titre</Label>
          <Input
            :model-value="inventoryData.title || '-'"
            readonly
            class="bg-gray-50 text-gray-700"
          />
        </InputWrapper>

        <InputWrapper>
          <Label>Infrastructure</Label>
          <Input
            :model-value="infrastructureName"
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

        <InputWrapper>
          <Label>Statut</Label>
          <div class="flex items-center h-10">
            <Badge :class="statusClass" class="uppercase">
              {{ inventoryData.status || '-' }}
            </Badge>
          </div>
        </InputWrapper>
      </FormSection>

      <!-- Description Section -->
      <FormSection title="Description">
        <InputWrapper>
          <Textarea
            :model-value="inventoryData.description || 'Aucune description'"
            readonly
            class="bg-gray-50 text-gray-700 min-h-[80px]"
            rows="3"
          />
        </InputWrapper>
      </FormSection>

      <!-- Observations Section -->
      <FormSection title="Observations">
        <div v-if="inventoryData.observations && inventoryData.observations.length > 0" class="space-y-2">
          <div
            v-for="(obs, index) in inventoryData.observations"
            :key="index"
            class="p-3 bg-gray-50 rounded-md border border-gray-200"
          >
            <span class="text-sm text-gray-700">{{ obs }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4 text-gray-500">
          Aucune observation pour le moment
        </div>
      </FormSection>

      <!-- Items Section -->
      <FormSection title="Éléments Inventoriés">
        <div v-if="loadingItems" class="flex gap-2 items-center justify-center py-10 text-gray-500">
          <span class="iconify animate-spin hugeicons--loading-03 text-xl"></span>
          <span>Chargement des éléments...</span>
        </div>
        <div v-else-if="items.length > 0" class="rounded-md border bg-white">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[60px]">N°</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Nom/Label</TableHead>
                <TableHead>État</TableHead>
                <TableHead>Description</TableHead>
                <TableHead class="text-right">Quantité</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in items" :key="item.id || index">
                <TableCell>{{ Number(index) + 1 }}</TableCell>
                <TableCell>
                  <Badge variant="outline" class="capitalize">{{ item.type || 'N/A' }}</Badge>
                </TableCell>
                <TableCell class="font-medium">
                  <template v-if="item.type === 'equipment' && item.equipment_id">
                    {{ getEquipmentName(item.equipment_id) }}
                  </template>
                  <template v-else>
                    {{ item.label || item.equipment_name || '-' }}
                  </template>
                </TableCell>
                <TableCell>
                  <span class="text-sm">{{ getStateName(item.etat || item.state_id) }}</span>
                </TableCell>
                <TableCell class="text-sm text-gray-600">
                  {{ item.description || '-' }}
                  <span v-if="item.emplacement" class="block text-xs text-gray-500 mt-1">
                    📍 {{ item.emplacement }}
                  </span>
                </TableCell>
                <TableCell class="text-right">
                  {{ item.quantite || item.quantity || '-' }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        <div v-else class="text-center py-10 text-gray-500 bg-gray-50 rounded-md border border-dashed">
          <span class="iconify hugeicons--package text-4xl mb-2"></span>
          <p>Aucun élément dans cet inventaire</p>
          <p class="text-sm mt-1">Les éléments ajoutés apparaîtront ici</p>
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
