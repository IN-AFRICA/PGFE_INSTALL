import type { RouteRecordRaw } from 'vue-router'

const infraRoutes: RouteRecordRaw[] = [
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra',
    redirect: '/infra/prealables',
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
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/operations/infrastructures/nouveau',
    name: 'infra-operations-infrastructures-nouveau',
    component: () => import('@/app/infra/InfrastructureForm.vue'),
  },
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/operations/infrastructures/:id/modifier',
    name: 'infra-operations-infrastructures-modifier',
    component: () => import('@/app/infra/InfrastructureForm.vue'),
  },

  // Préalables Routes
  // Route par défaut: Types & États
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/prealables',
    name: '/infra/prealables',
    component: () => import('@/app/infra/InfraTypeEtat.vue'),
  },
  // Catégories & Bailleurs (side-by-side)
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/prealables/cat-bailleurs',
    name: 'infra-prealables-cat-bailleurs',
    component: () => import('@/app/infra/InfraCatBailleur.vue'),
  },
  // Inventaire Équipements
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/prealables/inventaires',
    name: 'infra-prealables-inventaires',
    component: () => import('@/app/infra/MainInfraPrealables.vue'),
  },
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/prealables/inventaires/:id',
    name: 'infra-prealables-inventaires-details',
    component: () => import('@/app/infra/EquipmentInventoryDetails.vue'),
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


  // Form Routes
  {
    meta: { permission: 'infrastructure.full' },
    path: '/infra/operations/equipements/nouveau',
    name: 'infra-operations-equipements-nouveau',
    component: () => import('@/components/forms/infra/NewEquipement.vue'),
  },

]

export { infraRoutes }
