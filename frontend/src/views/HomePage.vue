<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import MasterCard from '../components/master/MasterCard.vue'
import MasterDrawer from '../components/master/MasterDrawer.vue'
import BookingWizard from '../components/booking/BookingWizard.vue'
import heroUrl from '../assets/images/hero_bg.png'

const selectedMaster = ref(null)
const drawerOpen = ref(false)
const drawerSide = ref('right')
const wizardOpen = ref(false)
const wizardMaster = ref(null)

const masters = ref([
  {
    id: 'anna',
    name: 'Анна Петрова',
    specialization: 'Топ-стилист · Колорист',
    tags: ['Топ-стилист'],
    bio: 'Эксперт по сложным окрашиваниям и модельным стрижкам. Более 10 лет опыта работы в индустрии красоты премиум-сегмента.',
    fullBio: 'Эксперт по сложным окрашиваниям и модельным стрижкам. Более 10 лет опыта работы в индустрии красоты премиум-сегмента. Работает с брендами Wella, Schwarzkopf, Olaplex. Постоянно совершенствует навыки на международных мастер-классах.',
    photo: new URL('../assets/images/master_1.png', import.meta.url).href,
    topServices: [
      'Сложное окрашивание (Airtouch, Balayage)',
      'Стрижка женская премиум',
      'Уход «Абсолютное счастье для волос»',
    ],
    services: [
      { name: 'Сложное окрашивание (Airtouch)', duration: '3 ч' },
      { name: 'Balayage + тонирование', duration: '2.5 ч' },
      { name: 'Стрижка женская премиум', duration: '1.5 ч' },
      { name: 'Уход «Абсолютное счастье для волос»', duration: '1 ч' },
    ],
    slots: [
      { label: 'Сегодня:', times: ['15:00', '17:00'] },
      { label: 'Завтра:', times: ['09:00', '11:00', '14:00'] },
      { label: 'Пятница:', times: ['10:00', '13:00', '16:00'] },
    ],
  },
  {
    id: 'ekaterina',
    name: 'Екатерина Смирнова',
    specialization: 'Визажист · Бровист',
    tags: ['Визажист', 'Бровист'],
    bio: 'Создает безупречные образы для особых случаев. Подчеркивает естественную красоту с помощью премиальной косметики Tom Ford, Charlotte Tilbury.',
    fullBio: 'Создает безупречные образы для особых случаев. Подчеркивает естественную красоту с помощью премиальной косметики Tom Ford, Charlotte Tilbury. 8 лет опыта, более 500 довольных клиентов.',
    photo: new URL('../assets/images/master_2.png', import.meta.url).href,
    topServices: [
      'Вечерний макияж',
      'Архитектура и окрашивание бровей',
      'Свадебный образ',
    ],
    services: [
      { name: 'Вечерний макияж', duration: '1.5 ч' },
      { name: 'Свадебный образ + репетиция', duration: '2 ч' },
      { name: 'Архитектура и окрашивание бровей', duration: '1 ч' },
      { name: 'Дневной макияж (clean girl)', duration: '1 ч' },
    ],
    slots: [
      { label: 'Завтра:', times: ['10:00', '14:00'] },
      { label: 'Суббота:', times: ['09:00', '11:00', '15:00'] },
    ],
  },
  {
    id: 'maria',
    name: 'Мария Иванова',
    specialization: 'Мастер ногтевого сервиса',
    tags: ['Мастер ногтевого сервиса'],
    bio: 'Идеальный маникюр с архитектурой формы. Работает с премиальными материалами Luxio, Kinetics. Соблюдение всех стандартов стерилизации.',
    fullBio: 'Идеальный маникюр с архитектурой формы. Работает с премиальными материалами Luxio, Kinetics. Соблюдение всех стандартов стерилизации. 6 лет опыта и более 1200 довольных клиентов.',
    photo: new URL('../assets/images/master_3.png', import.meta.url).href,
    topServices: [
      'Аппаратный маникюр + покрытие Luxio',
      'Smart-педикюр премиум',
      'SPA-уход для рук',
    ],
    services: [
      { name: 'Аппаратный маникюр + покрытие Luxio', duration: '1.5 ч' },
      { name: 'Smart-педикюр премиум', duration: '1.5 ч' },
      { name: 'SPA-уход для рук', duration: '45 мин' },
      { name: 'Nail art дизайн', duration: '2 ч' },
    ],
    slots: [
      { label: 'Сегодня:', times: ['16:00', '18:00'] },
      { label: 'Завтра:', times: ['09:00', '12:00', '15:00', '17:00'] },
    ],
  },
])

const reviews = [
  { name: 'Дарья', stars: 5, text: '«Делала окрашивание у Анны. Цвет получился идеальным, волосы живые и блестящие. Сервис на высшем уровне!»', date: '12 октября' },
  { name: 'Ольга', stars: 5, text: '«Макияж от Кати держался весь вечер, я чувствовала себя настоящей звездой. Очень внимательный мастер.»', date: '05 ноября' },
  { name: 'Елена', stars: 5, text: '«Лучший маникюр в городе. Идеальные блики, премиальные материалы. Атмосфера салона потрясающая.»', date: '22 ноября' },
  { name: 'Мария', stars: 4, text: '«Отличный сервис, вкусный кофе. Была небольшая задержка по времени, но результат превзошел ожидания.»', date: '01 декабря' },
]

function openDrawer(master, index) {
  selectedMaster.value = master
  drawerSide.value = index % 2 === 0 ? 'right' : 'left'
  drawerOpen.value = true
}

function openBooking(master) {
  wizardMaster.value = master ? { ...master, id: typeof master.id === 'string' ? undefined : master.id } : null
  drawerOpen.value = false
  wizardOpen.value = true
}

function starsString(count) {
  return '★'.repeat(count) + '☆'.repeat(5 - count)
}

function handleScroll() {
  const scrollY = window.scrollY
  document.body.style.setProperty('--scroll-y', scrollY + 'px')
  document.body.style.setProperty('--scroll-progress', Math.min(scrollY / 800, 1))

  const wh = window.innerHeight
  document.querySelectorAll('.staggered-card').forEach(card => {
    const rect = card.getBoundingClientRect()
    const cardProg = Math.max(0, Math.min(1, 1 - (rect.top - wh * 0.2) / (wh * 0.6)))
    card.style.setProperty('--card-progress', cardProg)
  })
}

onMounted(() => {
  document.body.style.setProperty('--scroll-y', window.scrollY + 'px')
  document.body.style.setProperty('--scroll-progress', Math.min(window.scrollY / 800, 1))
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <main class="home-page">
    <!-- Vignette overlay -->
    <div class="vignette-overlay" aria-hidden="true" />

    <!-- ═══ HERO ═══ -->
    <section class="hero">
      <div class="hero__bg" :style="{ backgroundImage: `url(${heroUrl})` }" />
      <div class="hero__overlay" />
      <div class="container hero__content">
        <p class="eyebrow">Премиальный салон красоты</p>
        <h1 class="hero__title">La Bellezza</h1>
        <p class="hero__subtitle">Искусство красоты в каждой детали</p>
        <div class="hero__actions">
          <a class="btn btn-gold btn-lg" href="#masters">Выбрать мастера</a>
          <RouterLink class="btn btn-outline btn-lg" to="/masters">Быстрая запись</RouterLink>
        </div>
        <a class="scroll-hint" href="#masters" aria-label="Прокрутить к мастерам">
          <span /><span />
        </a>
      </div>
    </section>

    <!-- ═══ FEATURED MASTERS ═══ -->
    <section id="masters" class="masters-section">
      <div class="container section-header">
        <h2>Топ-мастера</h2>
        <p class="text-secondary font-bio" style="font-size: 18px">
          Профессионалы, создающие эксклюзивные образы
        </p>
      </div>

      <div class="featured-masters">
        <MasterCard
          v-for="(master, i) in masters"
          :key="master.id"
          :master="master"
          variant="featured"
          @click="openDrawer(master, i)"
          @book="openBooking(master)"
        />
      </div>
    </section>

    <!-- ═══ TESTIMONIALS ═══ -->
    <section class="reviews-section container">
      <div class="section-header">
        <h2>Отзывы наших клиентов</h2>
      </div>
      <div class="reviews-carousel">
        <div
          v-for="review in reviews"
          :key="review.name"
          class="glass-panel review-card"
        >
          <div class="review-card__stars">{{ starsString(review.stars) }}</div>
          <p class="review-card__text font-bio">{{ review.text }}</p>
          <div class="review-card__footer">
            <span class="font-heading review-card__name">{{ review.name }}</span>
            <span class="font-caption review-card__date">{{ review.date }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Drawer -->
    <MasterDrawer
      v-model="drawerOpen"
      :master="selectedMaster"
      :side="drawerSide"
      @book="openBooking"
    />

    <BookingWizard
      v-model="wizardOpen"
      :preselected-master="wizardMaster"
    />
  </main>
</template>

<style scoped>
/* ═══ Hero ═══ */
.hero {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding-bottom: 80px;
  overflow: hidden;
}

.hero__bg {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background-size: cover;
  background-position: center center;
  z-index: -2;
  transform: translateY(calc(var(--scroll-y, 0px) * 0.45));
  will-change: transform;
}

.hero__overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: linear-gradient(
    to bottom,
    rgba(10, 10, 10, 0.1) 0%,
    rgba(10, 10, 10, 0.6) 70%,
    var(--bg-primary) 100%
  );
  z-index: -1;
}

.hero__content {
  position: relative;
  z-index: 10;
  padding-top: 15vh;
  padding-bottom: 11vh;
}

.hero__title {
  font-size: clamp(2.9rem, 14vw, 7.4rem);
  font-family: var(--font-brand);
  background: linear-gradient(135deg, var(--accent-gold) 0%, var(--accent-rose) 40%, var(--accent-gold) 80%);
  background-size: 300% auto;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: shimmerGold 8s linear infinite;
  margin: 0.6rem 0 0;
  line-height: 0.9;
}

.hero__subtitle {
  font-family: var(--font-subheading);
  font-size: clamp(1.15rem, 2.8vw, 2rem);
  color: var(--text-secondary);
  margin-top: 1rem;
  margin-bottom: 0;
  line-height: 1.15;
  letter-spacing: 0.04em;
  max-width: 42ch;
}

.hero__actions {
  margin-top: 1.7rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.scroll-hint {
  width: 34px;
  height: 54px;
  margin-top: 2.3rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.28);
  display: inline-flex;
  justify-content: center;
  position: relative;
}

.scroll-hint span {
  position: absolute;
  top: 10px;
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: var(--accent-gold);
  animation: scrollHint 1.8s infinite;
}

.scroll-hint span:last-child {
  animation-delay: 0.9s;
}

/* ═══ Masters Section ═══ */
.masters-section {
  padding-top: 100px;
}

.featured-masters {
  display: flex;
  flex-direction: column;
  gap: 40px;
  max-width: 100%;
  padding: 0 24px;
  overflow: clip;
}

/* ═══ Reviews ═══ */
.reviews-section {
  padding-top: 40px;
  overflow: hidden;
}

.reviews-carousel {
  display: flex;
  gap: 24px;
  overflow-x: auto;
  padding-bottom: 24px;
  scrollbar-width: thin;
  scrollbar-color: rgba(200, 169, 110, 0.3) transparent;
}

.review-card {
  flex: 0 0 320px;
  padding: 24px;
}

.review-card__stars {
  color: var(--accent-gold);
  margin-bottom: 12px;
  font-size: 18px;
}

.review-card__text {
  margin-bottom: 16px;
  min-height: 80px;
  font-size: 15px;
  color: var(--text-secondary);
  line-height: 1.6;
}

.review-card__footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.review-card__name {
  color: var(--accent-gold);
  font-size: 18px;
}

.review-card__date {
  color: var(--text-muted);
  font-size: 12px;
}
</style>
