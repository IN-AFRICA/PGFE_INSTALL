<script setup lang="ts">
import PageAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import CardDashStat from '@/components/molecules/CardDashStat.vue'
import DashLayout from '@/components/templates/DashLayoutWithMode.vue'
import { BarChart } from '@/components/ui/chart-bar'
import { Button } from '@/components/ui/button'
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
import FilterBadges from '@/components/atoms/FilterBadges.vue'
import { useDashboard } from '@/composables/useDashboard'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'
import { computed, onMounted, watch } from 'vue'

const {
  loading,
  error,
  totalStudents,
  totalGirls,
  totalBoys,
  chartData,
  currentSchoolYear,
  schoolYearTotal,
  currentYearData,
  filters,
  refresh,
  clearFilters,
  data: dashboardData,
} = useDashboard()

const { data: classrooms, fetchData: fetchClassrooms } = useGetApi(API_ROUTES.GET_CLASSROOMS)
const { data: filieres, fetchData: fetchFilieres } = useGetApi(API_ROUTES.GET_FILLIERES)
const { data: schoolYears, fetchData: fetchSchoolYears } = useGetApi(API_ROUTES.GET_SCHOOL_YEARS)

const refreshAllData = () => {
  fetchClassrooms()
  fetchFilieres()
  fetchSchoolYears()
  refresh()
}
onMounted(() => {
  refreshAllData()
})

const schoolYearsList = computed(() => {
  const v: any = schoolYears.value
  if (Array.isArray(v)) return v
  return v?.years ?? v?.data ?? []
})

const filieresList = computed(() => {
  const v: any = filieres.value
  if (Array.isArray(v)) return v
  return v?.data ?? []
})

const filteredClassrooms = computed(() => {
  const v: any = classrooms.value
  const list = Array.isArray(v) ? v : (v?.data ?? [])

  if (!filters.filiaire_id) return list

  return list.filter((c: any) => {
    const directId = c.filiere_id || c.filiaire_id
    if (directId) return String(directId) === String(filters.filiaire_id)

    const nestedId =
      c.academic_level?.cycle?.filiaire?.id ||
      c.academic_level?.cycle?.filiere_id ||
      c.academic_level?.cycle?.filiaire_id

    if (nestedId) return String(nestedId) === String(filters.filiaire_id)

    return false
  })
})

watch(
  () => filters.school_year_id,
  () => {
    filters.filiaire_id = undefined
    filters.classroom_id = undefined
  },
)

watch(
  () => filters.filiaire_id,
  () => {
    filters.classroom_id = undefined
  },
)

const referenceData = computed(() => ({
  school_year_id: schoolYearsList.value || [],
  classroom_id: classrooms.value || [],
  filiaire_id: filieresList.value || [],
}))

const customLabels = {
  school_year_id: (value: any, data: any[]) => {
    const year = data?.find((y: any) => y.id === value)
    return year ? year.name : value
  },
  classroom_id: (value: any, data: any[]) => {
    const list = Array.isArray(classrooms.value)
      ? classrooms.value
      : ((classrooms.value as any)?.data ?? [])
    const classroom = list?.find((c: any) => c.id === value)
    return classroom ? classroom.name : value
  },
  filiaire_id: (value: any, data: any[]) => {
    const filiere = data?.find((f: any) => f.id === value)
    return filiere ? filiere.name : value
  },
}

const removeFilter = (key: string) => {
  if (key === 'school_year_id') {
    filters.school_year_id = undefined
    filters.filiaire_id = undefined
    filters.classroom_id = undefined
  }
  if (key === 'filiaire_id') {
    filters.filiaire_id = undefined
    filters.classroom_id = undefined
  }
  if (key === 'classroom_id') {
    filters.classroom_id = undefined
  }
}

const hasActiveFilters = computed(() => {
  return filters.school_year_id || filters.classroom_id || filters.filiaire_id
})

const updateSchoolYear = (value: string) => {
  filters.school_year_id = value ? Number(value) : undefined
}

const updateFiliere = (value: string) => {
  filters.filiaire_id = value ? Number(value) : undefined
}

const updateClassroom = (value: string) => {
  filters.classroom_id = value ? Number(value) : undefined
}

// Watchers pour définir l'année active par défaut
watch(
  [schoolYearsList, dashboardData],
  ([years, data]) => {
    if (filters.school_year_id) return

    if (data?.school_year_id) {
      filters.school_year_id = Number(data.school_year_id)
      return
    }

    if (years?.length) {
      const activeYear = years.find(
        (y: any) =>
          y.encours == 1 ||
          y.encours === true ||
          y.encours === 'true' ||
          y.is_active == 1 ||
          y.is_active === true ||
          y.active == 1 ||
          y.active === true,
      )
      if (activeYear) {
        filters.school_year_id = activeYear.id
        return
      }
    }

    const currentName = data?.school_year_name
    if (currentName && years?.length) {
      const matchingYear = years.find(
        (y: any) =>
          String(y.name).trim().toLowerCase() === String(currentName).trim().toLowerCase(),
      )
      if (matchingYear) {
        filters.school_year_id = matchingYear.id
      }
    }
  },
  { immediate: true },
)
</script>

<template>
  <DashLayout
    non-formel-link="/apprenants"
    :show-switcher="false"
    active-route="/apprenants"
    module-name="students"
  >
    <PageAnimationWrapper>
      <div class="flex flex-col-reverse md:justify-between md:flex-row pt-6 md:pt-12">
        <div class="flex flex-col max-w-2xl">
          <h1 class="font-semibold text-xl text-foreground-title">Dashboard</h1>
          <p class="text-foreground-muted mt-0.5">
            Bonjour, Bienvenu à nouveau sur votre plateforme.
          </p>
          <p v-if="currentSchoolYear" class="text-sm text-primary mt-1">
            Année scolaire actuelle: {{ currentSchoolYear }}
          </p>
        </div>
      </div>

      <div v-if="error" class="pt-4">
        <div
          class="bg-red-50 border border-red-200 rounded-md p-4 flex items-center justify-between"
        >
          <div class="flex items-center">
            <span class="iconify hugeicons--alert-circle text-red-500 mr-2"></span>
            <span class="text-red-700">{{ error }}</span>
          </div>
          <Button @click="refreshAllData" class="text-red-600 hover:text-red-800 underline">
            Réessayer
          </Button>
        </div>
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
            <PopoverContent class="w-80">
              <div class="grid gap-4">
                <div class="space-y-2">
                  <h4 class="font-medium leading-none">Filtrer les données</h4>
                  <p class="text-sm text-muted-foreground">
                    Sélectionnez les critères pour filtrer le dashboard
                  </p>
                </div>
                <div class="flex flex-col gap-3.5">
                  <div class="flex flex-col space-y-1.5">
                    <Label class="text-sm font-medium">Année scolaire</Label>
                    <Select
                      :model-value="filters.school_year_id ? String(filters.school_year_id) : ''"
                      @update:modelValue="updateSchoolYear"
                    >
                      <SelectTrigger class="h-10 w-full">
                        <SelectValue placeholder="Sélectionnez une année" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="year in schoolYearsList"
                            :key="year.id"
                            :value="String(year.id)"
                          >
                            {{ year.name }}
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>

                  <div class="flex flex-col space-y-1.5">
                    <Label
                      class="text-sm font-medium"
                      :class="{ 'text-gray-400': !filters.school_year_id }"
                      >Section</Label
                    >
                    <Select
                      :model-value="filters.filiaire_id ? String(filters.filiaire_id) : ''"
                      @update:modelValue="updateFiliere"
                      :disabled="!filters.school_year_id"
                    >
                      <SelectTrigger class="h-10 w-full" :disabled="!filters.school_year_id">
                        <SelectValue
                          :placeholder="
                            filters.school_year_id
                              ? 'Sélectionnez une section'
                              : 'Sélectionnez d\'abord une année'
                          "
                        />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="filiere in filieresList"
                            :key="filiere.id"
                            :value="String(filiere.id)"
                          >
                            {{ filiere.name }}
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>

                  <div class="flex flex-col space-y-1.5">
                    <Label
                      class="text-sm font-medium"
                      :class="{ 'text-gray-400': !filters.filiaire_id }"
                      >Classe</Label
                    >
                    <Select
                      :model-value="filters.classroom_id ? String(filters.classroom_id) : ''"
                      @update:modelValue="updateClassroom"
                      :disabled="!filters.filiaire_id"
                    >
                      <SelectTrigger class="h-10 w-full" :disabled="!filters.filiaire_id">
                        <SelectValue
                          :placeholder="
                            filters.filiaire_id
                              ? 'Sélectionnez une classe'
                              : 'Sélectionnez d\'abord une section'
                          "
                        />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup>
                          <SelectItem
                            v-for="classroom in filteredClassrooms"
                            :key="classroom.id"
                            :value="String(classroom.id)"
                          >
                            {{ classroom.name }}
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
                <Button
                  v-if="hasActiveFilters"
                  variant="outline"
                  size="sm"
                  @click="clearFilters"
                  class="w-full"
                >
                  <span class="iconify hugeicons--delete-02 mr-2"></span>
                  Réinitialiser les filtres
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

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <CardDashStat
            icon="hugeicons--user"
            :value="loading ? 0 : totalStudents"
            title="Total"
            description="Total des apprenants"
            color="#008080"
            :loading="loading"
          />
          <CardDashStat
            icon="hugeicons--user"
            :value="loading ? 0 : totalBoys"
            title="Garçons"
            description="Total des garçons"
            color="#008080"
            :loading="loading"
          />
          <CardDashStat
            icon="hugeicons--user"
            :value="loading ? 0 : totalGirls"
            title="Filles"
            description="Total des filles"
            color="#008080"
            :loading="loading"
          />
        </div>

        <div class="mt-10">
          <div v-if="loading" class="bg-white rounded-lg p-8 text-center">
            <div class="animate-pulse">
              <div class="h-64 bg-gray-200 rounded"></div>
            </div>
            <p class="text-gray-500 mt-4">Chargement des données...</p>
          </div>

          <div v-else-if="chartData && chartData.length > 0">
            <BarChart
              :data="chartData"
              index="name"
              :categories="['garcon', 'fille']"
              :rounded-corners="30"
              :y-formatter="(value) => `${value} %`"
              class="bg-white rounded-lg p-3 text-center items-center capitalize"
            >
            </BarChart>
          </div>

          <div v-else class="bg-white rounded-lg p-8 text-center">
            <div class="text-gray-500">
              <span class="iconify hugeicons--chart-bar text-4xl mb-4 block"></span>
              <p>Aucune donnée disponible pour le graphique</p>
            </div>
          </div>
        </div>
      </div>
    </PageAnimationWrapper>
  </DashLayout>
</template>
