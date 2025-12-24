<script setup>
import { ref } from 'vue'
import { X, ChevronDown, SendHorizontal, MessageCircle } from 'lucide-vue-next'

const expanded = ref(false)
</script>

<template>
  <div class="chat-widget">
    <Transition name="chat-expand">
      <div v-if="expanded" class="chat-widget__window">
        <div class="chat-widget__header">
          <div class="chat-widget__header-info">
            <MessageCircle :size="18" :stroke-width="1.5" />
            <span>Ассистент La Bellezza</span>
          </div>
          <div class="chat-widget__header-actions">
            <button aria-label="Свернуть" @click="expanded = false">
              <ChevronDown :size="20" :stroke-width="1.5" />
            </button>
            <button aria-label="Закрыть" @click="expanded = false">
              <X :size="20" :stroke-width="1.5" />
            </button>
          </div>
        </div>
        <div class="chat-widget__messages">
          <p class="chat-widget__bot-msg">
            Здравствуйте! Я помогу подобрать мастера, услугу и удобное время для записи.
            Расскажите, что вас интересует?
          </p>
        </div>
        <div class="chat-widget__input">
          <input type="text" placeholder="Напишите сообщение..." />
          <button aria-label="Отправить">
            <SendHorizontal :size="20" :stroke-width="1.5" />
          </button>
        </div>
      </div>
    </Transition>

    <button v-if="!expanded" class="chat-widget__trigger" @click="expanded = true">
      <MessageCircle :size="28" :stroke-width="1.5" />
    </button>
  </div>
</template>
