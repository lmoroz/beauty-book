<script setup>
defineProps({
  master: { type: Object, required: true },
  variant: { type: String, default: 'featured' },
})

defineEmits(['click', 'book'])
</script>

<template>
  <article class="master-card" :class="`master-card--${variant}`" @click="$emit('click', master)">
    <div class="master-card__photo">
      <img :src="master.photo" :alt="master.name" />
    </div>
    <div class="master-card__body">
      <h3 class="master-card__name">{{ master.name }}</h3>
      <span class="master-card__spec">{{ master.specialization }}</span>
      <p class="master-card__bio">{{ master.bio }}</p>
      <button v-if="variant === 'grid'" class="master-card__cta" @click.stop="$emit('book', master)">
        Записаться
      </button>
    </div>
  </article>
</template>
