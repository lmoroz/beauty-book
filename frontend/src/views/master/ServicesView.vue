<script setup>
import { onMounted, computed } from 'vue'
import { LoaderCircle, Clock, Banknote } from 'lucide-vue-next'
import { useDashboardStore } from '../../stores/dashboard.js'

const dashboard = useDashboardStore()

const groupedServices = computed(() => {
  const svcs = dashboard.services
  if (!svcs.length) return []

  const groups = new Map()
  const other = []

  svcs.forEach(s => {
    if (s.category?.name) {
      if (!groups.has(s.category.name)) {
        groups.set(s.category.name, [])
      }
      groups.get(s.category.name).push(s)
    } else {
      other.push(s)
    }
  })

  const result = Array.from(groups.entries()).map(([name, items]) => ({ name, items }))
  if (other.length) result.push({ name: 'Другие', items: other })
  return result
})

function formatDuration(min) {
  const h = Math.floor(min / 60)
  const m = min % 60
  if (h && m) return `${h} ч ${m} мин`
  if (h) return `${h} ч`
  return `${m} мин`
}

function formatPrice(p) {
  return `${p.toLocaleString('ru-RU')} ₽`
}

onMounted(() => {
  dashboard.fetchServices()
})
</script>

<template>
  <section class="services-view">
    <div class="services-view__header">
      <h1>Мои услуги</h1>
    </div>

    <div v-if="dashboard.loading" class="services-view__loading">
      <LoaderCircle :size="24" :stroke-width="1.5" class="spinner" />
      <span>Загрузка услуг…</span>
    </div>

    <div v-else-if="!dashboard.services.length" class="services-view__empty">
      <p>У вас пока нет добавленных услуг</p>
    </div>

    <div v-else class="services-groups">
      <div v-for="group in groupedServices" :key="group.name" class="services-group">
        <h2 class="services-group__label">{{ group.name }}</h2>
        <div class="services-group__list">
          <article v-for="svc in group.items" :key="svc.id" class="service-card glass-panel">
            <div class="service-card__info">
              <h3>{{ svc.name }}</h3>
              <p v-if="svc.description" class="service-card__desc">{{ svc.description }}</p>
            </div>
            <div class="service-card__meta">
              <span class="service-card__duration">
                <Clock :size="14" :stroke-width="1.5" />
                {{ formatDuration(svc.duration_min) }}
              </span>
              <span class="service-card__price">
                <Banknote :size="14" :stroke-width="1.5" />
                {{ formatPrice(svc.price) }}
              </span>
              <span
                class="service-card__status"
                :class="svc.is_active ? 'service-card__status--active' : 'service-card__status--inactive'"
              >
                {{ svc.is_active ? 'Активна' : 'Неактивна' }}
              </span>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.services-view__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.services-view__header h1 {
  font-family: var(--font-heading);
  color: var(--text-primary, #F5F0EB);
  font-size: 32px;
}

.services-view__loading,
.services-view__empty {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 80px 0;
  color: var(--text-secondary, #AFAFAF);
}

.spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.services-groups {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.services-group__label {
  font-family: var(--font-nav);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: var(--text-muted, #777);
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--border-subtle, rgba(255,255,255,0.05));
}

.services-group__list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.service-card {
  padding: 20px 24px;
  border-radius: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  transition: border-color 0.2s;
}

.service-card:hover {
  border-color: var(--border-gold-hover, rgba(200,169,110,0.55));
}

.service-card__info h3 {
  font-family: var(--font-heading);
  font-size: 18px;
  color: var(--text-primary, #F5F0EB);
  margin-bottom: 4px;
  font-weight: 400;
}

.service-card__desc {
  color: var(--text-secondary, #AFAFAF);
  font-size: 14px;
  line-height: 1.5;
}

.service-card__meta {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
}

.service-card__duration,
.service-card__price {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: var(--text-secondary, #AFAFAF);
  font-family: var(--font-caption);
}

.service-card__price {
  color: var(--accent-gold, #DAB97B);
  font-weight: 500;
}

.service-card__status {
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-family: var(--font-caption);
  text-transform: uppercase;
}

.service-card__status--active {
  background: rgba(126, 198, 153, 0.12);
  color: var(--success, #7EC699);
  border: 1px solid rgba(126, 198, 153, 0.25);
}

.service-card__status--inactive {
  background: rgba(255, 255, 255, 0.06);
  color: var(--text-muted, #777);
  border: 1px solid var(--border-subtle, rgba(255,255,255,0.05));
}
</style>
