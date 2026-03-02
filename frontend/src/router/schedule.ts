import type { RouteRecordRaw } from 'vue-router'

const scheduleRoutes: RouteRecordRaw[] = [
  {
    meta: { permission: 'users.view' },
    path: '/schedule',
    redirect: '/schedule/school-planer',
    name: 'schedule',
  },
  {
    meta: { permission: 'users.view' },
    path: '/schedule/school-planer',
    name: 'school-planer',
    component: () => import('@/app/schedule/SchoolPlaner.vue'),
  },
]

export { scheduleRoutes }
