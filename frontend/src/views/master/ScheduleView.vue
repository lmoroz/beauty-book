<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { ChevronLeft, ChevronRight, LoaderCircle } from 'lucide-vue-next'
import { useDashboardStore } from '../../stores/dashboard.js'

const dashboard = useDashboardStore()

const weekStart = ref(getMonday(new Date()))
const loading = ref(false)

function getMonday(d) {
  const date = new Date(d)
  const day = date.getDay()
  const diff = date.getDate() - day + (day === 0 ? -6 : 1)
  date.setDate(diff)
  return formatDate(date)
}

function formatDate(d) {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const dd = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${dd}`
}

const dayNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
const today = formatDate(new Date())

const weekDays = computed(() => {
  const start = new Date(weekStart.value + 'T00:00:00')
  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(start)
    d.setDate(start.getDate() + i)
    return {
      date: formatDate(d),
      label: `${dayNames[i]} ${d.getDate()}`,
      isToday: formatDate(d) === today,
      isWeekend: i >= 5,
    }
  })
})

const weekLabel = computed(() => {
  const months = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек']
  const start = new Date(weekStart.value + 'T00:00:00')
  const end = new Date(start)
  end.setDate(start.getDate() + 6)
  const sm = months[start.getMonth()]
  const em = months[end.getMonth()]
  return `${start.getDate()} ${sm} – ${end.getDate()} ${em}`
})

const timeSlots = computed(() => {
  const slots = dashboard.schedule?.slots || []
  const times = new Set()
  slots.forEach(s => times.add(s.start_time?.slice(0, 5)))

  if (!times.size) {
    return ['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00']
  }
  return Array.from(times).sort()
})

const slotMap = computed(() => {
  const slots = dashboard.schedule?.slots || []
  const map = {}
  slots.forEach(s => {
    const key = `${s.date}_${s.start_time?.slice(0, 5)}`
    map[key] = s
  })
  return map
})

function getSlot(date, time) {
  return slotMap.value[`${date}_${time}`] || null
}

function getSlotClass(slot) {
  if (!slot) return ''
  if (slot.status === 'booked') return 'slot slot--booked'
  if (slot.status === 'blocked') return 'slot slot--blocked'
  if (slot.status === 'free') return 'slot slot--free'
  return 'slot'
}

function getSlotLabel(slot) {
  if (!slot) return ''
  if (slot.status === 'booked' && slot.booking) {
    return `${slot.booking.client_name} (${slot.booking.service_name || ''})`
  }
  if (slot.status === 'blocked') return 'Заблокировано'
  if (slot.status === 'free') return 'Свободно'
  return ''
}

function prevWeek() {
  const d = new Date(weekStart.value + 'T00:00:00')
  d.setDate(d.getDate() - 7)
  weekStart.value = formatDate(d)
}

function nextWeek() {
  const d = new Date(weekStart.value + 'T00:00:00')
  d.setDate(d.getDate() + 7)
  weekStart.value = formatDate(d)
}

async function loadSchedule() {
  loading.value = true
  await dashboard.fetchSchedule(weekStart.value)
  loading.value = false
}

watch(weekStart, loadSchedule)
onMounted(loadSchedule)
</script>

<template>
  <section class="schedule-view">
    <div class="schedule-view__header">
      <h1>Расписание</h1>
      <div class="schedule-view__nav">
        <button class="btn btn-outline btn-sm" @click="prevWeek">
          <ChevronLeft :size="18" :stroke-width="1.5" />
          <span>Назад</span>
        </button>
        <span class="schedule-view__week-label">{{ weekLabel }}</span>
        <button class="btn btn-outline btn-sm" @click="nextWeek">
          <span>Вперед</span>
          <ChevronRight :size="18" :stroke-width="1.5" />
        </button>
      </div>
    </div>

    <div v-if="loading" class="schedule-view__loading">
      <LoaderCircle :size="24" :stroke-width="1.5" class="spinner" />
      <span>Загрузка расписания…</span>
    </div>

    <div v-else class="schedule-grid">
      <!-- Header Row -->
      <div class="schedule-header schedule-header--corner"></div>
      <div
        v-for="day in weekDays"
        :key="day.date"
        class="schedule-header"
        :class="{
          'schedule-header--today': day.isToday,
          'schedule-header--weekend': day.isWeekend,
        }"
      >
        {{ day.label }}
      </div>

      <!-- Time Rows -->
      <template v-for="time in timeSlots" :key="time">
        <div class="schedule-time">{{ time }}</div>
        <div
          v-for="day in weekDays"
          :key="`${day.date}_${time}`"
          class="schedule-cell"
          :class="{ 'schedule-cell--weekend': day.isWeekend }"
        >
          <div v-if="getSlot(day.date, time)" :class="getSlotClass(getSlot(day.date, time))">
            {{ getSlotLabel(getSlot(day.date, time)) }}
          </div>
        </div>
      </template>
    </div>

    <p class="schedule-view__hint">
      Кликните на свободный слот, чтобы заблокировать его. Кликните на бронь, чтобы открыть детали.
    </p>
  </section>
</template>

<style scoped>
.schedule-view__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.schedule-view__header h1 {
  font-family: var(--font-heading);
  color: var(--text-primary, #F5F0EB);
  font-size: 32px;
}

.schedule-view__nav {
  display: flex;
  gap: 12px;
  align-items: center;
}

.schedule-view__week-label {
  font-family: var(--font-heading);
  font-size: 20px;
  color: var(--text-primary, #F5F0EB);
  min-width: 180px;
  text-align: center;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 14px;
  gap: 4px;
}

.schedule-view__loading {
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

.schedule-grid {
  display: grid;
  grid-template-columns: 80px repeat(7, 1fr);
  gap: 1px;
  background: var(--border-subtle, rgba(255,255,255,0.05));
  border: 1px solid var(--border-subtle, rgba(255,255,255,0.05));
  border-radius: 16px;
  overflow: hidden;
}

.schedule-header,
.schedule-cell {
  background: #0A0A0A;
  padding: 16px;
}

.schedule-header {
  text-align: center;
  font-family: var(--font-heading);
  color: var(--accent-gold, #DAB97B);
  background: #111111;
  font-size: 15px;
}

.schedule-header--corner {
  background: transparent;
}

.schedule-header--today {
  color: var(--text-primary, #F5F0EB);
  border-bottom: 2px solid var(--accent-gold, #DAB97B);
}

.schedule-header--weekend {
  color: var(--text-muted, #777);
}

.schedule-time {
  text-align: right;
  color: var(--text-muted, #777);
  font-size: 13px;
  background: #111111;
  padding: 16px 12px;
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
}

.schedule-cell--weekend {
  background: var(--bg-secondary, rgba(10,10,10,0.6));
}

.slot {
  border-radius: 4px;
  padding: 8px;
  font-size: 12px;
  margin: 4px 0;
  cursor: pointer;
  font-family: var(--font-caption);
  transition: opacity 0.15s;
}

.slot:hover {
  opacity: 0.85;
}

.slot--free {
  background: rgba(126, 198, 153, 0.1);
  border: 1px solid var(--success, #7EC699);
  color: var(--success, #7EC699);
}

.slot--booked {
  background: rgba(200, 169, 110, 0.1);
  border: 1px solid var(--accent-gold, #DAB97B);
  color: var(--accent-gold, #DAB97B);
}

.slot--blocked {
  background: rgba(255, 100, 100, 0.1);
  border: 1px dashed var(--error, #E57373);
  color: var(--error, #E57373);
}

.schedule-view__hint {
  margin-top: 16px;
  color: var(--text-secondary, #AFAFAF);
  font-size: 14px;
}
</style>
