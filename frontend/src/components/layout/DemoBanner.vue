<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useDashboardStore } from '../../stores/dashboard.js'

const route = useRoute()
const dashboard = useDashboardStore()

const isDemo = computed(() => 'demo' in route.query)
const isMasterArea = computed(() => route.path === '/master' || route.path.startsWith('/master/'))
const masterName = computed(() => dashboard.profile?.name || '')
const label = computed(() => {
  if (!isMasterArea.value) return 'Клиент'
  return masterName.value ? `Мастер · ${masterName.value}` : 'Мастер'
})
</script>

<template>
  <div v-if="isDemo" class="demo-banner" :class="{ 'demo-banner--master': isMasterArea }">
    {{ label }}
  </div>
</template>

<style scoped>
.demo-banner {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 9999;
  padding: 6px 0;
  text-align: center;
  font-family: var(--font-nav, system-ui, sans-serif);
  font-size: 25px;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #fff;
  background: rgba(33, 33, 33, 0.5);
  pointer-events: none;
  user-select: none;
}

.demo-banner--master {
  font-size: 16px;
}
</style>
