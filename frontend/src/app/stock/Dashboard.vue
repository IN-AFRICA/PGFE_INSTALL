<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import PageAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import CardDashStat from '@/components/molecules/CardDashStat.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import { BarChart } from '@/components/ui/chart-bar'
import {
  Select,
  SelectItem,
  SelectTrigger,
  SelectGroup,
  SelectValue,
  SelectContent,
} from '@/components/ui/select'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { API_ROUTES } from '@/utils/constants/api_route'
import { useGetApi } from '@/composables/useGetApi'

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Stock', href: '/stock' },
    { label: 'Tableau de bord', isActive: true },
  ],
}

// Type pour les données du dashboard
interface StockDashboardData {
  total_articles: number
  stock_total_quantity: number
  total_entries: string | number
  total_exits: string | number
  qty_entries: string | number
  qty_exits: string | number
  articles: any[]
  alertes: any[]
  operations_recents: any[]
}

// State for school year filter
const selectedSchoolYear = ref<string>('all')

// Load school years for filtering
const {
  data: schoolYearsRaw,
  loading: loadingYears,
  fetchData: fetchSchoolYears,
} = useGetApi<any>(API_ROUTES.GET_SCHOOL_YEARS)

const schoolYears = computed(() => {
  const v: any = schoolYearsRaw?.value
  let list: any[] = []
  if (Array.isArray(v)) list = v
  else if (v?.years && Array.isArray(v.years)) list = v.years
  else if (v?.data && Array.isArray(v.data)) list = v.data
  else if (v && typeof v === 'object')
    list = Object.values(v).filter((it: any) => it && typeof it === 'object' && (it.id || it.value))
  return list.map((y: any) => ({
    id: String(y.id ?? y.value ?? ''),
    name: y.name || y.label || y.year || `Année #${y.id ?? y.value}`,
  }))
})

// Fetch Dashboard Data
const {
  data: dashboardRaw,
  loading: loadingDashboard,
  error: dashboardError,
  fetchData: fetchDashboard,
} = useGetApi<StockDashboardData>(API_ROUTES.GET_STOCK_DASHBOARD)

async function loadDashboard() {
  if (selectedSchoolYear.value && selectedSchoolYear.value !== 'all') {
    await fetchDashboard({ school_year_id: selectedSchoolYear.value })
  } else {
    await fetchDashboard()
  }
}

onMounted(async () => {
  await Promise.all([fetchSchoolYears(), loadDashboard()])
})

watch(selectedSchoolYear, () => {
  loadDashboard()
})

// Combined loading state
const loading = computed(() => loadingDashboard.value || loadingYears.value)

// Alertes et opérations récentes
const alertes = computed(() => (dashboardRaw.value as any)?.alertes || [])
const operationsRecentes = computed(() => (dashboardRaw.value as any)?.operations_recents || [])

// Format date helper
const formatDate = (dateStr: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Stats cards - similar to Comptabilité dashboard (5 columns)
const stats = computed(() => {
  const data = (dashboardRaw.value || {}) as any
  return [
    {
      title: 'Total Items',
      value: Number(data.total_articles || 0),
      icon: 'hugeicons--package',
      color: '#3B82F6', // Blue
      description: 'Items enregistrés',
    },
    {
      title: 'Quantité en Stock',
      value: Number(data.stock_total_quantity || 0),
      icon: 'hugeicons--cube-01',
      color: '#10B981', // Green
      description: 'Unités disponibles',
    },
    {
      title: 'Total Entrées',
      value: Number(data.total_entries || 0),
      icon: 'hugeicons--arrow-down-01',
      color: '#8B5CF6', // Purple
      description: `${Number(data.qty_entries || 0)} unités`,
    },
    {
      title: 'Total Sorties',
      value: Number(data.total_exits || 0),
      icon: 'hugeicons--arrow-up-01',
      color: '#EF4444', // Red
      description: `${Number(data.qty_exits || 0)} unités`,
    },
    {
      title: 'Alertes Stock',
      value: data.alertes?.length || 0,
      icon: 'hugeicons--alert-02',
      color: '#F59E0B', // Amber
      description: 'Items sous seuil',
    },
  ]
})

// Chart data - monthly stock movements (like Comptabilité)
const frenchMonths = [
  'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
]

const chartData = computed(() => {
  const data = (dashboardRaw.value || {}) as any
  const currentMonth = new Date().getMonth() // 0-11
  
  const entreeValue = Number(data.qty_entries || 0)
  const sortieValue = Number(data.qty_exits || 0)

  // Distribute values across months - show actual values for current month
  return frenchMonths.map((monthName, index) => {
    const isCurrentMonth = index === currentMonth
    return {
      name: monthName,
      entrees: isCurrentMonth ? entreeValue : 0,
      sorties: isCurrentMonth ? sortieValue : 0,
    }
  })
})
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/stock/dashboard" module-name="stock">
    <PageAnimationWrapper>
      <div class="flex flex-col max-w-2xl">
        <h1 class="font-semibold text-xl text-foreground-title">Dashboard Stock</h1>
        <p class="text-foreground-muted mt-0.5">
          Bonjour, Bienvenue à nouveau sur votre plateforme de gestion des stocks.
        </p>
      </div>

      <div class="pt-7 md:pt-12 pb-20 mx-auto w-full max-w-7xl">
        <!-- Filtres -->
        <div class="flex items-center justify-between mb-6 gap-3">
          <div
            v-if="dashboardError"
            class="text-sm text-red-600 bg-red-50 border border-red-200 px-3 py-2 rounded-md"
          >
            Erreur de chargement du tableau de bord.
            <button type="button" class="ml-2 underline" @click="loadDashboard">Réessayer</button>
          </div>
          <div class="w-64">
            <Select v-model="selectedSchoolYear" :disabled="loadingYears || loadingDashboard">
              <SelectTrigger
                class="!h-9 rounded-md border bg-white"
                :class="loadingYears || loadingDashboard ? 'opacity-60 cursor-not-allowed' : ''"
              >
                <SelectValue :placeholder="loadingYears ? 'Chargement...' : 'Année scolaire'" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="all">Toutes les années</SelectItem>
                  <SelectItem v-for="y in schoolYears" :key="y.id || y.name" :value="y.id">
                    {{ y.name }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="flex justify-center py-20">
          <span class="iconify animate-spin hugeicons--loading-03 text-2xl text-gray-500"></span>
        </div>

        <template v-else>
          <!-- Stats Cards Grid (5 columns like Compta) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 h-[50px] w-[100%] mb-40">
            <CardDashStat
              v-for="(stat, index) in stats"
              :key="index"
              :title="stat.title"
              :value="stat.value"
              :icon="stat.icon"
              :color="stat.color"
              :description="stat.description"
            />
          </div>

          <!-- Chart (Monthly evolution like Compta) -->
          <BarChart
            :data="chartData"
            index="name"
            :categories="['entrees', 'sorties']"
            :rounded-corners="30"
            class="mt-10 bg-white rounded-lg p-3"
          >
            <template #title>
              <h3 class="text-foreground-title font-semibold text-lg">Mouvements de Stock</h3>
            </template>
          </BarChart>

          <!-- Alertes & Opérations récentes -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <!-- Alertes de Stock -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="iconify hugeicons--alert-02 text-amber-500 text-lg"></span>
                  <h3 class="font-semibold text-foreground-title">Alertes de Stock</h3>
                </div>
                <Badge v-if="alertes.length" variant="destructive" class="rounded-full">
                  {{ alertes.length }} alerte{{ alertes.length > 1 ? 's' : '' }}
                </Badge>
              </div>
              <div v-if="alertes.length" class="max-h-[280px] overflow-auto">
                <Table>
                  <TableHeader>
                    <TableRow class="bg-gray-50">
                      <TableHead class="w-10">#</TableHead>
                      <TableHead>Item</TableHead>
                      <TableHead class="text-center">Qté</TableHead>
                      <TableHead class="text-center">Min</TableHead>
                      <TableHead class="text-center">Max</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="(item, index) in alertes" :key="item.id" class="hover:bg-gray-50">
                      <TableCell class="text-gray-400 font-medium">{{ index + 1 }}</TableCell>
                      <TableCell>
                        <div class="font-medium">{{ item.name }}</div>
                        <div class="text-xs text-gray-500">{{ item.category?.name || '-' }}</div>
                      </TableCell>
                      <TableCell class="text-center">
                        <span class="font-bold text-red-600">{{ item.quantity }}</span>
                      </TableCell>
                      <TableCell class="text-center text-gray-600">{{ item.min_threshold }}</TableCell>
                      <TableCell class="text-center text-gray-600">{{ item.max_threshold }}</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
              <div v-else class="py-12 text-center text-gray-500">
                <span class="iconify hugeicons--check-circle text-4xl text-green-500 mb-2"></span>
                <p class="text-sm">Aucune alerte de stock</p>
              </div>
            </div>

            <!-- Opérations Récentes -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="iconify hugeicons--clock-01 text-blue-500 text-lg"></span>
                  <h3 class="font-semibold text-foreground-title">Opérations Récentes</h3>
                </div>
                <span class="text-xs text-gray-500">{{ operationsRecentes.length }} opération(s)</span>
              </div>
              <div v-if="operationsRecentes.length" class="max-h-[280px] overflow-auto">
                <Table>
                  <TableHeader>
                    <TableRow class="bg-gray-50">
                      <TableHead class="w-10">#</TableHead>
                      <TableHead>Date</TableHead>
                      <TableHead>Type</TableHead>
                      <TableHead>Item</TableHead>
                      <TableHead class="text-right">Qté</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="(op, idx) in operationsRecentes" :key="idx" class="hover:bg-gray-50">
                      <TableCell class="text-gray-400 font-medium">{{ idx + 1 }}</TableCell>
                      <TableCell class="text-xs text-gray-500 whitespace-nowrap">
                        {{ formatDate(op.date) }}
                      </TableCell>
                      <TableCell>
                        <Badge
                          :variant="op.type?.toLowerCase().includes('entr') ? 'default' : 'destructive'"
                          class="text-[10px] px-1.5 py-0.5"
                        >
                          {{ op.type }}
                        </Badge>
                      </TableCell>
                      <TableCell class="font-medium">{{ op.article }}</TableCell>
                      <TableCell class="text-right font-semibold">{{ op.quantite }}</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
              <div v-else class="py-12 text-center text-gray-500">
                <span class="iconify hugeicons--inbox text-4xl text-gray-300 mb-2"></span>
                <p class="text-sm">Aucune opération récente</p>
              </div>
            </div>
          </div>
        </template>
      </div>
    </PageAnimationWrapper>
  </DashLayout>
</template>
