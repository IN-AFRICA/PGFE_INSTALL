import type { RouteRecordRaw } from 'vue-router'

const moduleComptaRoutes: RouteRecordRaw[] = [
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite',
    component: () => import('@/app/comptabilite/MainDashCompta.vue'),
  },

  // SAISIE PREALABLE
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-prealable',
    component: () => import('@/app/comptabilite/prealables/ListCompteBanque.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-prealable/plan-comptable',
    component: () => import('@/app/comptabilite/prealables/PlanComptable.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-prealable/plan-analytique',
    component: () => import('@/app/comptabilite/prealables/PlanAnalytique.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-prealable/budget',
    component: () => import('@/app/comptabilite/prealables/ListBudget.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-prealable/a-nouveaux',
    component: () => import('@/app/comptabilite/prealables/A_Nouveau.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-prealable/rubriques-analytiques',
    component: () => import('@/app/comptabilite/prealables/RubriquesAnalytiques.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-prealable/lignes-budgetaires',
    component: () => import('@/app/comptabilite/prealables/LigneBudgetaire.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-prealable/class-comptable',
    component: () => import('@/app/comptabilite/prealables/ClassComptable.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/cloture-exercice',
    component: () => import('@/app/comptabilite/operations/new_exercice_cloture.vue'),
  },

  // SAISIE OPERATIONS

  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations',
    component: () => import('@/app/comptabilite/operations/Journal.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/recettes',
    component: () => import('@/app/comptabilite/operations/inputAndOuput.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/nouvelle-sortie',
    component: () => import('@/app/comptabilite/operations/NewOutput.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/nouvelle-entree',
    component: () => import('@/app/comptabilite/operations/NewInput.vue'),
  },

  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/nouveau-journal',
    component: () => import('@/app/comptabilite/operations/NewJournal.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/amortissements',
    component: () => import('@/app/comptabilite/operations/Amortissement.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/nouvel-amortissement',
    component: () => import('@/app/comptabilite/operations/New_Amortissement.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/immobilisations',
    component: () => import('@/app/comptabilite/operations/Immobilisations.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/nouvelle-immobilisation',
    component: () => import('@/app/comptabilite/operations/New_Immobilisation.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/upate-immobilisation',
    alias: '/comptabilite/update-immobilisation',
    component: () => import('@/app/comptabilite/operations/New_Immobilisation.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/saisie-operations/exercice-cloture',
    component: () => import('@/app/comptabilite/operations/new_exercice_cloture.vue'),
  },

  // FRAIS SCOLAIRES

  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/frais',
    name: 'comptabilite-frais',
    component: () => import('@/app/comptabilite/frais/GestionFraisScolaires.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/frais/types-frais',
    name: 'comptabilite-frais-types',
    component: () => import('@/app/comptabilite/frais/Gestion_type_frais.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/frais/devises',
    name: 'comptabilite-frais-devises',
    component: () => import('@/app/comptabilite/frais/GestionDevises.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/frais/paiements',
    name: 'comptabilite-frais-paiements',
    component: () => import('@/app/comptabilite/frais/Gestion_paiements.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/frais/paiements/nouveau',
    name: 'comptabilite-frais-nouveau-paiement',
    component: () => import('@/app/comptabilite/frais/NouveauPaiement.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/frais/motifs-paiement',
    name: 'comptabilite-frais-motifs-paiement',
    component: () => import('@/app/comptabilite/frais/Gestion_paiements_motifi.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/frais/methodes-paiement',
    name: 'comptabilite-frais-methodes-paiement',
    component: () => import('@/app/comptabilite/frais/Gestion_paiements_methodes.vue'),
  },
  {
    meta: { permission: 'accounting.module' },
    path: '/comptabilite/frais/taux-de-change',
    name: 'comptabilite-frais-taux-change',
    component: () => import('@/app/comptabilite/frais/Gestion_taux_echange.vue'),
  },
]

export { moduleComptaRoutes }
