<template>
  <footer class="app-footer">
    <div class="app-footer__inner">
      <div class="app-footer__brand">
        <span class="app-footer__logo">La Bellezza</span>
        <p>Искусство красоты в каждой детали</p>
      </div>

      <div class="app-footer__info">
        <h4>Контакты</h4>
        <p>г. Москва, ул. Примерная, 42</p>
        <p><a href="tel:+74951234567">+7 (495) 123-45-67</a></p>
      </div>

      <div class="app-footer__hours">
        <h4>Часы работы</h4>
        <p>Пн — Пт: 9:00 — 21:00</p>
        <p>Сб — Вс: 10:00 — 20:00</p>
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
}

.app-footer a:hover {
  color: var(--color-accent-gold);
}

.app-footer__bottom {
  max-width: 1400px;
  margin: 2rem auto 0;
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border);
  text-align: center;
}
</style>
