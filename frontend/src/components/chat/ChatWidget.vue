<script setup>
import { ref, watch, nextTick } from 'vue'
import { X, ChevronDown } from 'lucide-vue-next'
import chatAvatarUrl from '../../assets/images/chat_avatar.png'
import { useChat } from '../../composables/useChat.js'

const expanded = ref(false)
const userInput = ref('')
const messagesEl = ref(null)
const initialized = ref(false)

const { messages, loading, getGreeting, fetchGreeting } = useChat()

async function openChat() {
  expanded.value = true
  if (!initialized.value) {
    await fetchGreeting()
    const text = getGreeting()
    messages.value = text
      .split('\n')
      .map(line => line.trim())
      .filter(Boolean)
      .map(line => ({ role: 'assistant', content: line }))
    initialized.value = true
  }
  await nextTick()
  scrollToBottom()
}

function closeChat() { expanded.value = false }

function scrollToBottom() {
  if (messagesEl.value) {
    messagesEl.value.scrollTop = messagesEl.value.scrollHeight
  }
}

watch(expanded, (open) => {
  if (open) {
    document.addEventListener('keydown', onKeydown)
  } else {
    document.removeEventListener('keydown', onKeydown)
  }
})

function onKeydown(e) {
  if (e.key === 'Escape') closeChat()
}
</script>

<template>
  <!-- Chat avatar trigger -->
  <div class="chat-widget">
    <div class="chat-tooltip">Чат с ассистентом</div>
    <div class="chat-avatar" title="Чат с ассистентом" @click="openChat">
      <img :src="chatAvatarUrl" alt="Chat Assistant">
    </div>
  </div>

  <!-- Backdrop -->
  <Teleport to="body">
    <Transition name="chat-fade">
      <div v-if="expanded" class="chat-backdrop" @click="closeChat" />
    </Transition>

    <!-- Chat window -->
    <Transition name="chat-window">
      <section v-if="expanded" class="chat-window" aria-label="Чат с ассистентом">
        <header class="chat-window__head">
          <div class="chat-window__assistant">
            <span class="mini-avatar ai-photo ai-photo--assistant" aria-hidden="true">
              <img :src="chatAvatarUrl" alt="">
            </span>
            <div>
              <p>Ассистент La Bellezza</p>
              <small>Онлайн</small>
            </div>
          </div>
          <div class="chat-window__controls">
            <button type="button" aria-label="Свернуть чат" @click="closeChat">
              <ChevronDown :size="16" :stroke-width="1.5" />
            </button>
            <button type="button" aria-label="Закрыть чат" @click="closeChat">
              <X :size="16" :stroke-width="1.5" />
            </button>
          </div>
        </header>

        <div ref="messagesEl" class="chat-window__messages">
          <article
            v-for="(msg, i) in messages"
            :key="i"
            class="msg"
            :class="msg.role === 'assistant' ? 'msg--bot' : 'msg--client'"
          >
            {{ msg.content }}
          </article>
          <article v-if="loading" class="msg msg--typing">
            <span /><span /><span />
          </article>
        </div>

        <form class="chat-window__input" @submit.prevent>
          <input
            v-model="userInput"
            type="text"
            placeholder="Введите сообщение..."
            aria-label="Сообщение"
          >
          <button class="btn btn-gold" type="submit">Отправить</button>
        </form>
      </section>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* ═══ Chat Avatar ═══ */
.chat-widget {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.chat-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  cursor: pointer;
  border: 1px solid rgba(200, 169, 110, 0.55);
  background: linear-gradient(160deg, rgba(200, 169, 110, 0.32) 0%, rgba(212, 160, 160, 0.28) 100%);
  padding: 4px;
  animation: pulse-glow 3s infinite;
  transition: transform var(--duration-fast);
}

.chat-avatar:hover {
  transform: scale(1.05);
  animation-play-state: paused;
}

.chat-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.chat-tooltip {
  position: absolute;
  right: 80px;
  bottom: 20px;
  background: var(--bg-glass);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid var(--border-gold);
  color: var(--text-primary);
  font-family: var(--font-caption);
  font-size: 13px;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity var(--duration-fast), transform var(--duration-fast);
  transform: translateX(10px);
}

.chat-widget:hover .chat-tooltip {
  opacity: 1;
  transform: translateX(0);
}

/* ═══ Chat Backdrop ═══ */
.chat-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(5, 5, 5, 0.8);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  z-index: 1000;
}

/* ═══ Chat Window ═══ */
.chat-window {
  z-index: 1001;
  position: fixed;
  bottom: 10px;
  right: 24px;
  width: min(70vw, 980px);
  height: min(80vh, 760px);
  border-radius: 24px;
  border: 1px solid var(--border-subtle);
  background: rgba(18, 18, 18, .86);
  backdrop-filter: blur(22px);
  -webkit-backdrop-filter: blur(22px);
  box-shadow: 0 20px 50px rgba(0, 0, 0, .45);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding: 0;
}

.chat-window__head {
  display: flex;
  justify-content: space-between;
  gap: .8rem;
  align-items: center;
  padding: .9rem;
  border-bottom: 1px solid rgba(255, 255, 255, .08);
}

.chat-window__assistant {
  display: flex;
  gap: .6rem;
  align-items: center;
}

.mini-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.mini-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.chat-window__assistant p {
  margin: 0;
  font-family: var(--font-subheading);
  font-size: 1rem;
}

.chat-window__assistant small {
  color: var(--success);
}

.chat-window__controls {
  display: flex;
  gap: .4rem;
}

.chat-window__controls button {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, .18);
  background: rgba(255, 255, 255, .06);
  color: var(--text-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--duration-fast);
}

.chat-window__controls button:hover {
  background: rgba(255, 255, 255, .12);
  border-color: var(--accent-gold);
  color: var(--accent-gold);
}

.chat-window__messages {
  flex: 1;
  overflow-y: auto;
  padding: .9rem;
  display: grid;
  align-content: end;
  gap: .55rem;
}

.msg {
  max-width: 76%;
  border-radius: 15px;
  padding: .6rem .8rem;
  font-size: .9rem;
}

.msg--bot {
  justify-self: start;
  background: rgba(255, 255, 255, .07);
  border: 1px solid rgba(255, 255, 255, .09);
  color: #e7dfd7;
}

.msg--client {
  justify-self: end;
  background: rgba(200, 169, 110, .28);
  border: 1px solid rgba(200, 169, 110, .5);
}

.msg--typing {
  justify-self: start;
  display: inline-flex;
  gap: 5px;
  background: rgba(255, 255, 255, .07);
  border: 1px solid rgba(255, 255, 255, .09);
}

.msg--typing span {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--gold);
  animation: dotPulse 1s infinite ease-in-out;
}

.msg--typing span:nth-child(2) { animation-delay: .2s; }
.msg--typing span:nth-child(3) { animation-delay: .4s; }

.chat-window__input {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: .6rem;
  padding: .9rem;
  border-top: 1px solid rgba(255, 255, 255, .08);
}

.chat-window__input input {
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, .12);
  background: rgba(255, 255, 255, .05);
  color: var(--text-primary);
  padding: .6rem .85rem;
  font-size: .9rem;
}

.chat-window__input input:focus {
  border-color: var(--accent-gold);
}

/* ═══ Transitions ═══ */
.chat-fade-enter-active,
.chat-fade-leave-active {
  transition: opacity var(--duration-smooth);
}
.chat-fade-enter-from,
.chat-fade-leave-to {
  opacity: 0;
}

.chat-window-enter-active,
.chat-window-leave-active {
  transition: opacity .4s cubic-bezier(.22, .61, .36, 1),
              transform .4s cubic-bezier(.22, .61, .36, 1);
}

.chat-window-enter-from,
.chat-window-leave-to {
  opacity: 0;
  transform: scale(.6) translateY(40px);
}

@media (max-width: 768px) {
  .chat-window {
    width: 98vw;
    height: 95vh;
    right: 1vw;
    bottom: 1vh;
  }
}
</style>
