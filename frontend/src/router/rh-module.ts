import type { RouteRecordRaw } from 'vue-router'

const rhModuleRoutes: RouteRecordRaw[] = [
  {
    meta: { permission: 'personnel.module' },
    path: '/rh',
    name: 'rh-module-home',
    component: () => import('../app/rh/MainDashRh.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/personnel',
    name: 'rh-module-personnel',
    component: () => import('../app/rh/ListePersonnel.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/personnel/nouveau',
    name: 'rh-module-nouveau-personnel',
    component: () => import('@/components/forms/rh/AddNewPersonal.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/mise-en-place-personnel',
    name: 'rh-module-mise-en-place-personnel',
    component: () => import('../app/rh/ListeMiseEnPlace.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/mise-en-place-personnel/nouvelle-affectation',
    name: 'rh-module-mise-en-place-personnel-nouvelle-affectation',
    component: () => import('@/components/forms/rh/NouvelleAffectation.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/presence',
    name: 'rh-module-presence',
    component: () => import('../app/rh/ListePresence.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/conge',
    name: 'rh-module-conge',
    component: () => import('../app/rh/ListeConge.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/conge/nouveau-conge',
    name: 'newConge',
    component: () => import('@/components/forms/rh/NouveauConge.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/conge/modifier-conge/:id',
    name: 'editConge',
    component: () => import('@/components/forms/rh/NouveauConge.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/salaire',
    name: 'rh-module-salaire',
    component: () => import('../app/rh/ListeSalaire.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/evaluation',
    name: 'evaluation',
    component: () => import('../app/rh/ListeEvaluation.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/nouvelle-evaluation',
    name: 'newEvaluation',
    component: () => import('@/components/forms/rh/NewEvaluation.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/salaire/nouveau-salaire',
    name: 'rh-module-salaire-nouveau',
    component: () => import('@/components/forms/rh/NouveauPaiement.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/salaire/modifier-salaire/:id',
    name: 'rh-module-salaire-modifier',
    component: () => import('@/components/forms/rh/NouveauPaiement.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/formation-continue',
    name: 'rh-module-formation-continue',
    component: () => import('../app/rh/ListeFormation.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/formation-continue/nouvelle-formation',
    name: 'rh-module-formation-continue-nouvelle',
    component: () => import('@/components/forms/rh/NouvelleFormation.vue'),
  },
  {
    meta: { permission: 'personnel.module' },
    path: '/rh/saisie/formation-continue/modifier-formation/:id',
    name: 'rh-module-formation-continue-modifier',
    component: () => import('@/components/forms/rh/NouvelleFormation.vue'),
  },
]

export { rhModuleRoutes }
