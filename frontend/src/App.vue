<script setup>
import { RouterView, RouterLink } from 'vue-router'
</script>

<template>
  <header class="app-header">
    <nav>
      <RouterLink to="/">Home</RouterLink>
      <RouterLink to="/masters">Masters</RouterLink>
      <RouterLink to="/booking">Booking</RouterLink>
    </nav>
  </header>

  <RouterView />
</template>

<style scoped>
.app-header {
  padding: 1rem 2rem;
  border-bottom: 1px solid #eee;
}

.app-header nav {
  display: flex;
  gap: 1.5rem;
}

.app-header a {
  text-decoration: none;
  color: inherit;
  font-weight: 500;
}

.app-header a.router-link-active {
  color: #646cff;
}
</style>
