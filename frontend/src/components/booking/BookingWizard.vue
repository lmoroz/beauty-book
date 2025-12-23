<script setup>
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
          <button class="booking-wizard__close" @click="$emit('update:modelValue', false)">&times;</button>
          <p>Booking wizard — coming in Phase 4.3</p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
