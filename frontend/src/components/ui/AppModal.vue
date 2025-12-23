<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modelValue" class="app-modal__backdrop" @click.self="close">
        <div class="app-modal" :class="`app-modal--${size}`">
          <button class="app-modal__close" @click="close">&times;</button>
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  size: { type: String, default: 'md' },
})

const emit = defineEmits(['update:modelValue'])

function close() {
  emit('update:modelValue', false)
}

function onKeydown(e) {
  if (e.key === 'Escape' && props.modelValue) close()
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))
</script>
