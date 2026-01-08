<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { RouterView, RouterLink, useRouter, useRoute } from 'vue-router'
import { CalendarDays, ClipboardList, Scissors, UserCircle, LogOut, Menu, X } from 'lucide-vue-next'
import { useAuthStore } from '../../stores/auth.js'
import { useDashboardStore } from '../../stores/dashboard.js'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const dashboard = useDashboardStore()

const sidebarOpen = ref(false)

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

function closeSidebar() {
  sidebarOpen.value = false
}

const apiBase = (import.meta.env.VITE_API_URL || '/api/v1').replace(/\/api\/v1\/?$/, '')

function photoUrl(photo) {
  if (!photo) return null
  if (photo.startsWith('http')) return photo
  return apiBase + photo
}

const masterPhoto = computed(() => {
  if (dashboard.profile?.photo) return photoUrl(dashboard.profile.photo)
  return null
})

const masterName = computed(() => dashboard.profile?.name || auth.user?.username || 'Мастер')
const masterSpec = computed(() => {
  const specs = dashboard.profile?.specializations
  return specs?.length ? specs.map(s => s.name).join(' · ') : ''
})

function logout() {
  dashboard.unsubscribeSSE()
  auth.logout()
  router.push({ name: 'master-login' })
}

onMounted(async () => {
  if (!auth.user) {
    await auth.fetchUser()
  }
  dashboard.fetchProfile()

  const masterId = auth.masterId
  if (masterId) {
    dashboard.subscribeSSE(masterId)
  }
})

onUnmounted(() => {
  dashboard.unsubscribeSSE()
})
</script>

<template>
  <div class="dashboard-body">
    <!-- Mobile top bar -->
    <header class="dash-topbar">
      <button class="dash-topbar__burger" @click="toggleSidebar" aria-label="Меню">
        <Menu :size="24" :stroke-width="1.5" />
      </button>
      <span class="dash-topbar__logo">La Bellezza</span>
    </header>

    <!-- Sidebar backdrop (mobile) -->
    <Transition name="sidebar-fade">
      <div v-if="sidebarOpen" class="dash-sidebar-backdrop" @click="closeSidebar" />
    </Transition>

    <!-- Sidebar -->
    <aside class="dash-sidebar" :class="{ 'dash-sidebar--open': sidebarOpen }">
      <div class="dash-sidebar__top">
        <div class="dash-sidebar__logo">La Bellezza</div>
        <button class="dash-sidebar__close" @click="closeSidebar" aria-label="Закрыть">
          <X :size="20" :stroke-width="1.5" />
        </button>
      </div>

      <div class="dash-sidebar__profile">
        <div class="dash-sidebar__avatar">
          <img v-if="masterPhoto" :src="masterPhoto" :alt="masterName">
          <span v-else class="dash-sidebar__initials">
            {{ masterName.split(' ').map(w => w[0]).join('').toUpperCase() }}
          </span>
        </div>
        <div class="dash-sidebar__info">
          <div class="dash-sidebar__name">{{ masterName }}</div>
          <div class="dash-sidebar__spec">{{ masterSpec }}</div>
        </div>
      </div>

      <nav class="dash-nav">
        <RouterLink :to="{ name: 'master-schedule' }" :class="{ active: route.name === 'master-schedule' }" @click="closeSidebar">
          <CalendarDays :size="20" :stroke-width="1.5" />
          <span>Расписание</span>
        </RouterLink>
        <RouterLink :to="{ name: 'master-bookings' }" :class="{ active: route.name === 'master-bookings' }" @click="closeSidebar">
          <ClipboardList :size="20" :stroke-width="1.5" />
          <span>Записи</span>
        </RouterLink>
        <RouterLink :to="{ name: 'master-services' }" :class="{ active: route.name === 'master-services' }" @click="closeSidebar">
          <Scissors :size="20" :stroke-width="1.5" />
          <span>Мои услуги</span>
        </RouterLink>
        <RouterLink :to="{ name: 'master-profile' }" :class="{ active: route.name === 'master-profile' }" @click="closeSidebar">
          <UserCircle :size="20" :stroke-width="1.5" />
          <span>Профиль</span>
        </RouterLink>
      </nav>

      <button class="dash-sidebar__logout" @click="logout">
        <LogOut :size="20" :stroke-width="1.5" />
        <span>Выход</span>
      </button>
    </aside>

    <main class="dash-main">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
/* ═══ Mobile top bar (hidden on desktop) ═══ */
.dash-topbar {
  display: none;
}

/* ═══ Sidebar close button (hidden on desktop) ═══ */
.dash-sidebar__close {
  display: none;
}

/* ═══ Layout ═══ */
.dashboard-body {
  display: flex;
  flex-direction: row;
  height: 100vh;
  overflow: hidden;
  background-color: var(--bg-primary, #0A0A0A);
}

.dash-sidebar {
  width: 280px;
  background: #111111;
  border-right: 1px solid var(--border-subtle, rgba(255,255,255,0.05));
  display: flex;
  flex-direction: column;
  padding: 32px 0;
  flex-shrink: 0;
}

.dash-sidebar__top {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.dash-sidebar__logo {
  font-family: var(--font-brand);
  font-size: 24px;
  color: var(--accent-gold, #DAB97B);
  text-align: center;
  margin-bottom: 40px;
}

.dash-sidebar__profile {
  padding: 0 24px;
  margin-bottom: 32px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.dash-sidebar__avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 1px solid var(--border-gold, rgba(200,169,110,0.25));
  overflow: hidden;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(160deg, rgba(200,169,110,0.32), rgba(212,160,160,0.28));
}

.dash-sidebar__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.dash-sidebar__initials {
  font-family: var(--font-heading);
  font-size: 16px;
  color: var(--accent-gold, #DAB97B);
}

.dash-sidebar__name {
  font-family: var(--font-heading);
  color: var(--text-primary, #F5F0EB);
  font-size: 16px;
}

.dash-sidebar__spec {
  font-family: var(--font-caption);
  color: var(--text-secondary, #AFAFAF);
  font-size: 12px;
}

.dash-nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 0 16px;
}

.dash-nav a {
  padding: 12px 20px;
  border-radius: 12px;
  color: var(--text-secondary, #AFAFAF);
  font-family: var(--font-nav);
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 15px;
  text-decoration: none;
}

.dash-nav a:hover,
.dash-nav a.active,
.dash-nav a.router-link-exact-active {
  background: var(--bg-glass-strong, rgba(200,169,110,0.15));
  color: var(--accent-gold, #DAB97B);
}

.dash-sidebar__logout {
  margin-top: auto;
  margin-left: 16px;
  margin-right: 16px;
  padding: 12px 20px;
  border-radius: 12px;
  color: var(--error, #E57373);
  font-family: var(--font-nav);
  font-size: 15px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: background 0.2s;
}

.dash-sidebar__logout:hover {
  background: rgba(229, 115, 115, 0.1);
}

.dash-main {
  flex: 1;
  overflow-y: auto;
  padding: 40px;
  background: #0A0A0A;
}

/* ═══ Sidebar backdrop (mobile only) ═══ */
.dash-sidebar-backdrop {
  display: none;
}

.sidebar-fade-enter-active,
.sidebar-fade-leave-active {
  transition: opacity 0.25s ease;
}
.sidebar-fade-enter-from,
.sidebar-fade-leave-to {
  opacity: 0;
}

/* ═══ MOBILE ═══ */
@media (max-width: 768px) {
  .dash-topbar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 16px;
    background: #111111;
    border-bottom: 1px solid var(--border-subtle, rgba(255,255,255,0.05));
    position: sticky;
    top: 0;
    z-index: 50;
  }

  .dash-topbar__burger {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1px solid var(--border-subtle, rgba(255,255,255,0.08));
    background: rgba(255,255,255,0.04);
    color: var(--text-primary, #F5F0EB);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
  }

  .dash-topbar__burger:hover {
    background: rgba(255,255,255,0.08);
  }

  .dash-topbar__logo {
    font-family: var(--font-brand);
    font-size: 20px;
    color: var(--accent-gold, #DAB97B);
  }

  .dashboard-body {
    flex-direction: column;
    height: 100vh;
    overflow-y: auto;
    overflow-x: hidden;
  }

  .dash-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 280px;
    z-index: 200;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding-top: 24px;
  }

  .dash-sidebar--open {
    transform: translateX(0);
  }

  .dash-sidebar__close {
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 24px;
    right: 16px;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--border-subtle, rgba(255,255,255,0.08));
    background: rgba(255,255,255,0.04);
    color: var(--text-secondary, #AFAFAF);
    cursor: pointer;
    transition: background 0.2s;
  }

  .dash-sidebar__close:hover {
    background: rgba(255,255,255,0.08);
  }

  .dash-sidebar-backdrop {
    display: block;
    position: fixed;
    inset: 0;
    z-index: 150;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
  }

  .dash-main {
    padding: 20px 16px;
    overflow-y: visible;
    overflow-x: hidden;
    min-width: 0;
    min-height: 0;
  }
}
</style>
