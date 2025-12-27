<script setup>
import { ref, onMounted } from 'vue'
import { useMastersStore } from '../stores/masters.js'
import MasterCard from '../components/master/MasterCard.vue'
import MasterDrawer from '../components/master/MasterDrawer.vue'
import BookingWizard from '../components/booking/BookingWizard.vue'

const store = useMastersStore()

const selectedMaster = ref(null)
const drawerOpen = ref(false)
const wizardOpen = ref(false)
const wizardMaster = ref(null)

onMounted(() => store.fetchMasters())

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
    <section class="masters-catalog">
      <h1>Наши мастера</h1>

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
