<script setup>
import AppDrawer from '../ui/AppDrawer.vue'

defineProps({
  master: { type: Object, default: null },
  modelValue: { type: Boolean, default: false },
  side: { type: String, default: 'right' },
})

defineEmits(['update:modelValue', 'book'])
</script>

<template>
  <AppDrawer :model-value="modelValue" :side="side" width="90vw" @update:model-value="$emit('update:modelValue', $event)">
    <template v-if="master">
      <div class="master-drawer__hero">
        <img :src="master.photo" :alt="master.name" />
      </div>
      <div class="master-drawer__content">
        <h2>{{ master.name }}</h2>
        <p>{{ master.specialization }}</p>
        <p>{{ master.bio }}</p>
      </div>
      <div class="master-drawer__footer">
        <button @click="$emit('book', master)">Записаться к {{ master.name }}</button>
      </div>
    </template>
  </AppDrawer>
</template>
