<template>
  <aside class="app-sidebar" :class="{ 'app-sidebar--collapsed': collapsed }">
    <slot />
  </aside>
</template>

<script setup>
defineProps({
  collapsed: { type: Boolean, default: false },
})
</script>
