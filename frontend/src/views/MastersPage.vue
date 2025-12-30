<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMastersStore } from '../stores/masters.js'
import MasterCard from '../components/master/MasterCard.vue'
import MasterDrawer from '../components/master/MasterDrawer.vue'
import BookingWizard from '../components/booking/BookingWizard.vue'

const route = useRoute()
const router = useRouter()
const store = useMastersStore()

const selectedMaster = ref(null)
const drawerOpen = ref(false)
const wizardOpen = ref(false)
const wizardMaster = ref(null)

onMounted(() => store.fetchMasters())

watch(() => route.query.book, (val) => {
  if (val === '1') {
    wizardMaster.value = null
    wizardOpen.value = true
    router.replace({ path: '/masters' })
  }
}, { immediate: true })

function openDrawer(master) {
  selectedMaster.value = master
  drawerOpen.value = true
}

function openBooking(master) {
  wizardMaster.value = master
  drawerOpen.value = false
  wizardOpen.value = true
}
</script>

<template>
  <main class="masters-page">
    <section class="masters-catalog container">
      <div class="masters-catalog__header">
        <p class="eyebrow">Команда профессионалов</p>
        <h1>Наши мастера</h1>
        <p class="masters-catalog__subtitle">
          Каждый мастер — эксперт в своей области с&nbsp;индивидуальным подходом к&nbsp;каждому клиенту
        </p>
      </div>

      <div class="masters-catalog__grid">
        <MasterCard
          v-for="master in store.masters"
          :key="master.id"
          :master="master"
          variant="grid"
          @click="openDrawer(master)"
          @book="openBooking(master)"
        />
      </div>
    </section>

    <MasterDrawer
      v-model="drawerOpen"
      :master="selectedMaster"
      @book="openBooking"
    />

    <BookingWizard
      v-model="wizardOpen"
      :preselected-master="wizardMaster"
    />
  </main>
</template>

<style scoped>
.masters-page {
  padding-top: 120px;
  padding-bottom: 80px;
}

.masters-catalog__header {
  text-align: center;
  margin-bottom: 56px;
}

.masters-catalog__header h1 {
  font-family: var(--font-heading);
  font-size: clamp(32px, 5vw, 48px);
  margin: 12px 0 16px;
}

.masters-catalog__subtitle {
  color: var(--text-secondary);
  font-family: var(--font-bio);
  font-size: 17px;
  max-width: 540px;
  margin: 0 auto;
  line-height: 1.6;
}

/* ─── Grid layout ─── */
.masters-catalog__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 28px;
  align-items: start;
}

@media (max-width: 640px) {
  .masters-catalog__grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
}
</style>
