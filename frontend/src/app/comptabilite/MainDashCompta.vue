<script setup lang="ts">
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
import { useGetApi } from '@/composables/useGetApi.ts'
import { API_ROUTES } from '@/utils/constants/api_route.ts'
import { ref, computed, onMounted, watch } from 'vue'

// Chart data derived from backend
type ChartPoint = { name: string; recettes: number; depenses: number }

// Computed chart data from backend response
const chartData = computed<ChartPoint[]>(() => {
  const raw = (dashboardRaw.value as any) ?? null
  // Prefer nested chart node if present
  const source = raw?.chart ?? raw
  return normalizeChart(source)
})

const frenchMonths = [
  'Janvier',
  'Février',
  'Mars',
  'Avril',
  'Mai',
  'Juin',
  'Juillet',
  'Août',
  'Septembre',
  'Octobre',
  'Novembre',
  'Décembre',
]

function monthLabel(m: any): string {
  if (typeof m === 'number') {
    const idx = Math.max(1, Math.min(12, m)) - 1
    return frenchMonths[idx]
  }
  if (typeof m === 'string') {
    // Accept already-localized month labels or English month names
    const lower = m.toLowerCase()
    const en = [
      'january',
      'february',
      'march',
      'april',
      'may',
      'june',
      'july',
      'august',
      'september',
      'october',
      'november',
      'december',
    ]
    const iEn = en.indexOf(lower)
    if (iEn >= 0) return frenchMonths[iEn]
    return m
  }
  return '-'
}

function normalizeChart(raw: any): ChartPoint[] {
  // Support multiple possible backend shapes
  try {
    if (!raw) return []

    console.log('Dashboard - Raw data received:', raw)

    // Case SPECIAL: Direct { recette: "2000", dependances: "23000" } format from API
    if (raw.recette !== undefined || raw.dependances !== undefined) {
      const recetteValue = Number(raw.recette ?? 0)
      const depensesValue = Number(raw.dependances ?? raw.depenses ?? 0)

      console.log('Dashboard - Direct format detected:', {
        recette: recetteValue,
        depenses: depensesValue,
      })

      // Distribute values across months for visualization
      // Get current month to highlight it
      const currentMonth = new Date().getMonth() // 0-11

      return frenchMonths.map((monthName, index) => {
        // Show actual values for current month, zero for others
        // Or you can distribute evenly: recetteValue / 12
        const isCurrentMonth = index === currentMonth

        return {
          name: monthName,
          recettes: isCurrentMonth ? recetteValue : 0,
          depenses: isCurrentMonth ? depensesValue : 0,
        }
      })
    }

    // Case A: { chart: { monthly: [{ month, recettes, depenses }, ...] } }
    const monthly = raw?.chart?.monthly || raw?.monthly || raw?.chartData?.monthly
    if (Array.isArray(monthly)) {
      return monthly.map((it: any) => ({
        name: monthLabel(it.month ?? it.name ?? it.label),
        recettes: Number(it.recettes ?? it.recette ?? it.revenue ?? it.income ?? 0),
        depenses: Number(it.depenses ?? it.dependances ?? it.expense ?? it.expenses ?? 0),
      }))
    }

    // Case B: { revenues: [{month, amount}], expenses: [{month, amount}] }
    const revenues = raw?.revenues || raw?.income
    const expenses = raw?.expenses || raw?.depenses || raw?.dependances
    if (Array.isArray(revenues) && Array.isArray(expenses)) {
      const byMonth = new Map<string, { recettes: number; depenses: number }>()
      for (const r of revenues) {
        const key = monthLabel(r.month ?? r.name)
        const prev = byMonth.get(key) || { recettes: 0, depenses: 0 }
        prev.recettes += Number(r.amount ?? r.total ?? 0)
        byMonth.set(key, prev)
      }
      for (const e of expenses) {
        const key = monthLabel(e.month ?? e.name)
        const prev = byMonth.get(key) || { recettes: 0, depenses: 0 }
        prev.depenses += Number(e.amount ?? e.total ?? 0)
        byMonth.set(key, prev)
      }
      // Order by month if possible
      const ordered = frenchMonths.map((m) => ({
        name: m,
        recettes: byMonth.get(m)?.recettes ?? 0,
        depenses: byMonth.get(m)?.depenses ?? 0,
      }))
      // If no months matched, fallback to map iteration
      const hasAny = ordered.some((p) => p.recettes || p.depenses)
      if (hasAny) return ordered
      return Array.from(byMonth.entries()).map(([name, v]) => ({
        name,
        recettes: v.recettes,
        depenses: v.depenses,
      }))
    }

    // Case C: raw is an array of points already
    if (Array.isArray(raw)) {
      return raw.map((it: any) => ({
        name: monthLabel(it.month ?? it.name ?? it.label),
        recettes: Number(it.recettes ?? it.recette ?? it.revenue ?? it.income ?? 0),
        depenses: Number(it.depenses ?? it.dependances ?? it.expense ?? it.expenses ?? 0),
      }))
    }

    // Case D: { data: [...] }
    if (Array.isArray(raw?.data)) {
      return raw.data.map((it: any) => ({
        name: monthLabel(it.month ?? it.name ?? it.label),
        recettes: Number(it.recettes ?? it.recette ?? it.revenue ?? it.income ?? 0),
        depenses: Number(it.depenses ?? it.dependances ?? it.expense ?? it.expenses ?? 0),
      }))
    }

    // Fallback: return empty
    console.warn('Dashboard - No matching format found, returning empty array')
    return []
  } catch (e) {
    console.warn('Dashboard chart normalization error:', e)
  }
  return []
}

// Dashboard stats from backend
type DashboardStats = {
  total_accounts: number
  total_budgets: number
  total_balance: number
  total_paid: number
  students_paid: number
}

const selectedSchoolYear = ref<string>('all')

// Load school years for optional filtering
const {
  data: schoolYearsRaw,
  loading: loadingYears,
  fetchData: fetchSchoolYears,
} = useGetApi<any>(API_ROUTES.GET_SHOOL_YEARS)
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

// Fetch dashboard stats
const {
  data: dashboardRaw,
  loading: loadingDashboard,
  error: dashboardError,
  fetchData: fetchDashboard,
} = useGetApi<DashboardStats>(API_ROUTES.GET_DATA_DASHBOARD)

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

const totalAccounts = computed(() => Number((dashboardRaw.value as any)?.total_accounts ?? 0))
const totalBudgets = computed(() => Number((dashboardRaw.value as any)?.total_budgets ?? 0))
const totalBalance = computed(() => Number((dashboardRaw.value as any)?.total_balance ?? 0))
const totalPaid = computed(() => Number((dashboardRaw.value as any)?.total_paid ?? 0))
const studentsPaid = computed(() => Number((dashboardRaw.value as any)?.students_paid ?? 0))

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Comptabilité', isActive: true },
  ],
}
</script>

<template>
  <DashLayout active-route="/comptabilite" module-name="compta" :breadcrumb="breadcrumbItems">
    <PageAnimationWrapper>
      <div class="flex flex-col max-w-2xl">
        <h1 class="font-semibold text-xl text-foreground-title">Dashboard Comptabilité</h1>
        <p class="text-foreground-muted mt-0.5">
          Bonjour In Afrique, Bienvenu à nouveau sur votre plateforme de gestion financière.
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 h-[50px] w-[100%] mb-40">
          <CardDashStat
            icon="hugeicons--money-receive-01"
            :value="totalPaid"
            title="Montant total payé"
            description="Somme des paiements"
            color="#10B981"
          />
          <CardDashStat
            icon="hugeicons--money-receive-circle"
            :value="totalBalance"
            title="Solde total"
            description="Balance comptable"
            color="#3B82F6"
          />
          <CardDashStat
            icon="hugeicons--wallet-01"
            :value="totalBudgets"
            title="Budgets"
            description="Budgets enregistrés"
            color="#F59E0B"
          />
          <CardDashStat
            icon="hugeicons--bank"
            :value="totalAccounts"
            title="Comptes bancaires"
            description="Comptes enregistrés"
            color="#EF4444"
          />
          <CardDashStat
            icon="hugeicons--user-group"
            :value="studentsPaid"
            title="Élèves payants"
            description="Élèves avec frais à jour"
            color="#8B5CF6"
          />
        </div>
        <BarChart
          :data="chartData"
          index="name"
          :categories="['recettes', 'depenses']"
          :rounded-corners="30"
          class="mt-10 bg-white rounded-lg p-3"
        >
          <template #title>
            <h3 class="text-foreground-title font-semibold text-lg">Évolution Financière</h3>
          </template>
          <template #actions>
            <div>
              <!--<Select>
                                <SelectTrigger class="!h-9 rounded-md border bg-white">
                                    <SelectValue placeholder="Période" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="mois">Mois</SelectItem>
                                        <SelectItem value="trimestre">Trimestre</SelectItem>
                                        <SelectItem value="semestre">Semestre</SelectItem>
                                        <SelectItem value="annee">Année</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>-->
            </div>
          </template>
        </BarChart>
      </div>
    </PageAnimationWrapper>
  </DashLayout>
</template>
