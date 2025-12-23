<script setup>
import { ref } from 'vue'

const expanded = ref(false)
</script>

<template>
  <div class="chat-widget">
    <Transition name="chat-expand">
      <div v-if="expanded" class="chat-widget__window">
        <div class="chat-widget__header">
          <span>Ассистент La Bellezza</span>
          <button @click="expanded = false">&times;</button>
        </div>
        <div class="chat-widget__messages">
          <p class="chat-widget__bot-msg">
            Здравствуйте! Я помогу подобрать мастера, услугу и удобное время для записи.
            Расскажите, что вас интересует?
          </p>
        </div>
        <div class="chat-widget__input">
          <input type="text" placeholder="Напишите сообщение..." />
          <button>→</button>
        </div>
      </div>
    </Transition>

    <button v-if="!expanded" class="chat-widget__trigger" @click="expanded = true">
      <span class="chat-widget__avatar">💬</span>
    </button>
  </div>
</template>
