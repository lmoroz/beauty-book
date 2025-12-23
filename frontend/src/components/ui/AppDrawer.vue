<template>
  <Teleport to="body">
    <Transition name="drawer">
      <div v-if="modelValue" class="app-drawer__backdrop" @click.self="close">
        <div class="app-drawer" :class="`app-drawer--${side}`" :style="{ width }">
          <button class="app-drawer__close" @click="close">&times;</button>
          <div class="app-drawer__content">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  side: { type: String, default: 'right' },
  width: { type: String, default: '90vw' },
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
