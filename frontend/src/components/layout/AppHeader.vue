<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'

const scrolled = ref(false)

function onScroll() {
  scrolled.value = window.scrollY > 50
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <header class="app-header" :class="{ 'app-header--scrolled': scrolled }">
    <div class="app-header__inner">
      <RouterLink to="/" class="app-header__logo">La Bellezza</RouterLink>

      <nav class="app-header__nav">
        <RouterLink to="/">Главная</RouterLink>
        <RouterLink to="/masters">Наши мастера</RouterLink>
        <RouterLink to="/about">О салоне</RouterLink>
      </nav>

      <RouterLink to="/masters" class="app-header__cta">Записаться</RouterLink>
    </div>
  </header>
</template>

<style scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 100;
  transition: background-color 0.3s, backdrop-filter 0.3s;
}

.app-header--scrolled {
  background: var(--color-bg-glass);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--color-border);
}

.app-header__inner {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.app-header__logo {
  font-family: 'Kudry-WeirdDisplay', serif;
  font-size: 1.5rem;
  color: var(--color-accent-gold);
  text-decoration: none;
}

.app-header__nav {
  display: flex;
  gap: 2rem;
}

.app-header__nav a {
  font-family: 'Kudry-SansDisplay', sans-serif;
  text-decoration: none;
  color: var(--color-text-primary);
  transition: color 0.2s;
}

.app-header__nav a:hover,
.app-header__nav a.router-link-active {
  color: var(--color-accent-gold);
}

.app-header__cta {
  font-family: 'Kudry-SansDisplay', sans-serif;
  padding: 0.5rem 1.5rem;
  border-radius: 999px;
  background: var(--color-accent-gold);
  color: var(--color-bg-primary);
  text-decoration: none;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.app-header__cta:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(200, 169, 110, 0.3);
}
</style>
