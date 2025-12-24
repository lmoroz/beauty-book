<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'
import { Phone, Menu, X } from 'lucide-vue-next'

const scrolled = ref(false)
const mobileMenuOpen = ref(false)

function onScroll() {
  scrolled.value = window.scrollY > 50
}

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <header class="app-header" :class="{ 'app-header--scrolled': scrolled }">
    <div class="app-header__inner">
      <RouterLink to="/" class="app-header__logo">La Bellezza</RouterLink>

      <nav class="app-header__nav">
        <RouterLink to="/" @click="closeMobileMenu">Главная</RouterLink>
        <RouterLink to="/masters" @click="closeMobileMenu">Наши мастера</RouterLink>
        <RouterLink to="/about" @click="closeMobileMenu">О салоне</RouterLink>
      </nav>

      <div class="app-header__actions">
        <a href="tel:+74951234567" class="app-header__phone">
          <Phone :size="18" :stroke-width="1.5" />
          <span>+7 (495) 123-45-67</span>
        </a>
        <RouterLink to="/masters" class="app-header__cta">Записаться</RouterLink>
      </div>

      <button
        class="app-header__burger"
        aria-label="Меню"
        @click="toggleMobileMenu"
      >
        <component :is="mobileMenuOpen ? X : Menu" :size="24" :stroke-width="1.5" />
      </button>
    </div>

    <Transition name="mobile-menu">
      <div v-if="mobileMenuOpen" class="app-header__mobile-overlay" @click.self="closeMobileMenu">
        <nav class="app-header__mobile-nav">
          <RouterLink to="/" @click="closeMobileMenu">Главная</RouterLink>
          <RouterLink to="/masters" @click="closeMobileMenu">Наши мастера</RouterLink>
          <RouterLink to="/about" @click="closeMobileMenu">О салоне</RouterLink>
          <a href="tel:+74951234567" class="app-header__mobile-phone">
            <Phone :size="18" :stroke-width="1.5" />
            <span>+7 (495) 123-45-67</span>
          </a>
          <RouterLink to="/masters" class="app-header__cta" @click="closeMobileMenu">Записаться</RouterLink>
        </nav>
      </div>
    </Transition>
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

.app-header__actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.app-header__phone {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: 'Kudry-SansText', sans-serif;
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  text-decoration: none;
  transition: color 0.2s;
}

.app-header__phone:hover {
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

.app-header__burger {
  display: none;
  background: none;
  border: none;
  color: var(--color-text-primary);
  cursor: pointer;
  padding: 0.25rem;
  transition: color 0.2s;
}

.app-header__burger:hover {
  color: var(--color-accent-gold);
}

/* Mobile overlay */
.app-header__mobile-overlay {
  position: fixed;
  inset: 0;
  z-index: 99;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
}

.app-header__mobile-nav {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
}

.app-header__mobile-nav a {
  font-family: 'Kudry-SansDisplay', sans-serif;
  font-size: 1.25rem;
  text-decoration: none;
  color: var(--color-text-primary);
  transition: color 0.2s;
}

.app-header__mobile-nav a:hover {
  color: var(--color-accent-gold);
}

.app-header__mobile-phone {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: 'Kudry-SansText', sans-serif;
  color: var(--color-text-secondary);
}

/* Mobile menu transition */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition: opacity 0.3s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .app-header__nav,
  .app-header__actions {
    display: none;
  }

  .app-header__burger {
    display: block;
  }
}
</style>
