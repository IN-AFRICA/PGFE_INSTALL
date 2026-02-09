import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    meta: { permission: 'users.view' },
    path: '/admin',
    redirect: '/admin/countries',
    //name: '/admin-home',
    //component: () => import('@/app/admin/AdminHome.vue')

    // Commenté pour avoir le composant pays par defaut
  },
  {
    meta: { permission: 'users.view' },
    path: '/admin/countries',
    name: 'pays',
    component: () => import('@/app/admin/location/pays/Pays.vue'),
  },
  {
    meta: { permission: 'users.view' },
    path: '/admin/provinces',
    name: 'provinces',
    component: () => import('@/app/admin/location/provinces/Provinces.vue'),
  },
  {
    meta: { permission: 'users.view' },
    path: '/admin/territoires',
    name: 'territoires',
    component: () => import('@/app/admin/location/territoires/Territoires.vue'),
  },
  {
    meta: { permission: 'users.view' },
    path: '/admin/communes',
    name: 'communes',
    component: () => import('@/app/admin/location/communes/Communes.vue'),
  },
  {
    meta: { permission: 'users.view' },
    path: '/admin/ecoles',
    name: 'ecoles',
    component: () => import('@/app/admin/ecoles/SaisiSchool.vue'),
  },
  {
    meta: { permission: 'users.view' },
    path: '/admin/ecoles/nouveau',
    name: 'nouvelle-ecole',
    component: () => import('@/components/forms/admin/NewSchool.vue'),
  },
  {
    meta: { permission: 'users.view' },
    path: '/admin/ecoles/edit/:id',
    name: 'modifier-ecole',
    component: () => import('@/components/forms/admin/EditSchool.vue'),
  },
  {
    meta: { permission: 'users.view' },
    path: '/admin/type',
    name: 'type',
    component: () => import('@/app/admin/type/Type.vue'),
  },
]

export { adminRoutes }
