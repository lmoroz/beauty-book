<template>
  <span class="app-pill" :class="`app-pill--${variant}`">
    <slot />
  </span>
</template>

<script setup>
defineProps({
  variant: { type: String, default: 'neutral' },
})
</script>
