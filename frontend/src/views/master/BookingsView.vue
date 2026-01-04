<script setup>
import { ref, computed, onMounted } from 'vue'
import { LoaderCircle, Calendar, Clock, Phone } from 'lucide-vue-next'
import { useDashboardStore } from '../../stores/dashboard.js'

const dashboard = useDashboardStore()
const filter = ref('upcoming')

const statusLabels = {
  pending: 'Ожидает',
  confirmed: 'Подтверждена',
  completed: 'Завершена',
  cancelled: 'Отменена',
  no_show: 'Не пришёл',
}

const statusClass = {
  pending: 'badge--warning',
  confirmed: 'badge--success',
  completed: 'badge--muted',
  cancelled: 'badge--error',
  no_show: 'badge--error',
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr + 'T00:00:00')
  const months = ['янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек']
  const days = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
  return `${d.getDate()} ${months[d.getMonth()]}, ${days[d.getDay()]}`
}

function formatTime(t) {
  return t ? t.slice(0, 5) : ''
}

function formatPrice(p) {
  return p ? `${p.toLocaleString('ru-RU')} ₽` : ''
}

async function loadBookings() {
  await dashboard.fetchBookings(filter.value)
}

function setFilter(f) {
  filter.value = f
  loadBookings()
}

onMounted(loadBookings)
</script>

<template>
  <section class="bookings-view">
    <div class="bookings-view__header">
      <h1>Записи</h1>
      <div class="bookings-view__filters">
        <button
          class="pill"
          :class="filter === 'upcoming' ? 'pill-gold' : 'pill-outline'"
          @click="setFilter('upcoming')"
        >
          Предстоящие
        </button>
        <button
          class="pill"
          :class="filter === 'past' ? 'pill-gold' : 'pill-outline'"
          @click="setFilter('past')"
        >
          Прошедшие
        </button>
        <button
          class="pill"
          :class="filter === 'all' ? 'pill-gold' : 'pill-outline'"
          @click="setFilter('all')"
        >
          Все
        </button>
      </div>
    </div>

    <div v-if="dashboard.loading" class="bookings-view__loading">
      <LoaderCircle :size="24" :stroke-width="1.5" class="spinner" />
      <span>Загрузка записей…</span>
    </div>

    <div v-else-if="!dashboard.bookings.length" class="bookings-view__empty">
      <p>Нет записей для отображения</p>
    </div>

    <div v-else class="bookings-list">
      <article v-for="b in dashboard.bookings" :key="b.id" class="booking-card glass-panel">
        <div class="booking-card__main">
          <div class="booking-card__client">
            <h3>{{ b.client_name }}</h3>
            <span class="booking-card__phone">
              <Phone :size="14" :stroke-width="1.5" />
              {{ b.client_phone }}
            </span>
          </div>
          <div class="booking-card__service">
            <strong>{{ b.service_name }}</strong>
            <span class="booking-card__price" v-if="b.service_price">{{ formatPrice(b.service_price) }}</span>
          </div>
        </div>
        <div class="booking-card__meta">
          <span class="booking-card__date">
            <Calendar :size="14" :stroke-width="1.5" />
            {{ formatDate(b.date) }}
          </span>
          <span class="booking-card__time">
            <Clock :size="14" :stroke-width="1.5" />
            {{ formatTime(b.start_time) }} – {{ formatTime(b.end_time) }}
          </span>
          <span :class="['badge', statusClass[b.status]]">
            {{ statusLabels[b.status] || b.status }}
          </span>
        </div>
      </article>
    </div>
  </section>
</template>

<style scoped>
.bookings-view__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.bookings-view__header h1 {
  font-family: var(--font-heading);
  color: var(--text-primary, #F5F0EB);
  font-size: 32px;
}

.bookings-view__filters {
  display: flex;
  gap: 8px;
}

.bookings-view__loading,
.bookings-view__empty {
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

.bookings-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.booking-card {
  padding: 20px 24px;
  border-radius: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  transition: border-color 0.2s;
}

.booking-card:hover {
  border-color: var(--border-gold-hover, rgba(200,169,110,0.55));
}

.booking-card__main {
  display: flex;
  gap: 32px;
  align-items: center;
}

.booking-card__client h3 {
  font-family: var(--font-heading);
  font-size: 18px;
  color: var(--text-primary, #F5F0EB);
  margin-bottom: 4px;
}

.booking-card__phone {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--text-muted, #777);
}

.booking-card__service {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.booking-card__service strong {
  color: var(--text-primary, #F5F0EB);
  font-size: 15px;
  font-weight: 400;
}

.booking-card__price {
  color: var(--accent-gold, #DAB97B);
  font-size: 14px;
  font-family: var(--font-caption);
}

.booking-card__meta {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
}

.booking-card__date,
.booking-card__time {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: var(--text-secondary, #AFAFAF);
  font-family: var(--font-caption);
}

.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-family: var(--font-caption);
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.badge--warning {
  background: rgba(255, 193, 7, 0.12);
  color: #ffc107;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.badge--success {
  background: rgba(126, 198, 153, 0.12);
  color: var(--success, #7EC699);
  border: 1px solid rgba(126, 198, 153, 0.25);
}

.badge--error {
  background: rgba(229, 115, 115, 0.12);
  color: var(--error, #E57373);
  border: 1px solid rgba(229, 115, 115, 0.25);
}

.badge--muted {
  background: rgba(255, 255, 255, 0.06);
  color: var(--text-muted, #777);
  border: 1px solid var(--border-subtle, rgba(255,255,255,0.05));
}
</style>
