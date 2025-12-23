<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')

async function login() {
  error.value = ''
  try {
    // TODO: implement API call
    localStorage.setItem('master_token', 'placeholder')
    router.push({ name: 'master-schedule' })
  } catch (e) {
    error.value = 'Неверный email или пароль'
  }
}
</script>

<template>
  <main class="master-login">
    <div class="master-login__card">
      <h1>La Bellezza</h1>
      <h2>Вход для мастеров</h2>
      <form @submit.prevent="login">
        <input v-model="email" type="email" placeholder="Email" required />
        <input v-model="password" type="password" placeholder="Пароль" required />
        <p v-if="error" class="master-login__error">{{ error }}</p>
        <button type="submit">Войти</button>
      </form>
    </div>
  </main>
</template>
