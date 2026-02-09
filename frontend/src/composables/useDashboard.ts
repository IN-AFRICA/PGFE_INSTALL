import { ref, computed, onMounted, reactive, watch } from 'vue'
import DashboardService, {
  type DashboardStats,
  type DashboardFilters,
} from '@/services/DashboardService'

export function useDashboard() {
  const data = ref<DashboardStats | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const filters = reactive<DashboardFilters>({
    school_year_id: undefined,
    classroom_id: undefined,
    filiaire_id: undefined,
  })

  const totalStudents = computed(() => {
    return data.value ? data.value.total : 0
  })

  const totalGirls = computed(() => {
    return data.value ? data.value.girls : 0
  })

  const totalBoys = computed(() => {
    return data.value ? data.value.boys : 0
  })

  const filteredData = computed(() => {
    return data.value?.filtered || { gender: '', count: '0' }
  })

  const currentSchoolYear = computed(() => {
    return data.value?.school_year_name || ''
  })

  const schoolYearTotal = computed(() => {
    return data.value?.school_year_total || 0
  })

  const chartData = computed(() => {
    if (!data.value?.by_school_year) return []

    const totalAllYear = Object.values(data.value?.by_school_year).reduce(
      (sum, count) => sum + parseInt(count),
      0,
    )

    return Object.entries(data.value.by_school_year).map(([year, total]) => {
      const yearTotal = parseInt(total)
      const totalStudents = data.value?.total || 0
      const boysPercentage =
        totalStudents > 0 ? Math.round((data.value!.boys / totalStudents) * 100) : 0
      const girlsPercentage =
        totalStudents > 0 ? Math.round((data.value!.girls / totalStudents) * 100) : 0

      const girlsAbsolute = Math.round((yearTotal * data.value!.girls) / totalStudents) || 0
      const boysAbsolute = Math.round((yearTotal * data.value!.boys) / totalStudents) || 0

      return {
        name: year,
        total: yearTotal,
        garcon: boysPercentage,
        fille: girlsPercentage,
        garconA: boysAbsolute,
        filleA: girlsAbsolute,
      }
    })
  })

  const currentYearData = computed(() => {
    if (!data.value) return null

    return {
      year: data.value.school_year_name,
      total: data.value.school_year_total,
      girls: data.value.girls,
      boys: data.value.boys,
    }
  })

  const fetchDashboardData = async () => {
    loading.value = true
    error.value = null

    try {
      const activeFilters: DashboardFilters = {}
      if (filters.school_year_id) activeFilters.school_year_id = filters.school_year_id
      if (filters.classroom_id) activeFilters.classroom_id = filters.classroom_id
      if (filters.filiaire_id) activeFilters.filiaire_id = filters.filiaire_id

      const response = await DashboardService.getStats(activeFilters)
      console.log('useDashboard - API Response:', response)

      if (response.success) {
        data.value = response.data
      } else {
        error.value = response.message || 'Erreur lors de la récupération des données'
      }
    } catch (err: any) {
      error.value = err.message || 'Erreur de connexion'
      console.error('Erreur dashboard:', err)
    } finally {
      loading.value = false
    }
  }

  const refresh = () => {
    return fetchDashboardData()
  }

  const clearFilters = () => {
    filters.school_year_id = undefined
    filters.classroom_id = undefined
    filters.filiaire_id = undefined
  }

  watch(
    () => [filters.school_year_id, filters.classroom_id, filters.filiaire_id],
    () => {
      fetchDashboardData()
    },
  )

  onMounted(() => {
    fetchDashboardData()
  })

  return {
    data,
    loading,
    error,

    filters,

    totalStudents,
    totalGirls,
    totalBoys,
    filteredData,
    currentSchoolYear,
    schoolYearTotal,
    chartData,
    currentYearData,

    fetchDashboardData,
    refresh,
    clearFilters,
  }
}
