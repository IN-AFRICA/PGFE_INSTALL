import type { RouteRecordRaw } from 'vue-router'

const stockRoutes: RouteRecordRaw[] = [
  {
    meta: { permission: 'stock.full' },
    path: '/stock',
    redirect: '/stock/dashboard',
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/dashboard',
    name: 'stock-dashboard',
    component: () => import('@/app/stock/Dashboard.vue'),
  },
  // Préalables
  {
    meta: { permission: 'stock.full' },
    path: '/stock/prealables/articles',
    name: 'stock-prealables-articles',
    component: () => import('@/app/stock/prealables/Articles.vue'),
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/prealables/fournisseurs',
    name: 'stock-prealables-fournisseurs',
    component: () => import('@/app/stock/prealables/Fournisseurs.vue'),
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/prealables/categories',
    name: 'stock-prealables-categories',
    component: () => import('@/app/stock/prealables/Categories.vue'),
  },
  // Opérations
  {
    meta: { permission: 'stock.full' },
    path: '/stock/operations',
    name: 'stock-operations',
    component: () => import('@/app/stock/prealables/MainStockOperations.vue'),
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/operations/entrees-sorties',
    name: 'stock-operations-entrees-sorties',
    component: () => import('@/app/stock/operations/StockEntreesSorties.vue'),
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/operations/entrees',
    name: 'stock-operations-entrees',
    redirect: '/stock/operations/entrees-sorties',
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/operations/entrees/nouveau',
    name: 'stock-operations-entrees-nouveau',
    component: () => import('@/components/forms/stock/NewEntree.vue'),
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/operations/sorties',
    name: 'stock-operations-sorties',
    redirect: '/stock/operations/entrees-sorties',
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/operations/sorties/nouveau',
    name: 'stock-operations-sorties-nouveau',
    component: () => import('@/components/forms/stock/NewSortie.vue'),
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/operations/sorties/nouveau',
    name: 'stock-operations-sorties-nouveau',
    component: () => import('@/components/forms/stock/NewSortie.vue'),
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/operations/inventories',
    name: 'stock-operations-inventories',
    component: () => import('@/app/stock/operations/Inventories.vue'),
  },
  // Form Routes
  {
    meta: { permission: 'stock.full' },
    path: '/stock/prealables/articles/nouveau',
    name: 'stock-prealables-articles-nouveau',
    component: () => import('@/components/forms/stock/NewArticle.vue'),
  },
  {
    meta: { permission: 'stock.full' },
    path: '/stock/prealables/fournisseurs/nouveau',
    name: 'stock-prealables-fournisseurs-nouveau',
    component: () => import('@/components/forms/stock/NewFournisseur.vue'),
  },
]

export { stockRoutes }
