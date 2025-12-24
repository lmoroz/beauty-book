<script setup>
import { RouterView, RouterLink, useRouter } from 'vue-router'
import { CalendarDays, ClipboardList, Scissors, UserCircle, LogOut } from 'lucide-vue-next'

const router = useRouter()

function logout() {
  localStorage.removeItem('master_token')
  router.push({ name: 'master-login' })
}
</script>

<template>
  <div class="master-dashboard">
    <aside class="master-dashboard__sidebar">
      <div class="master-dashboard__logo">La Bellezza</div>

      <nav>
        <RouterLink :to="{ name: 'master-schedule' }">
          <CalendarDays :size="20" :stroke-width="1.5" />
          <span>Расписание</span>
        </RouterLink>
        <RouterLink :to="{ name: 'master-bookings' }">
          <ClipboardList :size="20" :stroke-width="1.5" />
          <span>Записи</span>
        </RouterLink>
        <RouterLink :to="{ name: 'master-services' }">
          <Scissors :size="20" :stroke-width="1.5" />
          <span>Мои услуги</span>
        </RouterLink>
        <RouterLink :to="{ name: 'master-profile' }">
          <UserCircle :size="20" :stroke-width="1.5" />
          <span>Профиль</span>
        </RouterLink>
      </nav>

      <button class="master-dashboard__logout" @click="logout">
        <LogOut :size="20" :stroke-width="1.5" />
        <span>Выход</span>
      </button>
    </aside>

    <main class="master-dashboard__content">
      <RouterView />
    </main>
  </div>
</template>
