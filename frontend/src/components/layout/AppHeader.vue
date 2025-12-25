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
  <header class="header" :class="{ 'header--scrolled': scrolled }">
    <div class="container header__inner">
      <RouterLink to="/" class="header__logo">
        <span>La Bellezza</span>
      </RouterLink>

      <nav class="header__nav">
        <RouterLink to="/" @click="closeMobileMenu">Главная</RouterLink>
        <RouterLink to="/masters" @click="closeMobileMenu">Наши мастера</RouterLink>
        <RouterLink to="/about" @click="closeMobileMenu">О салоне</RouterLink>
      </nav>

      <div class="header__actions">
        <a href="tel:+79991234567" class="header__phone">
          <Phone :size="16" :stroke-width="1.5" />
          <span>+7 (999) 123-45-67</span>
        </a>
        <RouterLink to="/masters" class="btn btn-gold">Записаться</RouterLink>
      </div>

      <button
        class="header__burger"
        aria-label="Меню"
        @click="toggleMobileMenu"
      >
        <component :is="mobileMenuOpen ? X : Menu" :size="24" :stroke-width="1.5" />
      </button>
    </div>

    <Transition name="mobile-menu">
      <div v-if="mobileMenuOpen" class="mobile-menu" @click.self="closeMobileMenu">
        <nav class="mobile-menu__nav">
          <RouterLink to="/" @click="closeMobileMenu">Главная</RouterLink>
          <RouterLink to="/masters" @click="closeMobileMenu">Наши мастера</RouterLink>
          <RouterLink to="/about" @click="closeMobileMenu">О салоне</RouterLink>
          <a href="tel:+79991234567" class="mobile-menu__phone">
            <Phone :size="18" :stroke-width="1.5" />
            <span>+7 (999) 123-45-67</span>
          </a>
          <RouterLink to="/masters" class="btn btn-gold" @click="closeMobileMenu">Записаться</RouterLink>
        </nav>
      </div>
    </Transition>
  </header>
</template>

<style scoped>
.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  padding: 24px 0;
  transition: all var(--duration-smooth) var(--ease-out);
}

.header.header--scrolled {
  padding: 16px 0;
  background: var(--bg-glass);
  backdrop-filter: blur(24px) saturate(140%);
  -webkit-backdrop-filter: blur(24px) saturate(140%);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.header__inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header__logo span {
  font-family: var(--font-brand);
  font-size: 28px;
  background: linear-gradient(135deg, #F5F0EB 0%, #C8A96E 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.header__nav {
  display: flex;
  gap: 32px;
}

.header__nav a {
  font-family: var(--font-nav);
  font-size: 14px;
  color: var(--text-primary);
  text-transform: uppercase;
  letter-spacing: 1px;
  transition: color var(--duration-fast);
}

.header__nav a:hover,
.header__nav a.router-link-exact-active {
  color: var(--accent-gold);
}

.header__actions {
  display: flex;
  align-items: center;
  gap: 24px;
}

.header__phone {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 15px;
  font-family: var(--font-nav);
  letter-spacing: 1px;
  color: var(--text-primary);
  transition: color var(--duration-fast);
}

.header__phone:hover {
  color: var(--accent-gold);
}

.header__burger {
  display: none;
  color: var(--text-primary);
  cursor: pointer;
  padding: 4px;
  transition: color var(--duration-fast);
}

.header__burger:hover {
  color: var(--accent-gold);
}

/* Mobile overlay */
.mobile-menu {
  position: fixed;
  inset: 0;
  z-index: 99;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  display: flex;
  align-items: center;
  justify-content: center;
}

.mobile-menu__nav {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
}

.mobile-menu__nav a {
  font-family: var(--font-nav);
  font-size: 1.25rem;
  color: var(--text-primary);
  text-transform: uppercase;
  letter-spacing: 2px;
  transition: color var(--duration-fast);
}

.mobile-menu__nav a:hover {
  color: var(--accent-gold);
}

.mobile-menu__phone {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: var(--font-body);
  color: var(--text-secondary);
}

/* Transitions */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition: opacity 0.3s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .header__nav,
  .header__actions {
    display: none;
  }

  .header__burger {
    display: block;
  }
}
</style>
