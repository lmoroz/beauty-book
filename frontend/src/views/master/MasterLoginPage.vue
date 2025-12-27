<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth.js'

const router = useRouter()
const auth = useAuthStore()

const login = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(login.value, password.value)
    router.push({ name: 'master-schedule' })
  } catch (e) {
    const msg = e.response?.data?.errors?.password?.[0]
    error.value = msg || 'Неверный логин или пароль'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="master-login">
    <div class="master-login__card">
      <h1 class="master-login__brand">La Bellezza</h1>
      <h2 class="master-login__title">Вход для мастеров</h2>

      <form class="master-login__form" @submit.prevent="handleLogin">
        <div class="master-login__field">
          <label for="login-input">Email или имя пользователя</label>
          <input
            id="login-input"
            v-model="login"
            type="text"
            placeholder="anna.petrova"
            autocomplete="username"
            required
          />
        </div>

        <div class="master-login__field">
          <label for="password-input">Пароль</label>
          <input
            id="password-input"
            v-model="password"
            type="password"
            placeholder="••••••••"
            autocomplete="current-password"
            required
          />
        </div>

        <p v-if="error" class="master-login__error">{{ error }}</p>

        <button type="submit" class="master-login__submit" :disabled="loading">
          {{ loading ? 'Вход…' : 'Войти' }}
        </button>
      </form>
    </div>
  </main>
</template>

<style scoped>
.master-login {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: var(--color-bg, #0e0e0e);
  padding: var(--space-md);
}

.master-login__card {
  width: 100%;
  max-width: 400px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: var(--space-2xl) var(--space-xl);
  backdrop-filter: blur(20px);
}

.master-login__brand {
  font-family: var(--font-display, serif);
  font-size: 2rem;
  text-align: center;
  color: var(--color-gold, #c9a96e);
  margin-bottom: var(--space-xs);
  font-weight: 400;
}

.master-login__title {
  font-size: 0.9rem;
  text-align: center;
  color: var(--color-text-muted, #999);
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: var(--space-xl);
}

.master-login__form {
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
}

.master-login__field {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}

.master-login__field label {
  font-size: 0.8rem;
  color: var(--color-text-muted, #999);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.master-login__field input {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 12px 16px;
  color: var(--color-text, #f5f0eb);
  font-size: 1rem;
  transition: border-color 0.2s ease;
}

.master-login__field input:focus {
  outline: none;
  border-color: var(--color-gold, #c9a96e);
}

.master-login__field input::placeholder {
  color: rgba(255, 255, 255, 0.2);
}

.master-login__error {
  color: #e74c3c;
  font-size: 0.85rem;
  text-align: center;
  margin: 0;
}

.master-login__submit {
  background: linear-gradient(135deg, var(--color-gold, #c9a96e), var(--color-gold-light, #dbb985));
  color: var(--color-bg, #0e0e0e);
  border: none;
  border-radius: 8px;
  padding: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s ease, transform 0.15s ease;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.master-login__submit:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
}

.master-login__submit:disabled {
  opacity: 0.5;
  cursor: wait;
}
</style>
