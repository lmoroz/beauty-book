<script setup>
import { ref } from 'vue'
import { useMastersStore } from '../stores/masters.js'
import MasterCard from '../components/master/MasterCard.vue'
import MasterDrawer from '../components/master/MasterDrawer.vue'

const store = useMastersStore()

const selectedMaster = ref(null)
const drawerOpen = ref(false)
const drawerSide = ref('right')

function openDrawer(master, index) {
  selectedMaster.value = master
  drawerSide.value = index % 2 === 0 ? 'right' : 'left'
  drawerOpen.value = true
}
</script>

<template>
  <main class="home-page">
    <section class="hero">
      <div class="hero__content">
        <h1 class="hero__title">La Bellezza</h1>
        <p class="hero__subtitle">Искусство красоты в каждой детали</p>
        <a href="#masters" class="hero__cta">Выбрать мастера</a>
      </div>
    </section>

    <section id="masters" class="featured-masters">
      <h2>Лучшие мастера</h2>
      <div class="featured-masters__grid">
        <MasterCard
          v-for="(master, i) in store.masters"
          :key="master.id"
          :master="master"
          variant="featured"
          @click="openDrawer(master, i)"
        />
      </div>
    </section>

    <section class="reviews">
      <h2>Отзывы наших клиентов</h2>
      <p>Reviews carousel — coming in Phase 4.2</p>
    </section>

    <MasterDrawer
      v-model="drawerOpen"
      :master="selectedMaster"
      :side="drawerSide"
    />
  </main>
</template>
