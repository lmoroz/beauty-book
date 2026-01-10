import { ref, computed } from 'vue'
import api from '../api/client.js'

const CONV_KEY = 'bellezza_conv_id'
const CLIENT_KEY = 'bellezza_client'
const DEFAULT_GREETING = 'Здравствуйте! Я помогу подобрать мастера, услугу и удобное время для записи. Расскажите, что вас интересует?'

const messages = ref([])
const loading = ref(false)
const greeting = ref('')
const conversationId = ref(sessionStorage.getItem(CONV_KEY) || '')

export function useChat() {
  const client = computed(() => {
    const stored = localStorage.getItem(CLIENT_KEY)
    return stored ? JSON.parse(stored) : null
  })

  const isReturning = computed(() => !!client.value?.name)

  async function fetchGreeting() {
    try {
      const { data } = await api.get('/chat/greeting')
      greeting.value = data.greeting || DEFAULT_GREETING
    } catch {
      greeting.value = DEFAULT_GREETING
    }
  }

  function getGreeting() {
    if (isReturning.value) {
      return `Здравствуйте, ${client.value.name}! Рад вас снова видеть. Чем могу помочь сегодня?`
    }
    return greeting.value || DEFAULT_GREETING
  }

  function saveClient(data) {
    localStorage.setItem(CLIENT_KEY, JSON.stringify(data))
  }

  async function sendMessage(text) {
    const trimmed = (text || '').trim()
    if (!trimmed || loading.value) return

    const historySnapshot = messages.value
      .filter(m => m.role === 'user' || m.role === 'assistant')
      .slice(-20)
      .map(m => ({ role: m.role, content: m.content }))

    messages.value.push({ role: 'user', content: trimmed })
    loading.value = true

    try {
      const { data } = await api.post('/chat', {
        message: trimmed,
        conversation_id: conversationId.value || undefined,
        history: historySnapshot,
      }, {
        timeout: 60000,
      })

      if (data.conversation_id) {
        conversationId.value = data.conversation_id
        sessionStorage.setItem(CONV_KEY, data.conversation_id)
      }

      if (data.reply) {
        messages.value.push({ role: 'assistant', content: data.reply })
      }
    } catch (err) {
      const status = err.response?.status
      let errorText

      if (status === 429) {
        errorText = 'Слишком много сообщений. Пожалуйста, подождите немного.'
      } else {
        errorText = 'Произошла ошибка. Пожалуйста, попробуйте ещё раз.'
      }

      messages.value.push({ role: 'assistant', content: errorText })
    } finally {
      loading.value = false
    }
  }

  function resetConversation() {
    messages.value = []
    conversationId.value = ''
    sessionStorage.removeItem(CONV_KEY)
  }

  return {
    messages,
    loading,
    conversationId,
    client,
    isReturning,
    getGreeting,
    fetchGreeting,
    saveClient,
    sendMessage,
    resetConversation,
  }
}
