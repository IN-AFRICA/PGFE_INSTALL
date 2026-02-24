<script setup lang="ts">
import { computed, onMounted } from 'vue'
import PageAnimationWrapper from '@/components/atoms/CAnimationWrapper.vue'
import CardDashStat from '@/components/molecules/CardDashStat.vue'
import DashLayout from '@/components/templates/DashLayout.vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { useGetApi } from '@/composables/useGetApi'
import { API_ROUTES } from '@/utils/constants/api_route'

const breadcrumbItems = {
  items: [
    { label: 'Accueil', href: '/', icon: 'hugeicons--home-01' },
    { label: 'Infrastructure', href: '/infra' },
    { label: 'Tableau de bord', isActive: true },
  ],
}

// API call for dashboard data
const {
  data: rawDashboardData,
  loading,
  error: dashboardError,
  fetchData
} = useGetApi(API_ROUTES.GET_INFRA_DASHBOARD)

// Computed properties for dashboard data
const dashboardData = computed(() => rawDashboardData.value?.data || {})
const latestInventaires = computed(() => {
  const inventaires = dashboardData.value.latest_inventaires
  return Array.isArray(inventaires) ? inventaires : []
})
const latestEtats = computed(() => {
  const etats = dashboardData.value.latest_etats
  return Array.isArray(etats) ? etats : []
})
const statusDistribution = computed(() => {
  const distribution = dashboardData.value.status_distribution
  return Array.isArray(distribution) ? distribution : []
})

// Stats cards - similar to Stock dashboard (5 columns)
const stats = computed(() => {
  const data = dashboardData.value || {}
  return [
    {
      title: 'Infrastructures',
      value: Number(data.total_infrastructures || 0),
      icon: 'hugeicons--building-06',
      color: '#3B82F6', // Blue
      description: 'Bâtiments enregistrés',
    },
    {
      title: 'Équipements',
      value: Number(data.total_equipements || 0),
      icon: 'hugeicons--computer',
      color: '#10B981', // Green
      description: 'Équipements disponibles',
    },
    {
      title: 'Inventaires',
      value: Number(data.total_inventaires || 0),
      icon: 'hugeicons--clipboard-list',
      color: '#8B5CF6', // Purple
      description: 'Inventaires réalisés',
    },
    {
      title: 'Signalements',
      value: Number(data.total_signalements || 0),
      icon: 'hugeicons--alert-02',
      color: '#F59E0B', // Amber
      description: 'Problèmes signalés',
    },
  ]
})

// Helper function to format dates
const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return dateString
  }
}

// Fetch dashboard data on mount
onMounted(() => {
  fetchData()
})
</script>

<template>
  <DashLayout :breadcrumb="breadcrumbItems" active-route="/infra" module-name="infra">
    <PageAnimationWrapper>
      <div class="flex flex-col max-w-2xl">
        <h1 class="font-semibold text-xl text-foreground-title">Dashboard Infrastructure</h1>
        <p class="text-foreground-muted mt-0.5">
          Bonjour, Bienvenue à nouveau sur votre plateforme de gestion des infrastructures.
        </p>
      </div>

      <div class="pt-7 md:pt-12 pb-20 mx-auto w-full max-w-7xl">
        <!-- Error state -->
        <div
          v-if="dashboardError"
          class="text-sm text-red-600 bg-red-50 border border-red-200 px-3 py-2 rounded-md mb-6"
        >
          Erreur de chargement du tableau de bord.
          <button type="button" class="ml-2 underline" @click="fetchData">Réessayer</button>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="flex justify-center py-20">
          <span class="iconify animate-spin hugeicons--loading-03 text-2xl text-gray-500"></span>
        </div>

        <template v-else>
          <!-- Stats Cards Grid (4 columns) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 h-[50px] w-[100%] mb-40">
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

          <!-- Distribution des États -->
          <div v-if="statusDistribution.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mt-10 mb-8">
            <h3 class="font-semibold text-foreground-title text-lg mb-4">Distribution des États</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div
                v-for="status in statusDistribution"
                :key="status.name"
                class="text-center p-4 bg-gray-50 rounded-lg"
              >
                <div class="text-2xl font-bold text-foreground-title">{{ status.count }}</div>
                <div class="text-sm text-foreground-muted">{{ status.name }}</div>
              </div>
            </div>
          </div>

          <!-- Inventaires & États récents -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <!-- Derniers Inventaires -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="iconify hugeicons--clipboard-list text-blue-500 text-lg"></span>
                  <h3 class="font-semibold text-foreground-title">Derniers Inventaires</h3>
                </div>
                <Badge v-if="latestInventaires.length" variant="secondary" class="rounded-full">
                  {{ latestInventaires.length }} récent{{ latestInventaires.length > 1 ? 's' : '' }}
                </Badge>
              </div>
              <div v-if="latestInventaires.length" class="max-h-[280px] overflow-auto">
                <Table>
                  <TableHeader>
                    <TableRow class="bg-gray-50">
                      <TableHead class="w-10">#</TableHead>
                      <TableHead>Inventaire</TableHead>
                      <TableHead class="text-center">Date</TableHead>
                      <TableHead class="text-center">Statut</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="(item, index) in latestInventaires" :key="item.id" class="hover:bg-gray-50">
                      <TableCell class="text-gray-400 font-medium">{{ index + 1 }}</TableCell>
                      <TableCell>
                        <div class="font-medium">{{ item.title || `Inventaire #${item.id}` }}</div>
                        <div class="text-xs text-gray-500">{{ item.note || '-' }}</div>
                      </TableCell>
                      <TableCell class="text-center text-sm text-gray-500">
                        {{ formatDate(item.inventory_date) }}
                      </TableCell>
                      <TableCell class="text-center">
                        <Badge variant="default" class="text-[10px] px-1.5 py-0.5">
                          {{ item.status || 'En cours' }}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
              <div v-else class="py-12 text-center text-gray-500">
                <span class="iconify hugeicons--inbox text-4xl text-gray-300 mb-2"></span>
                <p class="text-sm">Aucun inventaire récent</p>
              </div>
            </div>

            <!-- Derniers États -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="iconify hugeicons--check-list text-green-500 text-lg"></span>
                  <h3 class="font-semibold text-foreground-title">Derniers États</h3>
                </div>
                <span class="text-xs text-gray-500">{{ latestEtats.length }} état(s)</span>
              </div>
              <div v-if="latestEtats.length" class="max-h-[280px] overflow-auto">
                <Table>
                  <TableHeader>
                    <TableRow class="bg-gray-50">
                      <TableHead class="w-10">#</TableHead>
                      <TableHead>Nom</TableHead>
                      <TableHead class="text-center">Date de création</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="(etat, idx) in latestEtats" :key="etat.id" class="hover:bg-gray-50">
                      <TableCell class="text-gray-400 font-medium">{{ idx + 1 }}</TableCell>
                      <TableCell class="font-medium">{{ etat.name }}</TableCell>
                      <TableCell class="text-center text-sm text-gray-500">
                        {{ formatDate(etat.created_at) }}
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
              <div v-else class="py-12 text-center text-gray-500">
                <span class="iconify hugeicons--inbox text-4xl text-gray-300 mb-2"></span>
                <p class="text-sm">Aucun état récent</p>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mt-8">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
              <span class="iconify hugeicons--flash text-purple-500 text-lg"></span>
              <h3 class="font-semibold text-foreground-title">Actions Rapides</h3>
            </div>
            <div class="p-4">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <RouterLink
                  to="/infra/prealables"
                  class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-blue-300 transition-all group"
                >
                  <span class="iconify hugeicons--clipboard-list text-2xl text-blue-500 mr-3 group-hover:scale-110 transition-transform"></span>
                  <div>
                    <p class="font-medium text-foreground-title">Inventaires</p>
                    <p class="text-xs text-foreground-muted">Gérer les inventaires</p>
                  </div>
                </RouterLink>

                <RouterLink
                  to="/infra/operations"
                  class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-green-300 transition-all group"
                >
                  <span class="iconify hugeicons--computer text-2xl text-green-500 mr-3 group-hover:scale-110 transition-transform"></span>
                  <div>
                    <p class="font-medium text-foreground-title">Équipements</p>
                    <p class="text-xs text-foreground-muted">Gérer les équipements</p>
                  </div>
                </RouterLink>

                <RouterLink
                  to="/infra/operations/infrastructures"
                  class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-yellow-300 transition-all group"
                >
                  <span class="iconify hugeicons--building-06 text-2xl text-yellow-500 mr-3 group-hover:scale-110 transition-transform"></span>
                  <div>
                    <p class="font-medium text-foreground-title">Infrastructures</p>
                    <p class="text-xs text-foreground-muted">Gérer les infrastructures</p>
                  </div>
                </RouterLink>

                <RouterLink
                  to="/infra/prealables"
                  class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-purple-300 transition-all group"
                >
                  <span class="iconify hugeicons--settings-02 text-2xl text-purple-500 mr-3 group-hover:scale-110 transition-transform"></span>
                  <div>
                    <p class="font-medium text-foreground-title">Types & États</p>
                    <p class="text-xs text-foreground-muted">Configurer types et états</p>
                  </div>
                </RouterLink>
              </div>
            </div>
          </div>
        </template>
      </div>
    </PageAnimationWrapper>
  </DashLayout>
</template>

<style scoped></style>
