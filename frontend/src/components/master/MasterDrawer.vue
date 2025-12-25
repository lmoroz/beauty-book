<script setup>
import { computed, watch } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  master: { type: Object, default: null },
  modelValue: { type: Boolean, default: false },
  side: { type: String, default: 'right' },
})

const emit = defineEmits(['update:modelValue', 'book'])

const firstName = computed(() => props.master?.name?.split(' ')[0] || '')

function close() {
  emit('update:modelValue', false)
}

function onKeydown(e) {
  if (e.key === 'Escape' && props.modelValue) close()
}

watch(() => props.modelValue, (open) => {
  if (open) {
    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', onKeydown)
  } else {
    document.body.style.overflow = ''
    document.removeEventListener('keydown', onKeydown)
  }
})
</script>

<template>
  <Teleport to="body">
    <Transition name="drawer-fade">
      <div
        v-if="modelValue"
        class="drawer-backdrop"
        @click.self="close"
      />
    </Transition>

    <Transition :name="side === 'right' ? 'drawer-right' : 'drawer-left'">
      <aside
        v-if="modelValue && master"
        class="drawer"
        :class="`drawer--${side}`"
      >
        <button class="drawer__close" @click="close">
          <X :size="18" :stroke-width="1.5" />
        </button>

        <div class="drawer__scroll">
          <div class="drawer__hero">
            <img :src="master.photo" :alt="master.name" class="drawer__hero-img">
          </div>

          <div class="drawer__content">
            <h2 class="drawer__name">{{ master.name }}</h2>
            <div class="drawer__spec">{{ master.specialization }}</div>

            <div class="drawer__section">
              <h3 class="drawer__section-title">О мастере</h3>
              <p class="drawer__bio">{{ master.fullBio || master.bio }}</p>
            </div>

            <div v-if="master.services?.length" class="drawer__section">
              <h3 class="drawer__section-title">Услуги</h3>
              <div class="drawer__services">
                <div
                  v-for="svc in master.services"
                  :key="svc.name"
                  class="drawer-svc"
                >
                  <span class="drawer-svc__name">{{ svc.name }}</span>
                  <span class="drawer-svc__dur">{{ svc.duration }}</span>
                </div>
              </div>
            </div>

            <div v-if="master.slots?.length" class="drawer__section">
              <h3 class="drawer__section-title">Ближайшие свободные записи</h3>
              <div class="drawer__slots">
                <div
                  v-for="group in master.slots"
                  :key="group.label"
                  class="drawer-slot-group"
                >
                  <span class="drawer-slot-group__label">{{ group.label }}</span>
                  <span
                    v-for="time in group.times"
                    :key="time"
                    class="slot-pill"
                  >{{ time }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="drawer__cta-bar">
          <button class="drawer__cta" @click="$emit('book', master)">
            Записаться к {{ firstName }}
          </button>
        </div>
      </aside>
    </Transition>
  </Teleport>
</template>

<style scoped>
.drawer-backdrop {
  position: fixed;
  inset: 0;
  z-index: 200;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

.drawer {
  position: fixed;
  top: 0;
  width: min(520px, 90vw);
  height: 100vh;
  z-index: 210;
  background: rgba(10, 10, 10, 0.95);
  display: flex;
  flex-direction: column;
}

.drawer--right {
  right: 0;
  border-left: 1px solid var(--border-gold);
}

.drawer--left {
  left: 0;
  border-right: 1px solid var(--border-gold);
}

.drawer__close {
  position: absolute;
  top: 16px;
  right: 16px;
  z-index: 5;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid var(--border-gold);
  background: rgba(10, 10, 10, 0.7);
  backdrop-filter: blur(8px);
  color: var(--text-primary);
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--duration-fast);
}

.drawer__close:hover {
  background: var(--accent-gold);
  color: var(--bg-primary);
  border-color: var(--accent-gold);
}

.drawer__scroll {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(200, 169, 110, 0.3) transparent;
}

.drawer__hero {
  height: 320px;
  overflow: hidden;
}

.drawer__hero-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.drawer__content {
  padding: 1.75rem;
}

.drawer__name {
  font-family: var(--font-heading);
  font-size: 28px;
  margin-bottom: 0.3rem;
}

.drawer__spec {
  font-family: var(--font-caption);
  font-size: 14px;
  color: var(--accent-gold);
  letter-spacing: 0.04em;
  margin-bottom: 1.5rem;
}

.drawer__section {
  margin-bottom: 1.75rem;
}

.drawer__section-title {
  font-family: var(--font-nav);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: var(--text-muted);
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--border-subtle);
}

.drawer__bio {
  font-family: var(--font-body);
  font-size: 15px;
  color: var(--text-secondary);
  line-height: 1.8;
}

.drawer-svc {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.7rem 0.5rem;
  border-bottom: 1px solid var(--border-subtle);
  border-radius: 8px;
  cursor: pointer;
  transition: background var(--duration-fast);
}

.drawer-svc:hover {
  background: var(--bg-glass-strong);
}

.drawer-svc__name {
  font-family: var(--font-body);
  font-size: 15px;
}

.drawer-svc__dur {
  font-family: var(--font-caption);
  font-size: 13px;
  color: var(--text-muted);
}

.drawer-slot-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.drawer-slot-group__label {
  font-family: var(--font-nav);
  font-size: 13px;
  font-weight: 600;
  color: var(--text-secondary);
  min-width: 80px;
}

.drawer__cta-bar {
  padding: 1rem 1.75rem;
  border-top: 1px solid var(--border-gold);
  background: rgba(10, 10, 10, 0.95);
}

.drawer__cta {
  display: block;
  width: 100%;
  text-align: center;
  font-family: var(--font-nav);
  font-size: 16px;
  font-weight: 600;
  padding: 14px 0;
  border-radius: 999px;
  background: linear-gradient(135deg, #f0dab7 0%, #c8a96e 54%, #9f7445 100%);
  color: var(--bg-primary);
  letter-spacing: 0.03em;
  cursor: pointer;
  transition: transform var(--duration-fast), box-shadow var(--duration-fast);
}

.drawer__cta:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(200, 169, 110, 0.35);
}

/* ═══ Transitions ═══ */
.drawer-fade-enter-active,
.drawer-fade-leave-active {
  transition: opacity var(--duration-smooth);
}
.drawer-fade-enter-from,
.drawer-fade-leave-to {
  opacity: 0;
}

.drawer-right-enter-active,
.drawer-right-leave-active,
.drawer-left-enter-active,
.drawer-left-leave-active {
  transition: transform var(--duration-smooth) var(--ease-out);
}

.drawer-right-enter-from,
.drawer-right-leave-to {
  transform: translateX(100%);
}

.drawer-left-enter-from,
.drawer-left-leave-to {
  transform: translateX(-100%);
}

@media (max-width: 768px) {
  .drawer {
    width: 98vw;
  }
}
</style>
