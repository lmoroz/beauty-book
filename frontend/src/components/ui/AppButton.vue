<template>
  <button class="app-button" :class="[`app-button--${variant}`, `app-button--${size}`]" :disabled="disabled || loading">
    <span v-if="loading" class="app-button__spinner" />
    <slot v-else />
  </button>
</template>

<script setup>
defineProps({
  variant: { type: String, default: 'primary' },
  size: { type: String, default: 'md' },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
})
</script>
