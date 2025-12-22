<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useMastersStore } from '../stores/masters.js'

const route = useRoute()
const store = useMastersStore()
const master = ref(null)

onMounted(async () => {
  master.value = await store.fetchMaster(route.params.id)
})
</script>

<template>
  <main class="page master-profile-page">
    <p v-if="store.loading">Loading...</p>
    <p v-else-if="store.error">{{ store.error }}</p>
    <template v-else-if="master">
      <h1>{{ master.name }}</h1>
      <p>{{ master.specialization }}</p>
    </template>
  </main>
</template>
