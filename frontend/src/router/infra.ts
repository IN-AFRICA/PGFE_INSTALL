import type { RouteRecordRaw } from 'vue-router'

const infraRoutes: RouteRecordRaw[] = [
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra',
    component: () => import('@/app/infra/InfraHome.vue'),
    // Commenté pour avoir le composant pays par defaut
  },
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/operations',
    name: '/infra/operations',
    component: () => import('@/app/infra/MainInfraOperations.vue'),
  },
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/operations/infrastructures',
    name: '/infra/operations/infrastructures',
    component: () => import('@/app/infra/MainInfraInfrastructures.vue'),
  },

  // Préalables Routes
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/prealables',
    name: '/infra/prealables',
    component: () => import('@/app/infra/MainInfraPrealables.vue'),
  },
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/prealables/infrastructures',
    name: 'infra-prealables-infrastructures',
    component: () => import('@/app/infra/MainInfraInventoryInfrastructures.vue'),
  },
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/prealables/inventories/:id',
    name: 'infra-prealables-inventory-details',
    component: () => import('@/app/infra/InventoryDetails.vue'),
  },
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/types-etats',
    name: '/infra/types-etats',
    component: () => import('@/app/infra/InfraTypeEtat.vue'),
  },

  // Form Routes
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/operations/equipements/nouveau',
    name: 'infra-operations-equipements-nouveau',
    component: () => import('@/components/forms/infra/NewEquipement.vue'),
  },

]

export { infraRoutes }
