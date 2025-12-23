<script setup>
import { RouterView, RouterLink, useRouter } from 'vue-router'

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
        <RouterLink :to="{ name: 'master-schedule' }">Расписание</RouterLink>
        <RouterLink :to="{ name: 'master-bookings' }">Записи</RouterLink>
        <RouterLink :to="{ name: 'master-services' }">Мои услуги</RouterLink>
        <RouterLink :to="{ name: 'master-profile' }">Профиль</RouterLink>
      </nav>

      <button class="master-dashboard__logout" @click="logout">Выход</button>
    </aside>

    <main class="master-dashboard__content">
      <RouterView />
    </main>
  </div>
</template>
