<script setup>
import { Instagram, Send, MessageCircle, Phone, MapPin, Clock } from 'lucide-vue-next'
</script>

<template>
  <footer class="app-footer">
    <div class="app-footer__inner">
      <div class="app-footer__brand">
        <span class="app-footer__logo">La Bellezza</span>
        <p>Искусство красоты в каждой детали</p>

        <div class="app-footer__socials">
          <a href="#" aria-label="Instagram" class="app-footer__social-link">
            <Instagram :size="20" :stroke-width="1.5" />
          </a>
          <a href="#" aria-label="Telegram" class="app-footer__social-link">
            <Send :size="20" :stroke-width="1.5" />
          </a>
          <a href="#" aria-label="WhatsApp" class="app-footer__social-link">
            <MessageCircle :size="20" :stroke-width="1.5" />
          </a>
        </div>
      </div>

      <div class="app-footer__info">
        <h4>Контакты</h4>
        <p>
          <MapPin :size="14" :stroke-width="1.5" class="app-footer__icon" />
          г. Москва, ул. Примерная, 42
        </p>
        <p>
          <a href="tel:+74951234567">
            <Phone :size="14" :stroke-width="1.5" class="app-footer__icon" />
            +7 (495) 123-45-67
          </a>
        </p>
      </div>

      <div class="app-footer__hours">
        <h4>Часы работы</h4>
        <p>
          <Clock :size="14" :stroke-width="1.5" class="app-footer__icon" />
          Пн — Пт: 9:00 — 21:00
        </p>
        <p>
          <Clock :size="14" :stroke-width="1.5" class="app-footer__icon" />
          Сб — Вс: 10:00 — 20:00
        </p>
      </div>
    </div>

    <div class="app-footer__bottom">
      <p>&copy; 2020–2026 La Bellezza</p>
    </div>
  </footer>
</template>

<style scoped>
.app-footer {
  background: var(--color-bg-secondary);
  border-top: 1px solid var(--color-border);
  padding: 3rem 2rem 1.5rem;
  margin-top: auto;
}

.app-footer__inner {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.app-footer__logo {
  font-family: 'Kudry-WeirdDisplay', serif;
  font-size: 1.5rem;
  color: var(--color-accent-gold);
}

.app-footer h4 {
  font-family: 'Kudry-SansHeadline', sans-serif;
  color: var(--color-text-primary);
  margin-bottom: 0.5rem;
}

.app-footer p,
.app-footer a {
  font-family: 'Kudry-SansText', sans-serif;
  color: var(--color-text-secondary);
  font-size: 0.875rem;
  line-height: 1.6;
}

.app-footer a {
  text-decoration: none;
  transition: color 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
}

.app-footer a:hover {
  color: var(--color-accent-gold);
}

.app-footer__icon {
  flex-shrink: 0;
  vertical-align: middle;
  margin-right: 0.25rem;
  opacity: 0.6;
}

.app-footer__socials {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
}

.app-footer__social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  transition: color 0.2s, border-color 0.2s, background-color 0.2s;
}

.app-footer__social-link:hover {
  color: var(--color-accent-gold);
  border-color: var(--color-accent-gold);
  background: rgba(200, 169, 110, 0.08);
}

.app-footer__bottom {
  max-width: 1400px;
  margin: 2rem auto 0;
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border);
  text-align: center;
}

@media (max-width: 768px) {
  .app-footer__inner {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .app-footer__socials {
    justify-content: center;
  }
}
</style>
