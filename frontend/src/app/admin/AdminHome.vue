<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import DashPageHeader from '@/components/templates/DashPageHeader.vue'

const route = useRoute()

const addminGeotags = [
  { name: 'pays', text: 'Pays', href: 'admin/countries' },
  { name: 'provinces', text: 'Provinces', href: 'admin/provinces' },
  { name: 'territoires', text: 'Territoires', href: 'admin/territoires' },
  { name: 'communes', text: 'Communes', href: 'admin/communes' },
]

const activeTagName = computed(() => {
  const parts = route.path.split('/').filter(Boolean)
  const last = parts[parts.length - 1]
  if (!last || last === 'admin') return 'pays'
  if (last === 'countries') return 'pays'
  const names = addminGeotags.map((tag) => tag.name)
  return names.includes(last) ? last : 'pays'
})

import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import LayoutSaisieOperation from '@/components/templates/LayoutSaisieOperation.vue'
import BoxPanelWrapper from '@/components/atoms/BoxPanelWrapper.vue'
import { Button } from '@/components/ui/button'

import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import NewCountry from '@/components/modals/admin/location/NewCountry.vue'
import NewProvinces from '@/components/modals/admin/location/NewProvinces.vue'
import NewTerritories from '@/components/modals/admin/location/NewTerritories.vue'
// import NewCommunes from '@/components/modals/admin/NewCommunes.vue'
import DashLayout from '@/components/templates/DashLayout.vue'

const breadcrumbItems = {
  items: [
    {
      label: 'Accueil',
      href: '/',
      icon: 'hugeicons--home-01',
    },
    {
      label: 'Administration',
      href: '/admin',
    },
  ],
}

const query = ref('')

const selectedCountryId = ref<number | undefined>()
const selectedProvinceId = ref<number | undefined>()
const selectedTerritoryId = ref<number | undefined>()
const selectedCommuneId = ref<number | undefined>()

const {
  data: countries,
  loading: loadingCountries,
  error: errorCountries,
  fetchData: fetchCountries,
} = useGetApi<any[]>(API_ROUTES.GET_COUNTRIES)

const {
  data: provinces,
  loading: loadingProvinces,
  error: errorProvinces,
  fetchData: fetchProvinces,
} = useGetApi<any[]>(API_ROUTES.GET_PROVINCES)

const {
  data: territories,
  loading: loadingTerritories,
  error: errorTerritories,
  fetchData: fetchTerritories,
} = useGetApi<any[]>(API_ROUTES.GET_TERRITORIES)

const {
  data: communes,
  loading: loadingCommunes,
  error: errorCommunes,
  fetchData: fetchCommunes,
} = useGetApi<any[]>(API_ROUTES.GET_COMMUNES)

onMounted(() => {
  fetchCountries()
})

// Pays → Provinces
watch(selectedCountryId, async (id) => {
  selectedProvinceId.value = undefined
  selectedTerritoryId.value = undefined
  selectedCommuneId.value = undefined
  if (id) await fetchProvinces({ country_id: id })
})

// Provinces → Territoires
watch(selectedProvinceId, async (id) => {
  selectedTerritoryId.value = undefined
  selectedCommuneId.value = undefined
  if (id) await fetchTerritories({ province_id: id })
})

// Territoires → Communes
watch(selectedTerritoryId, async (id) => {
  selectedCommuneId.value = undefined
  if (id) await fetchCommunes({ territory_id: id })
})
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/admin" module-name="admin">
    <div class="pb-6 mx-auto w-full max-w-7xl">
      <BoxPanelWrapper>
        <!-- Header: search + actions -->
        <div class="flex items-center gap-3 justify-between">
          <div class="relative flex-1">
            <Input
              v-model="query"
              type="text"
              id="search"
              name="search"
              placeholder="Rechercher..."
              class="w-full max-w-sm ps-10 border border-gray-200/40 bg-white transition-all h-10 rounded-md"
            />
            <div
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-foreground-muted/70"
            >
              <span class="flex iconify hugeicons--search-01 text-sm"></span>
            </div>
          </div>
          <div class="flex flex-wrap items-center gap-2.5">
            <!-- Accès rapide aux formulaires d'ajout -->
            <NewCountry />
            <NewProvinces />
            <NewTerritories />
            <!-- <NewCommunes /> -->
          </div>
        </div>

        <!-- Filtres dépendants -->
        <!--       <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="bg-white rounded-md p-3">
          <label class="block text-sm font-medium mb-2">Pays</label>
          <select
            v-model="selectedCountryId"
            class="w-full border rounded-md h-10 px-3"
            :disabled="loadingCountries"
          >
            <option :value="undefined" disabled>-- Sélectionnez un pays --</option>
            <option v-for="c in countries || []" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <p v-if="errorCountries" class="text-sm text-red-600 mt-1">{{ errorCountries }}</p>
        </div>

        <div class="bg-white rounded-md p-3">
          <label class="block text-sm font-medium mb-2">Province</label>
          <select
            v-model="selectedProvinceId"
            class="w-full border rounded-md h-10 px-3"
            :disabled="!selectedCountryId || loadingProvinces"
          >
            <option :value="undefined" disabled>-- Sélectionnez une province --</option>
            <option v-for="p in provinces || []" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <p v-if="errorProvinces" class="text-sm text-red-600 mt-1">{{ errorProvinces }}</p>
        </div>

        <div class="bg-white rounded-md p-3">
          <label class="block text-sm font-medium mb-2">Territoire</label>
          <select
            v-model="selectedTerritoryId"
            class="w-full border rounded-md h-10 px-3"
            :disabled="!selectedProvinceId || loadingTerritories"
          >
            <option :value="undefined" disabled>-- Sélectionnez un territoire --</option>
            <option v-for="t in territories || []" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
          <p v-if="errorTerritories" class="text-sm text-red-600 mt-1">{{ errorTerritories }}</p>
        </div>

        <div class="bg-white rounded-md p-3">
          <label class="block text-sm font-medium mb-2">Commune</label>
          <select
            v-model="selectedCommuneId"
            class="w-full border rounded-md h-10 px-3"
            :disabled="!selectedTerritoryId || loadingCommunes"
          >
            <option :value="undefined" disabled>-- Sélectionnez une commune --</option>
            <option v-for="m in communes || []" :key="m.id" :value="m.id">{{ m.name }}</option>
          </select>
          <p v-if="errorCommunes" class="text-sm text-red-600 mt-1">{{ errorCommunes }}</p>
        </div>
      </div>
 -->
      </BoxPanelWrapper>
    </div>
  </DashLayout>
</template>

<style scoped></style>
