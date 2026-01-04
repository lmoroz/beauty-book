<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { ChevronLeft, ChevronRight, LoaderCircle, X, Phone, Mail, Clock, Calendar, FileText } from 'lucide-vue-next'
import { useDashboardStore } from '../../stores/dashboard.js'

const dashboard = useDashboardStore()

const weekStart = ref(getMonday(new Date()))
const loading = ref(false)

const bookingDetail = ref(null)
const showDetail = ref(false)
const detailLoading = ref(false)

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

async function handleSlotClick(slot) {
  if (!slot) return

  if (slot.status === 'booked') {
    detailLoading.value = true
    showDetail.value = true
    try {
      bookingDetail.value = await dashboard.fetchBookingDetail(slot.id)
    } catch {
      bookingDetail.value = null
    } finally {
      detailLoading.value = false
    }
    return
  }

  if (slot.status === 'free' || slot.status === 'blocked') {
    try {
      const result = await dashboard.toggleSlot(slot.id)
      slot.status = result.status
    } catch {
      // error is in store
    }
  }
}

function closeDetail() {
  showDetail.value = false
  bookingDetail.value = null
}

function formatDetailDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr + 'T00:00:00')
  const months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря']
  const days = ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота']
  return `${d.getDate()} ${months[d.getMonth()]}, ${days[d.getDay()]}`
}

function formatTime(t) {
  return t ? t.slice(0, 5) : ''
}

function formatPrice(p) {
  return p ? `${p.toLocaleString('ru-RU')} ₽` : ''
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

      <template v-for="time in timeSlots" :key="time">
        <div class="schedule-time">{{ time }}</div>
        <div
          v-for="day in weekDays"
          :key="`${day.date}_${time}`"
          class="schedule-cell"
          :class="{ 'schedule-cell--weekend': day.isWeekend }"
        >
          <div
            v-if="getSlot(day.date, time)"
            :class="getSlotClass(getSlot(day.date, time))"
            @click="handleSlotClick(getSlot(day.date, time))"
          >
            {{ getSlotLabel(getSlot(day.date, time)) }}
          </div>
        </div>
      </template>
    </div>

    <p class="schedule-view__hint">
      Кликните на свободный слот, чтобы заблокировать его. Кликните на бронь, чтобы открыть детали.
    </p>

    <!-- Booking Detail Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showDetail" class="detail-backdrop" @click.self="closeDetail">
          <div class="detail-modal glass-panel">
            <button class="detail-modal__close" @click="closeDetail">
              <X :size="18" :stroke-width="1.5" />
            </button>

            <div v-if="detailLoading" class="detail-modal__loading">
              <LoaderCircle :size="24" :stroke-width="1.5" class="spinner" />
              <span>Загрузка…</span>
            </div>

            <div v-else-if="bookingDetail" class="detail-modal__content">
              <h2>Запись #{{ bookingDetail.id }}</h2>

              <div class="detail-row">
                <span class="detail-label">Клиент</span>
                <span class="detail-value">{{ bookingDetail.client_name }}</span>
              </div>

              <div class="detail-row" v-if="bookingDetail.client_phone">
                <span class="detail-label">
                  <Phone :size="14" :stroke-width="1.5" />
                  Телефон
                </span>
                <a :href="'tel:' + bookingDetail.client_phone" class="detail-value detail-value--link">
                  {{ bookingDetail.client_phone }}
                </a>
              </div>

              <div class="detail-row" v-if="bookingDetail.client_email">
                <span class="detail-label">
                  <Mail :size="14" :stroke-width="1.5" />
                  Email
                </span>
                <span class="detail-value">{{ bookingDetail.client_email }}</span>
              </div>

              <div class="detail-divider"></div>

              <div class="detail-row">
                <span class="detail-label">
                  <Calendar :size="14" :stroke-width="1.5" />
                  Дата
                </span>
                <span class="detail-value">{{ formatDetailDate(bookingDetail.date) }}</span>
              </div>

              <div class="detail-row">
                <span class="detail-label">
                  <Clock :size="14" :stroke-width="1.5" />
                  Время
                </span>
                <span class="detail-value">
                  {{ formatTime(bookingDetail.start_time) }} – {{ formatTime(bookingDetail.end_time) }}
                </span>
              </div>

              <div class="detail-divider"></div>

              <div class="detail-row" v-if="bookingDetail.service">
                <span class="detail-label">Услуга</span>
                <span class="detail-value">{{ bookingDetail.service.name }}</span>
              </div>

              <div class="detail-row" v-if="bookingDetail.service">
                <span class="detail-label">Стоимость</span>
                <span class="detail-value text-gold">{{ formatPrice(bookingDetail.service.price) }}</span>
              </div>

              <div class="detail-row" v-if="bookingDetail.service">
                <span class="detail-label">Длительность</span>
                <span class="detail-value">{{ bookingDetail.service.duration_min }} мин</span>
              </div>

              <div class="detail-row" v-if="bookingDetail.notes">
                <span class="detail-label">
                  <FileText :size="14" :stroke-width="1.5" />
                  Примечания
                </span>
                <span class="detail-value">{{ bookingDetail.notes }}</span>
              </div>
            </div>

            <div v-else class="detail-modal__loading">
              <p>Не удалось загрузить данные</p>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
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
  transition: opacity 0.15s, transform 0.15s;
  user-select: none;
}

.slot:hover {
  opacity: 0.85;
  transform: scale(1.02);
}

.slot:active {
  transform: scale(0.98);
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
  background: rgba(229, 115, 115, 0.1);
  border: 1px dashed var(--error, #E57373);
  color: var(--error, #E57373);
}

.schedule-view__hint {
  margin-top: 16px;
  color: var(--text-secondary, #AFAFAF);
  font-size: 14px;
}

/* Detail Modal */
.detail-backdrop {
  position: fixed;
  inset: 0;
  z-index: 300;
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
}

.detail-modal {
  width: min(480px, 90vw);
  max-height: 85vh;
  overflow-y: auto;
  padding: 32px;
  position: relative;
}

.detail-modal__close {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 1px solid var(--border-gold, rgba(200,169,110,0.25));
  background: rgba(10, 10, 10, 0.7);
  color: var(--text-primary, #F5F0EB);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.detail-modal__close:hover {
  background: var(--accent-gold, #DAB97B);
  color: var(--bg-primary, #0A0A0A);
}

.detail-modal__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 40px 0;
  color: var(--text-secondary, #AFAFAF);
}

.detail-modal__content h2 {
  font-family: var(--font-heading);
  font-size: 24px;
  color: var(--accent-gold, #DAB97B);
  margin-bottom: 24px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
}

.detail-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-caption);
  font-size: 13px;
  color: var(--text-muted, #777);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-value {
  font-size: 15px;
  color: var(--text-primary, #F5F0EB);
}

.detail-value--link {
  color: var(--accent-gold, #DAB97B);
  text-decoration: none;
  transition: opacity 0.15s;
}

.detail-value--link:hover {
  opacity: 0.8;
}

.detail-divider {
  height: 1px;
  background: var(--border-subtle, rgba(255,255,255,0.05));
  margin: 8px 0;
}

/* Modal transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .detail-modal,
.modal-leave-active .detail-modal {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .detail-modal,
.modal-leave-to .detail-modal {
  transform: scale(0.9) translateY(20px);
  opacity: 0;
}
</style>
