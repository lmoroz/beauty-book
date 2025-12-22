<script setup>
import { onMounted } from 'vue'
import { useMastersStore } from '../stores/masters.js'

const store = useMastersStore()

onMounted(() => {
  store.fetchMasters()
})
</script>

<template>
  <main class="page masters-page">
    <h1>Masters</h1>
    <p v-if="store.loading">Loading...</p>
    <p v-else-if="store.error">{{ store.error }}</p>
    <ul v-else>
      <li v-for="master in store.masters" :key="master.id">
        <router-link :to="{ name: 'master-profile', params: { id: master.id } }">
          {{ master.name }}
        </router-link>
      </li>
    </ul>
  </main>
</template>
