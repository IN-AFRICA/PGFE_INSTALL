<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
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
const inventoryId = route.params.id

// Fetch Inventory Details
const { data: inventory, loading, fetchData } = useGetApi(API_ROUTES.GET_INFRA_INVENTORY(inventoryId as string))

onMounted(() => {
  fetchData({ include: 'equipments,real_states' })
})

const items = computed(() => {
  if (!inventory.value) return []
  return (inventory.value as any).equipments || []
})

const states = computed(() => {
    if (!inventory.value) return []
    return (inventory.value as any).real_states || []
})

const formattedDate = computed(() => {
    return (inventory.value as any)?.inventory_date || '-'
})

const note = computed(() => {
    return (inventory.value as any)?.note || '-'
})
</script>

<template>
  <DashFormLayout
    :link-back="'/infra/prealables'"
    title="Détails de l'Inventaire"
    group-route="/infra/prealables"
    module="infra"
    :breadcrumb="[
      { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
      { label: 'Infrastructure', href: '/infra' },
      { label: 'Préalables', href: '/infra/prealables' },
      { label: 'Détails Inventaire', isActive: true },
    ]"
  >
    <div class="w-full flex flex-col space-y-8">
      
      <!-- General Info Section -->
      <FormSection
        title="Informations Générales"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label>Date de l'inventaire</Label>
          <Input
            :model-value="formattedDate"
            readonly
            class="bg-gray-50 text-gray-700"
          />
        </InputWrapper>

        <InputWrapper>
          <Label>Note / Description</Label>
          <Textarea
            :model-value="note"
            readonly
            class="bg-gray-50 text-gray-700 min-h-[40px] resize-none"
            rows="1"
          />
        </InputWrapper>
      </FormSection>

      <!-- Details Sections -->
      <div v-if="loading" class="flex gap-2 items-center justify-center py-10">
          <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
          <span>Chargement...</span>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Equipments Table -->
          <FormSection title="Équipements Relevés">
             <div class="rounded-md border bg-white">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Équipement</TableHead>
                            <TableHead class="text-right">Quantité</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="item in items" :key="item.id">
                            <TableCell class="font-medium">{{ item.equipment?.name || item.equipment_id }}</TableCell>
                            <TableCell class="text-right">{{ item.quantity }}</TableCell>
                        </TableRow>
                         <TableRow v-if="items.length === 0">
                            <TableCell colspan="2" class="text-center h-24 text-gray-500">Aucun équipement</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
          </FormSection>

          <!-- States Table -->
          <FormSection title="États Constatés">
             <div class="rounded-md border bg-white">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>État</TableHead>
                            <TableHead>Note</TableHead>
                        </TableRow>
                    </TableHeader>
                     <TableBody>
                        <TableRow v-for="state in states" :key="state.id">
                            <TableCell class="font-medium">{{ state.state?.name || state.state_id }}</TableCell>
                            <TableCell>{{ state.note || '-' }}</TableCell>
                        </TableRow>
                        <TableRow v-if="states.length === 0">
                            <TableCell colspan="2" class="text-center h-24 text-gray-500">Aucun état</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
          </FormSection>
      </div>
    
    </div>
  </DashFormLayout>
</template>
