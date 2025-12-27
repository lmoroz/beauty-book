<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { X, ChevronLeft, ChevronRight, Check, Calendar, Clock, User, Loader2 } from 'lucide-vue-next'
import { useMastersStore } from '../../stores/masters.js'
import { useBookingsStore } from '../../stores/bookings.js'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  preselectedMaster: { type: Object, default: null },
})

const emit = defineEmits(['update:modelValue', 'booked'])

const mastersStore = useMastersStore()
const bookingsStore = useBookingsStore()

const STEPS = [
  { id: 'master', label: 'Мастер', icon: User },
  { id: 'service', label: 'Услуга', icon: Clock },
  { id: 'datetime', label: 'Дата и время', icon: Calendar },
  { id: 'contacts', label: 'Контакты', icon: User },
  { id: 'confirm', label: 'Подтверждение', icon: Check },
]

const currentStep = ref(0)
const slideDirection = ref('next')

const selectedMaster = ref(null)
const masterServices = ref([])
const selectedService = ref(null)
const selectedDate = ref(null)
const availableSlots = ref([])
const selectedSlot = ref(null)
const loadingSlots = ref(false)
const loadingServices = ref(false)
const submitting = ref(false)
const bookingResult = ref(null)
const bookingError = ref(null)

const clientName = ref('')
const clientPhone = ref('')
const clientEmail = ref('')
const clientNotes = ref('')

const calendarMonth = ref(new Date().getMonth())
const calendarYear = ref(new Date().getFullYear())

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 0: return !!selectedMaster.value
    case 1: return !!selectedService.value
    case 2: return !!selectedSlot.value
    case 3: return clientName.value.trim().length >= 2 && clientPhone.value.replace(/\D/g, '').length >= 11
    case 4: return true
    default: return false
  }
})

const isLastStep = computed(() => currentStep.value === STEPS.length - 1)
const isConfirmed = computed(() => !!bookingResult.value)

const calendarDays = computed(() => {
  const year = calendarYear.value
  const month = calendarMonth.value
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  let startDow = firstDay.getDay()
  if (startDow === 0) startDow = 7
  const days = []

  for (let i = 1; i < startDow; i++) {
    days.push({ day: null, date: null })
  }
  for (let d = 1; d <= lastDay.getDate(); d++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
    const dateObj = new Date(year, month, d)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    days.push({
      day: d,
      date: dateStr,
      past: dateObj < today,
      today: dateObj.getTime() === today.getTime(),
      selected: selectedDate.value === dateStr,
    })
  }
  return days
})

const calendarMonthName = computed(() => {
  const names = [
    'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
    'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь',
  ]
  return `${names[calendarMonth.value]} ${calendarYear.value}`
})

onMounted(() => {
  const saved = localStorage.getItem('beautybook_client')
  if (saved) {
    try {
      const parsed = JSON.parse(saved)
      clientName.value = parsed.name || ''
      clientPhone.value = parsed.phone || ''
      clientEmail.value = parsed.email || ''
    } catch {}
  }
})

watch(() => props.modelValue, (open) => {
  if (open) {
    currentStep.value = 0
    bookingResult.value = null
    bookingError.value = null
    selectedService.value = null
    selectedDate.value = null
    selectedSlot.value = null
    availableSlots.value = []

    if (props.preselectedMaster) {
      selectedMaster.value = props.preselectedMaster
      currentStep.value = 1
      loadServices(props.preselectedMaster.id)
    } else {
      selectedMaster.value = null
      if (!mastersStore.masters.length) mastersStore.fetchMasters()
    }
  }
  document.body.style.overflow = open ? 'hidden' : ''
})

watch(selectedDate, async (date) => {
  if (date && selectedMaster.value) {
    loadingSlots.value = true
    selectedSlot.value = null
    try {
      availableSlots.value = await mastersStore.fetchSchedule(selectedMaster.value.id, date)
    } finally {
      loadingSlots.value = false
    }
  }
})

async function loadServices(masterId) {
  loadingServices.value = true
  try {
    const data = await mastersStore.fetchMasterWithServices(masterId)
    masterServices.value = data?.activeServices || data?.services || []
  } finally {
    loadingServices.value = false
  }
}

function selectMaster(master) {
  selectedMaster.value = master
  selectedService.value = null
  masterServices.value = []
  loadServices(master.id)
}

function selectDate(dateStr) {
  if (!dateStr) return
  const d = new Date(dateStr)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  if (d < today) return
  selectedDate.value = dateStr
}

function prevMonth() {
  if (calendarMonth.value === 0) {
    calendarMonth.value = 11
    calendarYear.value--
  } else {
    calendarMonth.value--
  }
}

function nextMonth() {
  if (calendarMonth.value === 11) {
    calendarMonth.value = 0
    calendarYear.value++
  } else {
    calendarMonth.value++
  }
}

function formatPhone(e) {
  let digits = e.target.value.replace(/\D/g, '')
  if (digits.length > 0 && digits[0] === '8') digits = '7' + digits.slice(1)
  if (digits.length > 11) digits = digits.slice(0, 11)

  let formatted = ''
  if (digits.length >= 1) formatted = '+' + digits[0]
  if (digits.length >= 2) formatted += ' (' + digits.slice(1, 4)
  if (digits.length >= 5) formatted += ') ' + digits.slice(4, 7)
  if (digits.length >= 8) formatted += '-' + digits.slice(7, 9)
  if (digits.length >= 10) formatted += '-' + digits.slice(9, 11)

  clientPhone.value = formatted
}

function goNext() {
  if (!canProceed.value || isLastStep.value) return
  slideDirection.value = 'next'
  currentStep.value++
}

function goBack() {
  if (currentStep.value === 0) return
  slideDirection.value = 'prev'
  currentStep.value--
}

function close() {
  emit('update:modelValue', false)
}

function formatSlotTime(slot) {
  return slot.start_time?.slice(0, 5) + ' – ' + slot.end_time?.slice(0, 5)
}

function formatDateReadable(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr + 'T00:00:00')
  const months = [
    'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
    'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
  ]
  const weekdays = ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота']
  return `${d.getDate()} ${months[d.getMonth()]}, ${weekdays[d.getDay()]}`
}

async function submitBooking() {
  submitting.value = true
  bookingError.value = null
  try {
    localStorage.setItem('beautybook_client', JSON.stringify({
      name: clientName.value,
      phone: clientPhone.value,
      email: clientEmail.value,
    }))

    const result = await bookingsStore.createBooking({
      time_slot_id: selectedSlot.value.id,
      service_id: selectedService.value.id,
      client_name: clientName.value.trim(),
      client_phone: clientPhone.value.trim(),
      client_email: clientEmail.value.trim() || null,
      notes: clientNotes.value.trim() || null,
    })
    bookingResult.value = result
    emit('booked', result)
  } catch (e) {
    bookingError.value = e.response?.data?.message || 'Произошла ошибка. Попробуйте ещё раз.'
    if (e.response?.status === 400) {
      selectedSlot.value = null
      slideDirection.value = 'prev'
      currentStep.value = 2
      if (selectedDate.value && selectedMaster.value) {
        availableSlots.value = await mastersStore.fetchSchedule(selectedMaster.value.id, selectedDate.value)
      }
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modelValue" class="bw-backdrop" @click.self="close">
        <div class="bw glass-panel" @click.stop>
          <!-- Header -->
          <div class="bw__header">
            <button v-if="currentStep > 0 && !isConfirmed" class="bw__back" @click="goBack">
              <ChevronLeft :size="22" :stroke-width="1.5" />
            </button>
            <h2 class="bw__title">
              <template v-if="isConfirmed">Запись подтверждена!</template>
              <template v-else>Онлайн-запись</template>
            </h2>
            <button class="bw__close" @click="close">
              <X :size="22" :stroke-width="1.5" />
            </button>
          </div>

          <!-- Step indicator -->
          <div v-if="!isConfirmed" class="bw__steps">
            <div
              v-for="(step, i) in STEPS"
              :key="step.id"
              class="bw__step-dot"
              :class="{ active: i === currentStep, done: i < currentStep }"
            >
              <div class="bw__step-circle">
                <Check v-if="i < currentStep" :size="14" :stroke-width="2" />
                <span v-else>{{ i + 1 }}</span>
              </div>
              <span class="bw__step-label">{{ step.label }}</span>
            </div>
          </div>

          <!-- Content area -->
          <div class="bw__body">
            <TransitionGroup :name="slideDirection === 'next' ? 'slide-left' : 'slide-right'">
              <!-- Step 0: Master -->
              <div v-if="currentStep === 0" key="step-master" class="bw__panel">
                <p class="bw__hint">Выберите мастера для записи</p>
                <div class="bw__master-grid">
                  <button
                    v-for="m in mastersStore.masters"
                    :key="m.id"
                    class="bw__master-card"
                    :class="{ selected: selectedMaster?.id === m.id }"
                    @click="selectMaster(m)"
                  >
                    <div class="bw__master-avatar">
                      <img v-if="m.photo" :src="m.photo" :alt="m.name" />
                      <span v-else class="bw__master-initials">{{ m.name.split(' ').map(w => w[0]).join('') }}</span>
                    </div>
                    <div class="bw__master-info">
                      <strong>{{ m.name }}</strong>
                      <span class="pill pill-gold">{{ m.specialization }}</span>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Step 1: Service -->
              <div v-if="currentStep === 1" key="step-service" class="bw__panel">
                <p class="bw__hint">Выберите услугу у мастера <strong class="text-gold">{{ selectedMaster?.name }}</strong></p>
                <div v-if="loadingServices" class="bw__loading">
                  <Loader2 :size="24" :stroke-width="1.5" class="bw__spinner" />
                  <span>Загрузка услуг…</span>
                </div>
                <div v-else class="bw__service-list">
                  <button
                    v-for="svc in masterServices"
                    :key="svc.id"
                    class="bw__service-row"
                    :class="{ selected: selectedService?.id === svc.id }"
                    @click="selectedService = svc"
                  >
                    <div class="bw__service-name">{{ svc.name }}</div>
                    <div class="bw__service-meta">
                      <Clock :size="14" :stroke-width="1.5" />
                      {{ svc.duration_min }} мин
                    </div>
                  </button>
                </div>
                <p v-if="!loadingServices && !masterServices.length" class="bw__empty">
                  У мастера пока нет доступных услуг
                </p>
              </div>

              <!-- Step 2: Date & Time -->
              <div v-if="currentStep === 2" key="step-datetime" class="bw__panel">
                <p class="bw__hint">Выберите дату и время</p>

                <!-- Calendar -->
                <div class="bw__calendar">
                  <div class="bw__cal-header">
                    <button @click="prevMonth"><ChevronLeft :size="20" :stroke-width="1.5" /></button>
                    <span class="bw__cal-month">{{ calendarMonthName }}</span>
                    <button @click="nextMonth"><ChevronRight :size="20" :stroke-width="1.5" /></button>
                  </div>
                  <div class="bw__cal-weekdays">
                    <span v-for="d in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']" :key="d">{{ d }}</span>
                  </div>
                  <div class="bw__cal-grid">
                    <button
                      v-for="(cell, i) in calendarDays"
                      :key="i"
                      class="bw__cal-day"
                      :class="{
                        empty: !cell.day,
                        past: cell.past,
                        today: cell.today,
                        selected: cell.selected,
                      }"
                      :disabled="!cell.day || cell.past"
                      @click="selectDate(cell.date)"
                    >
                      {{ cell.day }}
                    </button>
                  </div>
                </div>

                <!-- Slots -->
                <div v-if="selectedDate" class="bw__slots-section">
                  <p class="bw__slots-date">{{ formatDateReadable(selectedDate) }}</p>
                  <div v-if="loadingSlots" class="bw__loading">
                    <Loader2 :size="24" :stroke-width="1.5" class="bw__spinner" />
                    <span>Загрузка слотов…</span>
                  </div>
                  <div v-else-if="availableSlots.length" class="bw__slots-grid">
                    <button
                      v-for="slot in availableSlots"
                      :key="slot.id"
                      class="slot-pill"
                      :class="{ 'slot-pill--selected': selectedSlot?.id === slot.id }"
                      @click="selectedSlot = slot"
                    >
                      {{ formatSlotTime(slot) }}
                    </button>
                  </div>
                  <p v-else class="bw__empty">На эту дату нет свободных слотов</p>
                </div>
              </div>

              <!-- Step 3: Contacts -->
              <div v-if="currentStep === 3" key="step-contacts" class="bw__panel">
                <p class="bw__hint">Ваши контактные данные</p>
                <div class="bw__form">
                  <div class="input-group">
                    <label for="bw-name">Имя *</label>
                    <input
                      id="bw-name"
                      v-model="clientName"
                      class="input-field"
                      type="text"
                      placeholder="Как к вам обращаться"
                      autocomplete="given-name"
                    />
                  </div>
                  <div class="input-group">
                    <label for="bw-phone">Телефон *</label>
                    <input
                      id="bw-phone"
                      :value="clientPhone"
                      class="input-field"
                      type="tel"
                      placeholder="+7 (___) ___-__-__"
                      autocomplete="tel"
                      @input="formatPhone"
                    />
                  </div>
                  <div class="input-group">
                    <label for="bw-email">Email</label>
                    <input
                      id="bw-email"
                      v-model="clientEmail"
                      class="input-field"
                      type="email"
                      placeholder="Для подтверждения (необязательно)"
                      autocomplete="email"
                    />
                  </div>
                  <div class="input-group">
                    <label for="bw-notes">Примечания</label>
                    <textarea
                      id="bw-notes"
                      v-model="clientNotes"
                      class="input-field"
                      rows="3"
                      placeholder="Пожелания к визиту…"
                    />
                  </div>
                </div>
              </div>

              <!-- Step 4: Confirmation -->
              <div v-if="currentStep === 4" key="step-confirm" class="bw__panel">
                <template v-if="isConfirmed">
                  <div class="bw__success">
                    <div class="bw__success-icon">
                      <Check :size="48" :stroke-width="1.5" />
                    </div>
                    <h3>Вы записаны!</h3>
                    <p>Номер записи: <strong class="text-gold">#{{ bookingResult.id }}</strong></p>
                    <p class="text-secondary">Мы свяжемся с вами для подтверждения</p>
                    <button class="btn btn-gold bw__done-btn" @click="close">Готово</button>
                  </div>
                </template>
                <template v-else>
                  <p class="bw__hint">Проверьте данные записи</p>
                  <div class="bw__summary">
                    <div class="bw__summary-row">
                      <span class="bw__summary-label">Мастер</span>
                      <span class="bw__summary-value">{{ selectedMaster?.name }}</span>
                    </div>
                    <div class="bw__summary-row">
                      <span class="bw__summary-label">Услуга</span>
                      <span class="bw__summary-value">{{ selectedService?.name }}</span>
                    </div>
                    <div class="bw__summary-row">
                      <span class="bw__summary-label">Длительность</span>
                      <span class="bw__summary-value">{{ selectedService?.duration_min }} мин</span>
                    </div>
                    <div class="bw__summary-row">
                      <span class="bw__summary-label">Дата</span>
                      <span class="bw__summary-value">{{ formatDateReadable(selectedDate) }}</span>
                    </div>
                    <div class="bw__summary-row">
                      <span class="bw__summary-label">Время</span>
                      <span class="bw__summary-value">{{ formatSlotTime(selectedSlot) }}</span>
                    </div>
                    <div class="bw__summary-divider"></div>
                    <div class="bw__summary-row">
                      <span class="bw__summary-label">Имя</span>
                      <span class="bw__summary-value">{{ clientName }}</span>
                    </div>
                    <div class="bw__summary-row">
                      <span class="bw__summary-label">Телефон</span>
                      <span class="bw__summary-value">{{ clientPhone }}</span>
                    </div>
                    <div v-if="clientEmail" class="bw__summary-row">
                      <span class="bw__summary-label">Email</span>
                      <span class="bw__summary-value">{{ clientEmail }}</span>
                    </div>
                    <div v-if="clientNotes" class="bw__summary-row">
                      <span class="bw__summary-label">Примечания</span>
                      <span class="bw__summary-value">{{ clientNotes }}</span>
                    </div>
                  </div>
                  <p v-if="bookingError" class="bw__error">{{ bookingError }}</p>
                </template>
              </div>
            </TransitionGroup>
          </div>

          <!-- Footer navigation -->
          <div v-if="!isConfirmed" class="bw__footer">
            <div class="bw__footer-hint">
              <span v-if="currentStep === 0">Шаг {{ currentStep + 1 }} из {{ STEPS.length }}</span>
              <span v-else>Шаг {{ currentStep + 1 }} из {{ STEPS.length }}</span>
            </div>
            <button
              v-if="!isLastStep"
              class="btn btn-gold"
              :disabled="!canProceed"
              @click="goNext"
            >
              Далее
              <ChevronRight :size="18" :stroke-width="1.5" />
            </button>
            <button
              v-else
              class="btn btn-gold"
              :disabled="submitting"
              @click="submitBooking"
            >
              <Loader2 v-if="submitting" :size="18" :stroke-width="1.5" class="bw__spinner" />
              <template v-else>
                <Check :size="18" :stroke-width="1.5" />
                Подтвердить запись
              </template>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* ─── Backdrop ─── */
.bw-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  padding: 16px;
}

/* ─── Main panel ─── */
.bw {
  position: relative;
  width: 100%;
  max-width: 560px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ─── Header ─── */
.bw__header {
  display: flex;
  align-items: center;
  padding: 20px 24px 16px;
  gap: 12px;
}

.bw__title {
  flex: 1;
  font-family: var(--font-subheading);
  font-size: 22px;
  font-weight: normal;
}

.bw__back,
.bw__close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.bw__back:hover,
.bw__close:hover {
  color: var(--text-primary);
  background: rgba(255, 255, 255, 0.08);
}

/* ─── Step indicator ─── */
.bw__steps {
  display: flex;
  justify-content: center;
  gap: 4px;
  padding: 0 24px 16px;
}

.bw__step-dot {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  flex: 1;
  max-width: 80px;
}

.bw__step-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-family: var(--font-caption);
  border: 1.5px solid var(--border-subtle);
  color: var(--text-muted);
  transition: all var(--duration-smooth);
}

.bw__step-dot.active .bw__step-circle {
  border-color: var(--accent-gold);
  color: var(--accent-gold);
  box-shadow: 0 0 12px rgba(200, 169, 110, 0.3);
}

.bw__step-dot.done .bw__step-circle {
  border-color: var(--success);
  background: rgba(126, 198, 153, 0.15);
  color: var(--success);
}

.bw__step-label {
  font-family: var(--font-caption);
  font-size: 11px;
  color: var(--text-muted);
  text-align: center;
  transition: color var(--duration-fast);
}

.bw__step-dot.active .bw__step-label {
  color: var(--accent-gold);
}

.bw__step-dot.done .bw__step-label {
  color: var(--text-secondary);
}

/* ─── Body ─── */
.bw__body {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 0 24px;
  position: relative;
  min-height: 300px;
}

.bw__panel {
  width: 100%;
}

.bw__hint {
  font-family: var(--font-body);
  font-size: 15px;
  color: var(--text-secondary);
  margin-bottom: 20px;
}

.bw__loading {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 0;
  color: var(--text-secondary);
  font-family: var(--font-body);
  font-size: 14px;
}

.bw__empty {
  color: var(--text-muted);
  font-family: var(--font-body);
  font-size: 14px;
  text-align: center;
  padding: 32px 0;
}

.bw__error {
  color: var(--error);
  font-family: var(--font-body);
  font-size: 14px;
  margin-top: 16px;
  padding: 12px;
  border-radius: 12px;
  background: rgba(229, 115, 115, 0.1);
  border: 1px solid rgba(229, 115, 115, 0.25);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.bw__spinner {
  animation: spin 0.8s linear infinite;
}

/* ─── Step 0: Master grid ─── */
.bw__master-grid {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.bw__master-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid var(--border-subtle);
  cursor: pointer;
  transition: all var(--duration-fast);
  text-align: left;
  color: var(--text-primary);
}

.bw__master-card:hover {
  border-color: var(--border-gold);
  background: rgba(200, 169, 110, 0.05);
}

.bw__master-card.selected {
  border-color: var(--accent-gold);
  background: rgba(200, 169, 110, 0.1);
  box-shadow: 0 0 20px rgba(200, 169, 110, 0.15);
}

.bw__master-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: var(--bg-glass-strong);
  display: flex;
  align-items: center;
  justify-content: center;
}

.bw__master-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.bw__master-initials {
  font-family: var(--font-subheading);
  font-size: 18px;
  color: var(--accent-gold);
}

.bw__master-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.bw__master-info strong {
  font-family: var(--font-subheading);
  font-size: 16px;
  font-weight: normal;
}

.bw__master-info .pill {
  align-self: flex-start;
}

/* ─── Step 1: Service list ─── */
.bw__service-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.bw__service-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-radius: 14px;
  border: 1px solid var(--border-subtle);
  cursor: pointer;
  transition: all var(--duration-fast);
  text-align: left;
  color: var(--text-primary);
}

.bw__service-row:hover {
  border-color: var(--border-gold);
  background: rgba(200, 169, 110, 0.05);
}

.bw__service-row.selected {
  border-color: var(--accent-gold);
  background: rgba(200, 169, 110, 0.1);
  box-shadow: 0 0 16px rgba(200, 169, 110, 0.12);
}

.bw__service-name {
  font-family: var(--font-body);
  font-size: 15px;
}

.bw__service-meta {
  display: flex;
  align-items: center;
  gap: 5px;
  font-family: var(--font-caption);
  font-size: 13px;
  color: var(--text-secondary);
  white-space: nowrap;
}

/* ─── Step 2: Calendar ─── */
.bw__calendar {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 16px;
  padding: 16px;
  border: 1px solid var(--border-subtle);
  margin-bottom: 20px;
}

.bw__cal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.bw__cal-header button {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.bw__cal-header button:hover {
  color: var(--accent-gold);
  background: rgba(200, 169, 110, 0.1);
}

.bw__cal-month {
  font-family: var(--font-subheading);
  font-size: 16px;
}

.bw__cal-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  margin-bottom: 6px;
}

.bw__cal-weekdays span {
  font-family: var(--font-caption);
  font-size: 12px;
  color: var(--text-muted);
  padding: 4px 0;
}

.bw__cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 3px;
}

.bw__cal-day {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-body);
  font-size: 14px;
  border-radius: 10px;
  cursor: pointer;
  color: var(--text-primary);
  transition: all var(--duration-fast);
}

.bw__cal-day.empty {
  visibility: hidden;
}

.bw__cal-day.past {
  color: var(--text-muted);
  opacity: 0.4;
  cursor: default;
}

.bw__cal-day.today {
  border: 1px solid var(--border-gold);
}

.bw__cal-day:not(.past):not(.empty):hover {
  background: rgba(200, 169, 110, 0.1);
}

.bw__cal-day.selected {
  background: var(--accent-gold) !important;
  color: #1a1a1a;
  font-weight: 600;
}

/* ─── Slots ─── */
.bw__slots-section {
  padding-top: 4px;
}

.bw__slots-date {
  font-family: var(--font-body);
  font-size: 14px;
  color: var(--text-secondary);
  margin-bottom: 12px;
  text-transform: capitalize;
}

.bw__slots-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.slot-pill--selected {
  background: var(--accent-gold) !important;
  color: #1a1a1a !important;
  border-color: var(--accent-gold) !important;
  font-weight: 600;
  transform: scale(1.05);
}

/* ─── Step 3: Form ─── */
.bw__form {
  padding-bottom: 8px;
}

.bw__form textarea.input-field {
  resize: vertical;
  min-height: 60px;
}

/* ─── Step 4: Summary ─── */
.bw__summary {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 16px;
  padding: 20px;
  border: 1px solid var(--border-subtle);
}

.bw__summary-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 8px 0;
}

.bw__summary-row + .bw__summary-row {
  border-top: 1px solid rgba(255, 255, 255, 0.04);
}

.bw__summary-label {
  font-family: var(--font-caption);
  font-size: 13px;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.bw__summary-value {
  font-family: var(--font-body);
  font-size: 15px;
  text-align: right;
  max-width: 60%;
}

.bw__summary-divider {
  height: 1px;
  background: var(--border-gold);
  margin: 12px 0;
  opacity: 0.4;
}

/* ─── Success ─── */
.bw__success {
  text-align: center;
  padding: 32px 0 16px;
}

.bw__success-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(126, 198, 153, 0.2), rgba(126, 198, 153, 0.05));
  border: 2px solid var(--success);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  color: var(--success);
}

.bw__success h3 {
  font-family: var(--font-subheading);
  font-size: 24px;
  margin-bottom: 8px;
}

.bw__success p {
  font-family: var(--font-body);
  font-size: 15px;
  margin-bottom: 6px;
}

.bw__done-btn {
  margin-top: 24px;
}

/* ─── Footer ─── */
.bw__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px 20px;
  border-top: 1px solid var(--border-subtle);
}

.bw__footer-hint {
  font-family: var(--font-caption);
  font-size: 13px;
  color: var(--text-muted);
}

.bw__footer .btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.bw__footer .btn:disabled {
  opacity: 0.4;
  pointer-events: none;
}

/* ─── Modal animation ─── */
.modal-enter-active { transition: all 0.35s var(--ease-out); }
.modal-leave-active { transition: all 0.25s var(--ease-out); }
.modal-enter-from { opacity: 0; }
.modal-enter-from .bw { transform: scale(0.95) translateY(20px); opacity: 0; }
.modal-leave-to { opacity: 0; }
.modal-leave-to .bw { transform: scale(0.97) translateY(10px); opacity: 0; }

/* ─── Step slide transitions ─── */
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all 0.35s var(--ease-out);
}

.slide-left-enter-from { transform: translateX(60px); opacity: 0; }
.slide-left-leave-to { transform: translateX(-60px); opacity: 0; position: absolute; }
.slide-right-enter-from { transform: translateX(-60px); opacity: 0; }
.slide-right-leave-to { transform: translateX(60px); opacity: 0; position: absolute; }

/* ─── Mobile ─── */
@media (max-width: 640px) {
  .bw {
    max-width: 100%;
    max-height: 100vh;
    border-radius: 0;
    height: 100vh;
  }

  .bw-backdrop {
    padding: 0;
  }

  .bw__step-label {
    display: none;
  }

  .bw__steps {
    gap: 12px;
  }
}
</style>
