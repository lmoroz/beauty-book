<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useBookingsStore } from '../stores/bookings.js'

const route = useRoute()
const store = useBookingsStore()

onMounted(() => store.fetchBooking(route.params.id))
</script>

<template>
  <main class="booking-status-page">
    <h1>Статус записи</h1>
    <p v-if="store.loading">Загрузка...</p>
    <p v-else-if="store.error">{{ store.error }}</p>
    <template v-else-if="store.currentBooking">
      <p>Booking #{{ store.currentBooking.id }}</p>
      <p>Статус: {{ store.currentBooking.status }}</p>
    </template>
  </main>
</template>
