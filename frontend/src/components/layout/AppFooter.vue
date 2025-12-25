<script setup>
import { Instagram, Send, MessageCircle } from 'lucide-vue-next'
</script>

<template>
  <footer class="footer">
    <div class="container footer__grid">
      <div class="footer__col">
        <a href="/" class="header__logo" style="display:block; margin-bottom:20px;">
          <span style="font-size: 24px;">La Bellezza</span>
        </a>
        <p>Искусство красоты в каждой детали.</p>
        <p>Ваш персональный оазис стиля и совершенства.</p>
        <div class="footer__socials">
          <a href="#" aria-label="Instagram" class="footer__social-link">
            <Instagram :size="20" :stroke-width="1.5" />
          </a>
          <a href="#" aria-label="Telegram" class="footer__social-link">
            <Send :size="20" :stroke-width="1.5" />
          </a>
          <a href="#" aria-label="WhatsApp" class="footer__social-link">
            <MessageCircle :size="20" :stroke-width="1.5" />
          </a>
        </div>
      </div>
      <div class="footer__col">
        <h4>Контакты</h4>
        <p>г. Москва, ул. Премиальная, д. 1</p>
        <p>+7 (999) 123-45-67</p>
        <p>hello@labellezza.ru</p>
      </div>
      <div class="footer__col">
        <h4>Часы работы</h4>
        <p>Пн - Вс: 10:00 – 22:00</p>
        <p>Без выходных</p>
      </div>
      <div class="footer__col">
        <h4>Соцсети</h4>
        <ul>
          <li><a href="#" class="text-gold">Instagram</a></li>
          <li><a href="#" class="text-gold">Telegram</a></li>
          <li><a href="#" class="text-gold">WhatsApp</a></li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom container">
      © 2020-2026 La Bellezza. Все права защищены.
    </div>
  </footer>
</template>

<style scoped>
.footer {
  background: var(--color-bg-secondary);
  padding: 80px 0 40px;
  border-top: 1px solid var(--border-subtle);
  margin-top: auto;
  position: relative;
  z-index: 1;
}

.footer__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 40px;
  margin-bottom: 60px;
}

.footer__col h4 {
  font-family: var(--font-heading);
  font-size: 20px;
  margin-bottom: 20px;
  color: var(--accent-gold);
}

.footer__col p,
.footer__col li {
  color: var(--text-secondary);
  margin-bottom: 12px;
  font-size: 15px;
}

.footer__col a {
  transition: color var(--duration-fast);
}

.footer__col a:hover {
  color: var(--accent-gold);
}

.footer__socials {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.footer__social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid var(--border-gold);
  color: var(--text-secondary);
  transition: all var(--duration-fast);
}

.footer__social-link:hover {
  color: var(--accent-gold);
  border-color: var(--accent-gold);
  background: rgba(200, 169, 110, 0.08);
}

.footer__bottom {
  text-align: center;
  padding-top: 24px;
  border-top: 1px solid var(--border-subtle);
  color: var(--text-muted);
  font-size: 14px;
}

@media (max-width: 768px) {
  .footer__grid {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .footer__socials {
    justify-content: center;
  }
}
</style>
