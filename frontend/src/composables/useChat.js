import { ref, computed, watch } from 'vue'
import api from '../api/client.js'

const CONV_KEY = 'bellezza_conv_id'
const CONV_ACTIVITY_KEY = 'bellezza_conv_activity'
const CONV_MESSAGES_KEY = 'bellezza_conv_messages'
const CLIENT_KEY = 'bellezza_client'
const DEFAULT_GREETING = 'Здравствуйте! Я помогу подобрать мастера, услугу и удобное время для записи. Расскажите, что вас интересует?'
const CONVERSATION_TTL_MS = 4 * 60 * 60 * 1000 // 4 hours

function isConversationExpired() {
  const lastActivity = localStorage.getItem(CONV_ACTIVITY_KEY)
  if (!lastActivity) return true
  return (Date.now() - Number(lastActivity)) > CONVERSATION_TTL_MS
}

function touchActivity() {
  localStorage.setItem(CONV_ACTIVITY_KEY, String(Date.now()))
}

function loadPersistedState() {
  if (isConversationExpired()) {
    localStorage.removeItem(CONV_KEY)
    localStorage.removeItem(CONV_MESSAGES_KEY)
    localStorage.removeItem(CONV_ACTIVITY_KEY)
    return { conversationId: '', messages: [] }
  }

  const convId = localStorage.getItem(CONV_KEY) || ''
  let messages = []
  try {
    const raw = localStorage.getItem(CONV_MESSAGES_KEY)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (Array.isArray(parsed)) messages = parsed
    }
  } catch {
    // corrupted data — start fresh
  }

  return { conversationId: convId, messages }
}

const persisted = loadPersistedState()
const messages = ref(persisted.messages)
const loading = ref(false)
const greeting = ref('')
const conversationId = ref(persisted.conversationId)

watch(messages, (val) => {
  try {
    localStorage.setItem(CONV_MESSAGES_KEY, JSON.stringify(val))
  } catch {
    // quota exceeded — ignore
  }
}, { deep: true })

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
        localStorage.setItem(CONV_KEY, data.conversation_id)
      }

      touchActivity()

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
    localStorage.removeItem(CONV_KEY)
    localStorage.removeItem(CONV_MESSAGES_KEY)
    localStorage.removeItem(CONV_ACTIVITY_KEY)
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
