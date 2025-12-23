import { ref, computed } from 'vue'
import api from '../api/client.js'

const CLIENT_KEY = 'bellezza_client'

export function useChat() {
  const messages = ref([])
  const loading = ref(false)

  const client = computed(() => {
    const stored = localStorage.getItem(CLIENT_KEY)
    return stored ? JSON.parse(stored) : null
  })

  const isReturning = computed(() => !!client.value?.name)

  function getGreeting() {
    if (isReturning.value) {
      return `Здравствуйте, ${client.value.name}! Рад вас снова видеть. Чем могу помочь сегодня?`
    }
    return 'Здравствуйте! Я помогу подобрать мастера, услугу и удобное время для записи. Расскажите, что вас интересует?'
  }

  function saveClient(data) {
    localStorage.setItem(CLIENT_KEY, JSON.stringify(data))
  }

  return { messages, loading, client, isReturning, getGreeting, saveClient }
}
