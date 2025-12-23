<template>
  <div class="app-card" :class="{ 'app-card--hoverable': hoverable }">
    <slot />
  </div>
</template>

<script setup>
defineProps({
  hoverable: { type: Boolean, default: true },
})
</script>
