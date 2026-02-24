import type { RouteRecordRaw } from 'vue-router'

const locationRoutes: RouteRecordRaw[] = [
  {
    meta: { permission: 'location.full' },
    path: '/location',
    redirect: '/location/prealables/projects',
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/operations',
    name: 'location-operations',
    component: () => import('@/app/location/MainLocationOperations.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/operations/versements',
    name: 'location-operations-versements',
    component: () => import('@/app/location/LocationVersement.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/operations/cessions',
    name: 'location-operations-cessions',
    component: () => import('@/app/location/LocationCession.vue'),
  },

  // Préalables Routes
  {
    meta: { permission: 'location.full' },
    path: '/location/prealables',
    name: 'location-prealables',
    component: () => import('@/app/location/MainLocationPrealables.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/prealables/projects',
    name: 'location-prealables-projects',
    component: () => import('@/app/location/LocationProjects.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/prealables/contract-equipments',
    name: 'location-prealables-contract-equipments',
    component: () => import('@/app/location/LocationContractEquipments.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/prealables/equipement',
    name: 'location-prealables-equipement',
    component: () => import('@/app/location/LocationEquipement.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/prealables/locataire',
    name: 'location-prealables-locataire',
    component: () => import('@/app/location/LocationLocataire.vue'),
  },

  // Form Routes - Préalables
  {
    meta: { permission: 'location.full' },
    path: '/location/prealables/projects/nouveau',
    name: 'location-prealables-projects-nouveau',
    component: () => import('@/components/forms/location/NewLocationProject.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/prealables/equipement/nouveau',
    name: 'location-prealables-equipement-nouveau',
    component: () => import('@/components/forms/location/NewLocationEquipement.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/prealables/locataire/nouveau',
    name: 'location-prealables-locataire-nouveau',
    component: () => import('@/components/forms/location/NewLocationLocataire.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/prealables/contract-equipments/nouveau',
    name: 'location-prealables-contract-equipments-nouveau',
    component: () => import('@/components/forms/location/NewLocationContractEquipment.vue'),
  },

  // Form Routes - Opérations
  {
    meta: { permission: 'location.full' },
    path: '/location/operations/contrats/nouveau',
    name: 'location-operations-contrats-nouveau',
    component: () => import('@/components/forms/location/NewLocationContrat.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/operations/versements/nouveau',
    name: 'location-operations-versements-nouveau',
    component: () => import('@/components/forms/location/NewLocationVersement.vue'),
  },
  {
    meta: { permission: 'location.full' },
    path: '/location/operations/cessions/nouveau',
    name: 'location-operations-cessions-nouveau',
    component: () => import('@/components/forms/location/NewLocationCession.vue'),
  },
]

export { locationRoutes }
