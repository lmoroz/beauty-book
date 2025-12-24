<script setup>
import { X } from 'lucide-vue-next'

defineProps({
  modelValue: { type: Boolean, default: false },
  preselectedMaster: { type: Object, default: null },
})

defineEmits(['update:modelValue', 'booked'])
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modelValue" class="booking-wizard__backdrop">
        <div class="booking-wizard">
          <button class="booking-wizard__close" @click="$emit('update:modelValue', false)">
            <X :size="24" :stroke-width="1.5" />
          </button>
          <p>Booking wizard — coming in Phase 4.3</p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
