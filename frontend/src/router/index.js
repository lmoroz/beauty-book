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
    path: '/about',
    name: 'about',
    component: () => import('../views/AboutPage.vue'),
  },
  {
    path: '/booking/:id',
    name: 'booking-status',
    component: () => import('../views/BookingStatusPage.vue'),
  },
  {
    path: '/master/login',
    name: 'master-login',
    component: () => import('../views/master/MasterLoginPage.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/master/dashboard',
    name: 'master-dashboard',
    component: () => import('../views/master/MasterDashboardPage.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'master-schedule',
        component: () => import('../views/master/ScheduleView.vue'),
      },
      {
        path: 'bookings',
        name: 'master-bookings',
        component: () => import('../views/master/BookingsView.vue'),
      },
      {
        path: 'services',
        name: 'master-services',
        component: () => import('../views/master/ServicesView.vue'),
      },
      {
        path: 'profile',
        name: 'master-profile',
        component: () => import('../views/master/ProfileView.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.hash) return { el: to.hash, behavior: 'smooth' }
    return { top: 0 }
  },
})

router.beforeEach(async (to) => {
  const token = localStorage.getItem('master_token')

  if (to.meta.requiresAuth && !token) {
    return { name: 'master-login' }
  }

  if (to.meta.guestOnly && token) {
    return { name: 'master-schedule' }
  }
})

export default router
