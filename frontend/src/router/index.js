import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../views/HomePage.vue'),
  },
  {
    path: '/masters',
    name: 'masters',
    component: () => import('../views/MastersPage.vue'),
  },
  {
    path: '/masters/:id',
    name: 'master-profile',
    component: () => import('../views/MasterProfilePage.vue'),
  },
  {
    path: '/booking',
    name: 'booking',
    component: () => import('../views/BookingPage.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
