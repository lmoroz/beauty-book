<script setup>
import { ref, computed, watch } from 'vue'
import { X, Loader2 } from 'lucide-vue-next'
import { useMastersStore } from '../../stores/masters.js'

const props = defineProps({
  master: { type: Object, default: null },
  modelValue: { type: Boolean, default: false },
  side: { type: String, default: 'right' },
})

const emit = defineEmits(['update:modelValue', 'book'])

const mastersStore = useMastersStore()
const services = ref([])
const slots = ref([])
const loadingServices = ref(false)
const loadingSlots = ref(false)

const firstName = computed(() => props.master?.name?.split(' ')[0] || '')

const initials = computed(() =>
  (props.master?.name || '')
    .split(' ')
    .map(w => w[0])
    .join('')
    .toUpperCase()
)

const isApiMaster = computed(() =>
  props.master && typeof props.master.id === 'number'
)

const displayServices = computed(() => {
  if (props.master?.services?.length) return props.master.services
  return services.value
})

const displaySlots = computed(() => {
  if (props.master?.slots?.length) return props.master.slots
  return slots.value
})

function formatDuration(svc) {
  if (svc.duration) return svc.duration
  if (svc.duration_min) {
    const h = Math.floor(svc.duration_min / 60)
    const m = svc.duration_min % 60
    if (h && m) return `${h} ч ${m} мин`
    if (h) return `${h} ч`
    return `${m} мин`
  }
  return ''
}

function close() {
  emit('update:modelValue', false)
}

function onKeydown(e) {
  if (e.key === 'Escape' && props.modelValue) close()
}

async function loadApiData() {
  if (!isApiMaster.value) return

  loadingServices.value = true
  try {
    const data = await mastersStore.fetchMasterWithServices(props.master.id)
    services.value = data?.activeServices || data?.services || []
  } finally {
    loadingServices.value = false
  }

  loadingSlots.value = true
  try {
    const today = new Date()
    const grouped = []
    const dayLabels = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
    for (let d = 0; d < 3; d++) {
      const date = new Date(today)
      date.setDate(today.getDate() + d)
      const dateStr = date.toISOString().slice(0, 10)
      const fetched = await mastersStore.fetchSchedule(props.master.id, dateStr)
      if (fetched.length) {
        const label = d === 0 ? 'Сегодня:' : d === 1 ? 'Завтра:' : `${dayLabels[date.getDay()]}:`
        grouped.push({
          label,
          times: fetched.slice(0, 5).map(s => s.start_time?.slice(0, 5)),
        })
      }
    }
    slots.value = grouped
  } finally {
    loadingSlots.value = false
  }
}

watch(() => props.modelValue, (open) => {
  if (open) {
    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', onKeydown)
    services.value = []
    slots.value = []
    loadApiData()
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
            <img v-if="master.photo" :src="master.photo" :alt="master.name" class="drawer__hero-img">
            <div v-else class="drawer__hero-placeholder">
              <span class="drawer__hero-initials">{{ initials }}</span>
            </div>
          </div>

          <div class="drawer__content">
            <h2 class="drawer__name">{{ master.name }}</h2>
            <div class="drawer__spec">{{ master.specialization }}</div>

            <div class="drawer__section">
              <h3 class="drawer__section-title">О мастере</h3>
              <p class="drawer__bio">{{ master.fullBio || master.bio }}</p>
            </div>

            <div v-if="displayServices.length || loadingServices" class="drawer__section">
              <h3 class="drawer__section-title">Услуги</h3>
              <div v-if="loadingServices" class="drawer__loading">
                <Loader2 :size="18" :stroke-width="1.5" class="drawer__spinner" />
                <span>Загрузка…</span>
              </div>
              <div v-else class="drawer__services">
                <div
                  v-for="svc in displayServices"
                  :key="svc.name || svc.id"
                  class="drawer-svc"
                >
                  <span class="drawer-svc__name">{{ svc.name }}</span>
                  <span class="drawer-svc__dur">{{ formatDuration(svc) }}</span>
                </div>
              </div>
            </div>

            <div v-if="displaySlots.length || loadingSlots" class="drawer__section">
              <h3 class="drawer__section-title">Ближайшие свободные записи</h3>
              <div v-if="loadingSlots" class="drawer__loading">
                <Loader2 :size="18" :stroke-width="1.5" class="drawer__spinner" />
                <span>Загрузка…</span>
              </div>
              <div v-else class="drawer__slots">
                <div
                  v-for="group in displaySlots"
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
            Записаться
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

.drawer__hero-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(200, 169, 110, 0.15), rgba(200, 169, 110, 0.05));
}

.drawer__hero-initials {
  font-family: var(--font-brand);
  font-size: 72px;
  color: var(--accent-gold);
  opacity: 0.6;
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

.drawer__loading {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 0;
  color: var(--text-muted);
  font-family: var(--font-body);
  font-size: 14px;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.drawer__spinner {
  animation: spin 0.8s linear infinite;
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
