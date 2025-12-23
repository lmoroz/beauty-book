<template>
  <div class="app-avatar" :class="`app-avatar--${size}`">
    <img v-if="src" :src="src" :alt="alt" />
    <span v-else class="app-avatar__fallback">{{ initials }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  src: { type: String, default: '' },
  alt: { type: String, default: '' },
  name: { type: String, default: '' },
  size: { type: String, default: 'md' },
})

const initials = computed(() =>
  props.name
    .split(' ')
    .map((w) => w[0])
    .join('')
    .toUpperCase()
    .slice(0, 2),
)
</script>
