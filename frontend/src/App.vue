<script setup>
import { computed } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import AppHeader from './components/layout/AppHeader.vue'
import AppFooter from './components/layout/AppFooter.vue'
import ChatWidget from './components/chat/ChatWidget.vue'
import DemoBanner from './components/layout/DemoBanner.vue'

const route = useRoute()

const isMasterArea = computed(() => route.path === '/master' || route.path.startsWith('/master/'))
const showChat = computed(() => !isMasterArea.value)
const showHeaderFooter = computed(() => !isMasterArea.value)
</script>

<template>
  <DemoBanner />
  <AppHeader v-if="showHeaderFooter" />

  <RouterView />

  <AppFooter v-if="showHeaderFooter" />

  <ChatWidget v-if="showChat" />
</template>
