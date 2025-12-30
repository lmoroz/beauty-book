<script setup>
defineProps({
  master: { type: Object, required: true },
  variant: { type: String, default: 'featured' },
})

defineEmits(['click', 'book'])
</script>

<template>
  <!-- Featured staggered card (Home page) -->
  <article
    v-if="variant === 'featured'"
    class="staggered-card"
    @click="$emit('click', master)"
  >
    <div class="staggered-card__img-wrapper">
      <img :src="master.photo" :alt="master.name" class="staggered-card__img">
    </div>
    <div class="staggered-card__info">
      <div class="staggered-card__name-row">
        <h3 class="staggered-card__name">{{ master.name }}</h3>
        <div class="staggered-card__spec">
          <span
            v-for="spec in master.specializations"
            :key="spec.id"
            class="pill pill-outline"
          >{{ spec.name }}</span>
        </div>
      </div>
      <p class="staggered-card__bio">{{ master.bio }}</p>
      <ul class="staggered-card__services">
        <li v-for="svc in master.topServices" :key="svc">{{ svc }}</li>
      </ul>
      <button class="btn btn-outline" @click.stop="$emit('book', master)">
        Записаться
      </button>
    </div>
  </article>

  <!-- Grid card (Masters catalog page) -->
  <article
    v-else
    class="master-card"
    @click="$emit('click', master)"
  >
    <div class="master-card__img-wrapper">
      <img :src="master.photo" :alt="master.name" class="master-card__img">
    </div>

    <div class="master-card__body">
      <h3 class="master-card__title">{{ master.name }}</h3>
      <div class="master-card__tags">
        <span
          v-for="spec in master.specializations"
          :key="spec.id"
          class="pill pill-outline"
        >{{ spec.name }}</span>
      </div>
      <p class="master-card__bio">{{ master.shortBio || master.bio }}</p>
      <ul class="master-card__services">
        <li v-for="svc in master.topServices" :key="svc">{{ svc }}</li>
      </ul>
      <button class="btn btn-gold" @click.stop="$emit('book', master)">
        Записаться
      </button>
    </div>
  </article>
</template>

<style scoped>
/* ═══ Featured Staggered Card ═══ */
.staggered-card {
  display: flex;
  align-items: stretch;
  gap: 0;
  width: min(92%, 1200px);
  height: 300px;
  border-radius: 24px;
  background: var(--bg-glass);
  backdrop-filter: blur(24px) saturate(140%);
  -webkit-backdrop-filter: blur(24px) saturate(140%);
  border: 1px solid var(--border-subtle);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
  opacity: clamp(0, calc(var(--card-progress, 1) * 2), 1);
  position: relative;
  z-index: 1;
  transition: box-shadow var(--duration-smooth), border-color var(--duration-smooth);
  overflow: hidden;
  cursor: pointer;
}

.staggered-card:hover {
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.8);
  border-color: var(--accent-gold);
}

.staggered-card:nth-child(even) {
  flex-direction: row-reverse;
  align-self: flex-end;
  transform: translateX(calc((1 - clamp(0, var(--card-progress, 1), 1)) * 100px));
}

.staggered-card:nth-child(odd) {
  align-self: flex-start;
  transform: translateX(calc((1 - clamp(0, var(--card-progress, 1), 1)) * -100px));
}

.staggered-card__img-wrapper {
  flex: 0 0 35%;
  overflow: hidden;
}

.staggered-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 1s var(--ease-out);
}

.staggered-card:hover .staggered-card__img {
  transform: scale(1.05);
}

.staggered-card__info {
  flex: 1;
  padding: 24px 32px 24px 28px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  overflow: hidden;
}

.staggered-card:nth-child(even) .staggered-card__info {
  padding: 24px 28px 24px 32px;
}

.staggered-card__name-row {
  display: flex;
  align-items: baseline;
  gap: 12px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.staggered-card__name {
  font-family: var(--font-heading);
  font-size: 28px;
  margin: 0;
  white-space: nowrap;
}

.staggered-card__spec {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.staggered-card__bio {
  color: var(--text-secondary);
  font-family: var(--font-bio);
  margin-bottom: 10px;
  font-size: 15px;
  line-height: 1.5;
}

.staggered-card__services {
  list-style: none;
  padding: 0;
  margin: 0 0 12px 0;
}

.staggered-card__services li {
  padding-left: 20px;
  position: relative;
  margin-bottom: 4px;
  color: var(--text-primary);
  font-size: 14px;
}

.staggered-card__services li::before {
  content: "✦";
  position: absolute;
  left: 0;
  top: 1px;
  color: var(--accent-gold);
  font-size: 12px;
}

.staggered-card__info .btn {
  padding: 10px 24px;
  font-size: 13px;
}

/* ═══ Grid Card (Catalog — Masonry) ═══ */
.master-card {
  display: inline-block;
  width: 100%;
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  background: var(--bg-glass);
  backdrop-filter: blur(24px) saturate(140%);
  -webkit-backdrop-filter: blur(24px) saturate(140%);
  border: 1px solid var(--border-subtle);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
  transition: all var(--duration-smooth) var(--ease-out);
  cursor: pointer;
}

.master-card:hover {
  transform: translateY(-6px);
  border-color: var(--border-gold-hover);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6), 0 0 0 1px var(--border-gold-hover);
}

.master-card__img-wrapper {
  overflow: hidden;
  line-height: 0;
}

.master-card__img {
  width: 100%;
  aspect-ratio: 3 / 4;
  object-fit: cover;
  object-position: top;
  transition: transform var(--duration-slow) var(--ease-out);
}

.master-card:hover .master-card__img {
  transform: scale(1.04);
}

.master-card__body {
  padding: 20px 22px 24px;
}

.master-card__title {
  font-family: var(--font-heading);
  font-size: 22px;
  margin-bottom: 8px;
  line-height: 1.2;
}

.master-card__tags {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.master-card__bio {
  color: var(--text-secondary);
  font-family: var(--font-bio);
  font-size: 14px;
  line-height: 1.55;
  margin-bottom: 14px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.master-card__services {
  list-style: none;
  padding: 0;
  margin: 0 0 18px 0;
}

.master-card__services li {
  padding-left: 20px;
  position: relative;
  margin-bottom: 4px;
  color: var(--text-primary);
  font-size: 13px;
  font-family: var(--font-caption);
}

.master-card__services li::before {
  content: "✦";
  position: absolute;
  left: 0;
  top: 1px;
  color: var(--accent-gold);
  font-size: 11px;
}

.master-card__body .btn {
  width: 100%;
  padding: 11px 20px;
  font-size: 13px;
}

/* ═══ Responsive ═══ */
@media (max-width: 1024px) {
  .staggered-card {
    flex-direction: column !important;
    height: auto;
    width: 100%;
    transform: none !important;
  }

  .staggered-card:hover {
    transform: translateY(-8px) !important;
  }

  .staggered-card__img-wrapper {
    min-height: 300px;
    flex: none;
  }

  .staggered-card__info {
    padding: 32px !important;
  }
}
</style>
