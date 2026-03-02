import type { RouteRecordRaw } from 'vue-router'

export const insertionRoutes: RouteRecordRaw[] = [
    {
        path: '/insertion',
        redirect: '/insertion/prealables/entreprises',
    },
    {
        path: '/insertion/prealables',
        redirect: '/insertion/prealables/entreprises',
    },
    {
        path: '/insertion/prealables/entreprises',
        name: 'insertion-prealables-entreprises',
        component: () => import('@/app/insertion/prealables/InsertionEntreprises.vue'),
    },
    {
        path: '/insertion/prealables/candidats',
        name: 'insertion-prealables-candidats',
        component: () => import('@/app/insertion/prealables/InsertionCandidats.vue'),
    },
    {
        path: '/insertion/operations',
        redirect: '/insertion/operations/offres',
    },
    {
        path: '/insertion/operations/offres',
        name: 'insertion-operations-offres',
        component: () => import('@/app/insertion/operations/InsertionOffres.vue'),
    },
    {
        path: '/insertion/operations/candidatures',
        name: 'insertion-operations-candidatures',
        component: () => import('@/app/insertion/operations/InsertionCandidatures.vue'),
    },
]