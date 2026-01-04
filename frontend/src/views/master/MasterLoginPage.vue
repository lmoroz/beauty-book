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
    <div class="master-login__card glass-panel">
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
  background: var(--bg-primary, #0A0A0A);
  padding: 24px;
}

.master-login__card {
  width: 100%;
  max-width: 440px;
  padding: 48px 40px;
}

.master-login__brand {
  font-family: var(--font-brand);
  font-size: 2rem;
  text-align: center;
  color: var(--accent-gold, #DAB97B);
  margin-bottom: 8px;
  font-weight: 400;
}

.master-login__title {
  font-size: 0.85rem;
  text-align: center;
  color: var(--text-muted, #777);
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 32px;
}

.master-login__form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.master-login__field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.master-login__field label {
  font-family: var(--font-caption);
  font-size: 12px;
  color: var(--text-secondary, #AFAFAF);
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.master-login__field input {
  background: var(--bg-primary, #0A0A0A);
  border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.05));
  border-radius: 12px;
  padding: 14px 16px;
  color: var(--text-primary, #F5F0EB);
  font-size: 1rem;
  font-family: var(--font-body);
  transition: border-color 0.2s, box-shadow 0.2s;
}

.master-login__field input:focus {
  outline: none;
  border-color: var(--accent-gold, #DAB97B);
  box-shadow: 0 0 0 2px rgba(200, 169, 110, 0.2);
}

.master-login__field input::placeholder {
  color: var(--text-muted, #777);
}

.master-login__error {
  color: var(--error, #E57373);
  font-size: 0.85rem;
  text-align: center;
  margin: 0;
}

.master-login__submit {
  background: linear-gradient(135deg, #f0dab7 0%, #c8a96e 54%, #9f7445 100%);
  color: #1f1710;
  border: none;
  border-radius: 999px;
  padding: 14px;
  font-family: var(--font-nav);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-top: 4px;
  box-shadow: 0 8px 20px rgba(200, 169, 110, 0.26);
}

.master-login__submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px rgba(200, 169, 110, 0.4);
}

.master-login__submit:disabled {
  opacity: 0.5;
  cursor: wait;
}
</style>
