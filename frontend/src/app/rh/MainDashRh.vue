<script setup lang="ts">
import PageAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import type { BreadcrumbProps } from '@/types'
import { Button } from '@/components/ui/button'
import { BarChart } from '@/components/ui/chart-bar'
import { Label } from '@/components/ui/label'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { computed, onMounted, ref, watch } from 'vue'

const breadcrumbItems: BreadcrumbProps = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'GRH', isActive: true },
  ],
}

interface PersonnelStats {
  year: number
  month: number
  gender: 'Masculin' | 'Feminin'
  total_personnel: number
}

const currentYear = new Date().getFullYear()
const filters = ref<{ year?: number }>({ year: currentYear })

const {
  data: stats,
  loading,
  error,
  fetchData,
  refresh: refreshStats,
} = useGetApi<PersonnelStats[]>(API_ROUTES.GET_STATS_MONTH)

const {
  data: schoolYears,
  fetchData: fetchSchoolYears,
  refresh: refreshSchoolYears,
} = useGetApi(API_ROUTES.GET_SCHOOL_YEARS)

const refreshAll = async () => {
  await Promise.all([refreshStats(), refreshSchoolYears()])
}

const schoolYearsList = computed(() => {
  const v: any = schoolYears.value
  if (Array.isArray(v)) return v
  return v?.years ?? v?.data ?? []
})

const yearOptions = computed(() => {
  const parseYear = (value: any) => {
    if (value === undefined || value === null) return undefined
    if (typeof value === 'number') return value
    const match = String(value).match(/\d{4}/)
    return match ? Number(match[0]) : undefined
  }

  const fromSchoolYears = schoolYearsList.value
    .map((year: any) => parseYear(year.year ?? year.name ?? year.label ?? year.id))
    .filter((y: number | undefined): y is number => Number.isFinite(y))

  const fromStats = Array.isArray(stats.value)
    ? stats.value.map((item) => item.year).filter((y) => Number.isFinite(y))
    : []

  const candidates = [
    currentYear,
    ...(filters.value.year ? [filters.value.year] : []),
    ...fromSchoolYears,
    ...fromStats,
  ]
  const unique = Array.from(new Set(candidates)).filter((y) => Number.isFinite(y)) as number[]

  return unique.sort((a, b) => b - a).map((year) => ({ id: year, name: String(year) }))
})

const referenceData = computed(() => ({
  year: yearOptions.value,
}))

const customLabels = {
  year: (value: any, data: any[]) => {
    const match = data?.find((item: any) => String(item.id) === String(value))
    return match ? match.name : String(value)
  },
}

const hasActiveFilters = computed(() => filters.value.year !== undefined)

const removeFilter = (key: string) => {
  if (key === 'year') {
    filters.value.year = undefined
  }
}

const updateYear = (value: string | number | null) => {
  filters.value.year = value ? Number(value) : undefined
}

const fetchStats = async () => {
  await fetchData({ year: filters.value.year ?? currentYear })
}

onMounted(async () => {
  fetchSchoolYears()
  await fetchStats()
})

watch(
  () => filters.value.year,
  async () => {
    await fetchStats()
  },
)

const monthLabels = [
  'Jan',
  'Fév',
  'Mar',
  'Avr',
  'Mai',
  'Juin',
  'Juil',
  'Août',
  'Sep',
  'Oct',
  'Nov',
  'Déc',
]

const chartData = computed(() => {
  const list = Array.isArray(stats.value) ? stats.value : []
  const grouped = new Map<
    string,
    { label: string; month: number; year: number; Feminin: number; Masculin: number }
  >()

  list.forEach((item) => {
    const month = Number(item.month)
    const year = Number(item.year)
    const key = `${year}-${month}`
    if (!grouped.has(key)) {
      grouped.set(key, {
        label: monthLabels[month - 1] ?? `M${month}`,
        month,
        year,
        Feminin: 0,
        Masculin: 0,
      })
    }

    const entry = grouped.get(key)!
    const gender = String(item.gender).toLowerCase()
    if (gender === 'feminin' || gender === 'féminin') entry.Feminin = item.total_personnel ?? 0
    if (gender === 'masculin') entry.Masculin = item.total_personnel ?? 0
  })

  return Array.from(grouped.values()).sort((a, b) =>
    a.year === b.year ? a.month - b.month : a.year - b.year,
  )
})
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/rh" module-name="rh">
    <PageAnimationWrapper>
      <div class="flex flex-col gap-2 pt-6 md:pt-12">
        <h1 class="font-semibold text-xl text-foreground-title">Tableau de bord RH</h1>
        <p class="text-foreground-muted">Suivez l'évolution mensuelle du personnel par genre.</p>
      </div>

      <div class="pt-7 md:pt-12 pb-20 mx-auto w-full max-w-7xl">
        <div class="flex flex-wrap items-center gap-3 mb-6">
          <Popover>
            <PopoverTrigger as-child>
              <Button variant="ghost" size="sm" class="h-10 rounded-md border bg-white">
                <span class="hidden sm:flex">Filtres</span>
                <span class="iconify hugeicons--filter"></span>
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-72">
              <div class="grid gap-4">
                <div class="space-y-2">
                  <h4 class="font-medium leading-none">Filtrer les données</h4>
                  <p class="text-sm text-muted-foreground">
                    Sélectionnez une année pour rafraîchir le graphique.
                  </p>
                </div>
                <div class="flex flex-col space-y-1.5">
                  <Label class="text-sm font-medium">Année</Label>
                  <Select
                    :model-value="filters.year ? String(filters.year) : ''"
                    @update:modelValue="updateYear"
                  >
                    <SelectTrigger class="h-10 w-full">
                      <SelectValue placeholder="Sélectionnez une année" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectGroup>
                        <SelectItem
                          v-for="year in yearOptions"
                          :key="year.id"
                          :value="String(year.id)"
                        >
                          {{ year.name }}
                        </SelectItem>
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                </div>
                <Button
                  v-if="hasActiveFilters"
                  variant="outline"
                  size="sm"
                  @click="removeFilter('year')"
                  class="w-full"
                >
                  <span class="iconify hugeicons--delete-02 mr-2"></span>
                  Réinitialiser
                </Button>
              </div>
            </PopoverContent>
          </Popover>

          <FilterBadges
            :filters="filters"
            :reference-data="referenceData"
            :custom-labels="customLabels"
            @remove-filter="removeFilter"
          />
        </div>

        <div
          v-if="error"
          class="mb-6 bg-red-50 border border-red-200 rounded-md p-4 flex items-center justify-between"
        >
          <div class="flex items-center gap-2">
            <span class="iconify hugeicons--alert-circle text-red-500"></span>
            <span class="text-red-700">{{ error }}</span>
          </div>
          <Button variant="ghost" class="text-red-600 hover:text-red-800" @click="refreshAll"
            >Réessayer</Button
          >
        </div>

        <div class="mt-4">
          <div v-if="loading" class="bg-white rounded-lg p-8 text-center shadow-sm">
            <div class="animate-pulse space-y-4">
              <div class="h-6 bg-gray-200 rounded"></div>
              <div class="h-64 bg-gray-200 rounded"></div>
            </div>
            <p class="text-gray-500 mt-4">Chargement des données...</p>
          </div>

          <template v-else>
            <BarChart
              v-if="chartData.length"
              :data="chartData"
              index="label"
              :categories="['Feminin', 'Masculin']"
              :rounded-corners="30"
              class="bg-white rounded-lg p-3 text-center items-center capitalize shadow-sm"
            >
              <template #title>
                <h3 class="text-foreground-title font-semibold text-lg">Ressources</h3>
              </template>
              <template #actions>
                <div class="text-sm text-muted-foreground pr-3">
                  Année {{ filters.year ?? currentYear }}
                </div>
              </template>
            </BarChart>

            <div v-else class="bg-white rounded-lg p-8 text-center shadow-sm">
              <div class="text-gray-500 flex flex-col items-center gap-2">
                <p>Aucune donnée disponible pour cette année.</p>
              </div>
            </div>
          </template>
        </div>
      </div>
    </PageAnimationWrapper>
  </DashLayout>
</template>
