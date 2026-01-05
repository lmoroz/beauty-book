<script setup>
import { onMounted, onUnmounted, computed } from 'vue'
import { RouterView, RouterLink, useRouter, useRoute } from 'vue-router'
import { CalendarDays, ClipboardList, Scissors, UserCircle, LogOut } from 'lucide-vue-next'
import { useAuthStore } from '../../stores/auth.js'
import { useDashboardStore } from '../../stores/dashboard.js'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const dashboard = useDashboardStore()

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
    <aside class="dash-sidebar">
      <div class="dash-sidebar__logo">La Bellezza</div>

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
        <RouterLink :to="{ name: 'master-schedule' }" :class="{ active: route.name === 'master-schedule' }">
          <CalendarDays :size="20" :stroke-width="1.5" />
          <span>Расписание</span>
        </RouterLink>
        <RouterLink :to="{ name: 'master-bookings' }" :class="{ active: route.name === 'master-bookings' }">
          <ClipboardList :size="20" :stroke-width="1.5" />
          <span>Записи</span>
        </RouterLink>
        <RouterLink :to="{ name: 'master-services' }" :class="{ active: route.name === 'master-services' }">
          <Scissors :size="20" :stroke-width="1.5" />
          <span>Мои услуги</span>
        </RouterLink>
        <RouterLink :to="{ name: 'master-profile' }" :class="{ active: route.name === 'master-profile' }">
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
</style>
