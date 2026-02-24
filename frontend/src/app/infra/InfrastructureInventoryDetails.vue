<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import DashFormLayout from '@/components/templates/DashFormLayout.vue'
import FormSection from '@/components/atoms/FormSection.vue'
import InputWrapper from '@/components/atoms/InputWrapper.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
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
const inventoryId = route.params.id

// Fetch Infrastructure Inventory Details
const { data: inventory, loading, fetchData } = useGetApi(
  API_ROUTES.GET_INFRA_INFRASTRUCTURE_INVENTAIRE(inventoryId as string)
)

onMounted(() => {
  fetchData()
})

const inventoryData = computed(() => {
  if (!inventory.value) return null
  // Handle nested data structure
  return (inventory.value as any).data || inventory.value
})

const formattedDate = computed(() => {
  return inventoryData.value?.inventory_date || '-'
})

const title = computed(() => {
  return inventoryData.value?.title || '-'
})

const description = computed(() => {
  return inventoryData.value?.description || '-'
})

const status = computed(() => {
  return inventoryData.value?.status || '-'
})

const observations = computed(() => {
  const obs = inventoryData.value?.observations
  return Array.isArray(obs) ? obs : []
})

const infrastructure = computed(() => {
  return inventoryData.value?.infrastructure || null
})

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'excellent':
      return 'default'
    case 'bon':
      return 'secondary'
    case 'moyen':
      return 'outline'
    case 'mauvais':
      return 'destructive'
    case 'critique':
      return 'destructive'
    default:
      return 'outline'
  }
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
      { label: 'Inventaires Infrastructures', href: '/infra/prealables/infrastructures' },
      { label: 'Détails', isActive: true },
    ]"
  >
    <!-- Loading State -->
    <div v-if="loading" class="flex gap-2 items-center justify-center py-20">
      <span class="iconify animate-spin hugeicons--loading-03 text-2xl"></span>
      <span>Chargement...</span>
    </div>

    <div v-else class="w-full flex flex-col space-y-8">
      <!-- General Info Section -->
      <FormSection
        title="Informations Générales"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label>Titre</Label>
          <Input
            :model-value="title"
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
          <div class="mt-1">
            <Badge :variant="getStatusVariant(status)" class="capitalize">
              {{ status }}
            </Badge>
          </div>
        </InputWrapper>

        <InputWrapper>
          <Label>Description</Label>
          <Textarea
            :model-value="description"
            readonly
            class="bg-gray-50 text-gray-700 min-h-[60px] resize-none"
            rows="2"
          />
        </InputWrapper>
      </FormSection>

      <!-- Infrastructure Info -->
      <FormSection
        v-if="infrastructure"
        title="Infrastructure Concernée"
        class="grid sm:grid-cols-2 gap-y-6 gap-x-10"
      >
        <InputWrapper>
          <Label>Nom</Label>
          <Input
            :model-value="infrastructure.name || '-'"
            readonly
            class="bg-gray-50 text-gray-700"
          />
        </InputWrapper>

        <InputWrapper>
          <Label>Emplacement</Label>
          <Input
            :model-value="infrastructure.emplacement || '-'"
            readonly
            class="bg-gray-50 text-gray-700"
          />
        </InputWrapper>

        <InputWrapper>
          <Label>Date de construction</Label>
          <Input
            :model-value="infrastructure.date_construction || '-'"
            readonly
            class="bg-gray-50 text-gray-700"
          />
        </InputWrapper>

        <InputWrapper>
          <Label>Montant construction</Label>
          <Input
            :model-value="infrastructure.montant_construction ? `${infrastructure.montant_construction} FC` : '-'"
            readonly
            class="bg-gray-50 text-gray-700"
          />
        </InputWrapper>
      </FormSection>

      <!-- Observations Section -->
      <FormSection title="Observations">
        <div class="rounded-md border bg-white">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-12">#</TableHead>
                <TableHead>Observation</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(obs, index) in observations" :key="index">
                <TableCell class="text-gray-400 font-medium">{{ index + 1 }}</TableCell>
                <TableCell>{{ obs }}</TableCell>
              </TableRow>
              <TableRow v-if="observations.length === 0">
                <TableCell colspan="2" class="text-center h-24 text-gray-500">
                  Aucune observation
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </FormSection>
    </div>
  </DashFormLayout>
</template>
