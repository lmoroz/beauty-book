<script setup>
import { ref, onMounted, watch } from 'vue'
import { LoaderCircle, Check } from 'lucide-vue-next'
import { useDashboardStore } from '../../stores/dashboard.js'

const dashboard = useDashboardStore()

const form = ref({
  bio: '',
  phone: '',
  email: '',
})

const saving = ref(false)
const saved = ref(false)
const saveError = ref('')

const apiBase = (import.meta.env.VITE_API_URL || '/api/v1').replace(/\/api\/v1\/?$/, '')

function photoUrl(photo) {
  if (!photo) return null
  if (photo.startsWith('http')) return photo
  return apiBase + photo
}

function loadForm() {
  const p = dashboard.profile
  if (p) {
    form.value = {
      bio: p.bio || '',
      phone: p.phone || '',
      email: p.email || '',
    }
  }
}

watch(() => dashboard.profile, loadForm)

async function saveProfile() {
  saving.value = true
  saved.value = false
  saveError.value = ''
  try {
    await dashboard.updateProfile(form.value)
    saved.value = true
    setTimeout(() => { saved.value = false }, 3000)
  } catch (e) {
    saveError.value = e.response?.data?.message || 'Ошибка сохранения'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (!dashboard.profile) {
    await dashboard.fetchProfile()
  }
  loadForm()
})
</script>

<template>
  <section class="profile-view">
    <h1>Профиль</h1>

    <div v-if="dashboard.loading && !dashboard.profile" class="profile-view__loading">
      <LoaderCircle :size="24" :stroke-width="1.5" class="spinner" />
      <span>Загрузка…</span>
    </div>

    <div v-else-if="dashboard.profile" class="profile-view__content">
      <div class="profile-view__card glass-panel">
        <div class="profile-view__hero">
          <div class="profile-view__avatar">
            <img
              v-if="dashboard.profile.photo"
              :src="photoUrl(dashboard.profile.photo)"
              :alt="dashboard.profile.name"
            >
            <span v-else class="profile-view__initials">
              {{ (dashboard.profile.name || '').split(' ').map(w => w[0]).join('').toUpperCase() }}
            </span>
          </div>
          <div>
            <h2>{{ dashboard.profile.name }}</h2>
            <div class="profile-view__specs">
              <span
                v-for="s in dashboard.profile.specializations"
                :key="s.id"
                class="pill pill-gold"
              >{{ s.name }}</span>
            </div>
          </div>
        </div>

        <form class="profile-view__form" @submit.prevent="saveProfile">
          <div class="input-group">
            <label for="profile-bio">Биография</label>
            <textarea
              id="profile-bio"
              v-model="form.bio"
              class="input-field"
              rows="4"
              placeholder="Расскажите о себе…"
            ></textarea>
          </div>

          <div class="profile-view__row">
            <div class="input-group">
              <label for="profile-phone">Телефон</label>
              <input
                id="profile-phone"
                v-model="form.phone"
                class="input-field"
                type="tel"
                placeholder="+7 (___) ___-__-__"
              />
            </div>
            <div class="input-group">
              <label for="profile-email">Email</label>
              <input
                id="profile-email"
                v-model="form.email"
                class="input-field"
                type="email"
                placeholder="master@example.com"
              />
            </div>
          </div>

          <div class="profile-view__actions">
            <button type="submit" class="btn btn-gold" :disabled="saving">
              <LoaderCircle v-if="saving" :size="18" :stroke-width="1.5" class="spinner" />
              <Check v-else-if="saved" :size="18" :stroke-width="1.5" />
              <span>{{ saved ? 'Сохранено' : saving ? 'Сохранение…' : 'Сохранить' }}</span>
            </button>
          </div>

          <p v-if="saveError" class="profile-view__error">{{ saveError }}</p>
        </form>
      </div>
    </div>
  </section>
</template>

<style scoped>
.profile-view h1 {
  font-family: var(--font-heading);
  color: var(--text-primary, #F5F0EB);
  font-size: 32px;
  margin-bottom: 32px;
}

.profile-view__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 80px 0;
  color: var(--text-secondary, #AFAFAF);
}

.spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.profile-view__card {
  padding: 32px;
  border-radius: 24px;
  max-width: 720px;
}

.profile-view__hero {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid var(--border-subtle, rgba(255,255,255,0.05));
}

.profile-view__avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  border: 2px solid var(--border-gold, rgba(200,169,110,0.25));
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(160deg, rgba(200,169,110,0.32), rgba(212,160,160,0.28));
}

.profile-view__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-view__initials {
  font-family: var(--font-heading);
  font-size: 28px;
  color: var(--accent-gold, #DAB97B);
}

.profile-view__hero h2 {
  font-family: var(--font-heading);
  font-size: 28px;
  color: var(--text-primary, #F5F0EB);
  margin-bottom: 8px;
}

.profile-view__specs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.profile-view__form {
  display: flex;
  flex-direction: column;
}

.profile-view__row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.profile-view__actions {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}

.profile-view__actions .btn {
  gap: 8px;
}

.profile-view__error {
  color: var(--error, #E57373);
  margin-top: 12px;
  font-size: 14px;
}
</style>
