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

// Fetch items for this inventory
const { data: rawItems, loading: loadingItems, fetchData: fetchItems } = useGetApi(
  API_ROUTES.GET_INFRA_EQUIPMENTS
)

// Fetch types and states for name mapping
const { data: rawTypes, fetchData: fetchTypes } = useGetApi(API_ROUTES.GET_INFRA_TYPES)
const { data: rawStates, fetchData: fetchStates } = useGetApi(API_ROUTES.GET_INFRA_STATES)

onMounted(async () => {
  await Promise.all([
    fetchItems(),
    fetchTypes(),
    fetchStates(),
  ])
})

const items = computed(() => {
  if (!rawItems.value) return []
  if (Array.isArray(rawItems.value)) return rawItems.value
  return (rawItems.value as any).data || []
})

const types = computed(() => {
  if (!rawTypes.value) return []
  if (Array.isArray(rawTypes.value)) return rawTypes.value
  return (rawTypes.value as any).data || []
})

const states = computed(() => {
  if (!rawStates.value) return []
  if (Array.isArray(rawStates.value)) return rawStates.value
  return (rawStates.value as any).data || []
})

const getTypeName = (typeId: number | string) => {
  const t = types.value.find((item: any) => item.id === Number(typeId))
  return t ? t.name : '-'
}

const getStateName = (stateId: number | string) => {
  const s = states.value.find((item: any) => item.id === Number(stateId))
  return s ? s.name : '-'
}

// Build inventory info from available data
const inventoryData = computed(() => {
  if (items.value.length > 0) {
    const firstItem = items.value[0]
    return {
      id: inventoryId,
      inventory_date: firstItem.created_at,
      note: 'Inventaire équipements',
    }
  }
  return null
})

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
                <TableHead>Nom</TableHead>
                <TableHead>N° Série</TableHead>
                <TableHead>Emplacement</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>État</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(item, index) in items" :key="item.id || index">
                <TableCell>{{ Number(index) + 1 }}</TableCell>
                <TableCell class="font-medium">{{ item.name || '-' }}</TableCell>
                <TableCell>{{ item.serial_number || '-' }}</TableCell>
                <TableCell>{{ item.location || '-' }}</TableCell>
                <TableCell>{{ getTypeName(item.type_id) }}</TableCell>
                <TableCell>
                  <span 
                    class="px-2 py-1 rounded-full text-xs font-medium"
                    :class="{
                      'bg-green-100 text-green-700': getStateName(item.state_id).toLowerCase().includes('bon'),
                      'bg-yellow-100 text-yellow-700': getStateName(item.state_id).toLowerCase().includes('moyen'),
                      'bg-red-100 text-red-700': getStateName(item.state_id).toLowerCase().includes('mauvais'),
                    }"
                  >
                    {{ getStateName(item.state_id) }}
                  </span>
                </TableCell>
              </TableRow>
              <TableRow v-if="items.length === 0">
                <TableCell colspan="6" class="text-center h-24 text-gray-500">
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
